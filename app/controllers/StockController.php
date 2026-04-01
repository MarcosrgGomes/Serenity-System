<?php
/**
 * Controlador de Estoque (Versão SQL/PDO)
 */

class StockController {
    
    /**
     * @var PDO A instância da conexão com o banco de dados
     */
    private $pdo;

    /**
     * Construtor para injetar a conexão com o banco.
     * @param PDO $pdo A instância PDO.
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function index() {
        $this->movements();
    }
    
    /**
     * Lista de todas as movimentações
     */
    public function movements() {
        // --- LÓGICA SQL ---
        // Busca movimentações, nome do produto e nome do usuário com JOINs
        $sql = "SELECT m.*, p.name as product_name, u.name as user_name
                FROM stock_movements m
                LEFT JOIN products p ON m.product_id = p.id
                LEFT JOIN users u ON m.user_id = u.id
                ORDER BY m.date DESC";
        
        $movements = $this->pdo->query($sql)->fetchAll();
        // -------------------
        
        require_once APP_PATH . '/views/stock/movements.php';
    }

    /**
     * Exclui uma movimentação e reverte o estoque do produto.
     * Só permitido se o estoque atual ainda for igual ao "estoque novo" deste registro
     * (ou seja, não houve movimentações posteriores que alteraram o saldo).
     */
    public function deleteMovement() {
        if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Ação não autorizada.');
            redirect('stock', ['action' => 'movements']);
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            setFlashMessage('error', 'Movimentação inválida.');
            redirect('stock', ['action' => 'movements']);
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('SELECT * FROM stock_movements WHERE id = ?');
            $stmt->execute([$id]);
            $m = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$m) {
                throw new Exception('Movimentação não encontrada.');
            }

            $productId = (int) ($m['product_id'] ?? 0);
            if ($productId <= 0) {
                $this->pdo->prepare('DELETE FROM stock_movements WHERE id = ?')->execute([$id]);
                $this->pdo->commit();
                setFlashMessage('success', 'Registro de movimentação removido.');
                $this->redirectAfterMovementDelete(0);
            }

            $stmt = $this->pdo->prepare('SELECT quantity FROM products WHERE id = ? FOR UPDATE');
            $stmt->execute([$productId]);
            $currentQty = (int) $stmt->fetchColumn();

            if ($currentQty !== (int) $m['new_quantity']) {
                $this->pdo->rollBack();
                setFlashMessage(
                    'error',
                    'Não é possível excluir: o estoque atual foi alterado por outras movimentações. Exclua primeiro as movimentações mais recentes deste produto.'
                );
                redirect('stock', ['action' => 'movements']);
            }

            $stmt = $this->pdo->prepare('UPDATE products SET quantity = ? WHERE id = ?');
            $stmt->execute([(int) $m['old_quantity'], $productId]);

            $stmt = $this->pdo->prepare('DELETE FROM stock_movements WHERE id = ?');
            $stmt->execute([$id]);

            $this->pdo->commit();
            setFlashMessage('success', 'Movimentação excluída e estoque revertido.');
            $this->redirectAfterMovementDelete($productId);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            setFlashMessage('error', 'Não foi possível excluir: ' . $e->getMessage());
        }

        redirect('stock', ['action' => 'movements']);
    }

    /**
     * Volta para a visualização do produto se o link de exclusão tiver vindo de lá.
     */
    private function redirectAfterMovementDelete(int $productId): void {
        if ($productId > 0 && ($_GET['return'] ?? '') === 'view') {
            $pid = (int) ($_GET['product_id'] ?? 0);
            if ($pid === $productId) {
                redirect('products', ['action' => 'view', 'id' => (string) $pid]);
            }
        }
        redirect('stock', ['action' => 'movements']);
    }
    
    /**
     * Formulário de ajuste de estoque
     */
   public function adjustment() {
        // --- LÓGICA SQL ---
        // [CORRETO] Agora pedimos a coluna 'quantity'
        $products = $this->pdo->query("SELECT id, name, sku, quantity FROM products WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        // -------------------
        
        require_once APP_PATH . '/views/stock/adjustment.php';
    }
    
    /**
     * Salva um ajuste de estoque (Entrada/Saída/etc.)
     */
    public function saveAdjustment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('stock', ['action' => 'adjustment']);
        }

        Security::validateRequest();

        $productId = intval($_POST['product_id'] ?? 0);
        $rawType = $_POST['type'] ?? '';
        $allowedTypes = ['entry', 'exit', 'return', 'adjustment', 'loss'];
        $type = in_array($rawType, $allowedTypes) ? $rawType : '';
        $quantity = intval($_POST['quantity'] ?? 0);
        $reason = sanitize($_POST['reason'] ?? '');
        
        if ($productId <= 0 || empty($type) || $quantity <= 0) {
            setFlashMessage('error', 'Dados inválidos.');
            redirect('stock', ['action' => 'adjustment']);
        }
        
        // --- LÓGICA SQL COM TRANSAÇÃO ---
        // Isso garante que ambas as tabelas (products e stock_movements)
        // sejam atualizadas juntas, ou nenhuma delas.
        
        try {
            $this->pdo->beginTransaction();
            
            // 1. Pega o estoque atual e TRAVA a linha do produto
            $stmt = $this->pdo->prepare("SELECT quantity FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$productId]);
            $oldQuantity = $stmt->fetchColumn();

            if ($oldQuantity === false) {
                throw new Exception("Produto não encontrado.");
            }
            
            // 2. Calcula novo estoque
            $newQuantity = $oldQuantity;
            if ($type === 'entry' || $type === 'return') {
                $newQuantity += $quantity;
            } else { // 'exit', 'damage', 'loss'
                $newQuantity -= $quantity;
                if ($newQuantity < 0) {
                    $newQuantity = 0; // Impede estoque negativo
                }
            }
            
            // 3. Atualiza a tabela 'products'
            $stmt = $this->pdo->prepare("UPDATE products SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $productId]);
            
            // 4. Insere o registro na tabela 'stock_movements'
            $sql = "INSERT INTO stock_movements 
                        (product_id, user_id, type, quantity, old_quantity, new_quantity, reason, date)
                    VALUES 
                        (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $productId,
                $_SESSION['user_id'],
                $type,
                $quantity,
                $oldQuantity,
                $newQuantity,
                $reason
            ]);
            
            // 5. Confirma a transação
            $this->pdo->commit();
            
            // Security::auditLog('stock_adjustment', [...]);
            setFlashMessage('success', 'Movimentação de estoque registrada com sucesso!');
            redirect('stock', ['action' => 'movements']);
            
        } catch (Exception $e) {
            // 6. Desfaz a transação em caso de erro
            $this->pdo->rollBack();
            setFlashMessage('error', 'Falha ao registrar movimentação: ' . $e->getMessage());
            redirect('stock', ['action' => 'adjustment']);
        }
        // --- FIM DA LÓGICA SQL ---
    }
    
    /**
     * Visão de inventário
     */
    public function inventory() {
        
        // --- [CORREÇÃO] BUSCAR A LISTA DE PRODUTOS (ESTAVA FALTANDO) ---
        $sql_products = "SELECT name, quantity, min_quantity, cost_price, sale_price 
                         FROM products 
                         ORDER BY name ASC";
        $products = $this->pdo->query($sql_products)->fetchAll();
        // --- FIM DA CORREÇÃO ---

        
        // Busca estatísticas gerais (O seu código disto já estava correto)
        $sql_stats = "SELECT COUNT(id) as total_items, 
                             SUM(quantity) as total_quantity, 
                             SUM(cost_price * quantity) as total_stock_value,
                             SUM(sale_price * quantity) as total_sale_value 
                      FROM products";
        $stats = $this->pdo->query($sql_stats)->fetch();
        
        
        require_once APP_PATH . '/views/stock/inventory.php';
    }
    
    /**
     * Relatório de estoque baixo
     */
    public function lowStock() {
        // --- LÓGICA SQL ---
        // Busca produtos onde a quantidade é menor ou igual ao mínimo
        $sql = "SELECT p.id, p.sku, p.name, p.quantity, p.min_quantity, p.cost_price, p.sale_price, p.status,
                       c.name AS category_name, s.name AS supplier_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s  ON p.supplier_id  = s.id
                WHERE p.quantity <= p.min_quantity
                ORDER BY p.quantity ASC";
        $lowStockProducts = $this->pdo->query($sql)->fetchAll();
        // -------------------
        
        require_once APP_PATH . '/views/stock/low-stock.php';
    }
}