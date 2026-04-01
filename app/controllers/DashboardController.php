<?php

/**
 * Controlador do Dashboard (Versão SQL/PDO)
 */

class DashboardController
{

    /**
     * @var PDO A instância da conexão com o banco de dados
     */
    private $pdo;

    /**
     * Construtor para injetar a conexão com o banco.
     * @param PDO $pdo A instância PDO.
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Dashboard principal
     */
    public function index()
    {
        $stats = [];
        // Garantir que os valores existam mesmo se as constantes não estiverem definidas
        $lowStock = defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 5;
        $criticalStock = defined('CRITICAL_STOCK_THRESHOLD') ? (int)CRITICAL_STOCK_THRESHOLD : 2;


        // --- Estatísticas de Produtos e Fornecedores (Contagens) ---
        $stats['total_products'] = $this->pdo->query("SELECT COUNT(id) FROM products")->fetchColumn();
        $stats['active_products'] = $this->pdo->query("SELECT COUNT(id) FROM products WHERE status = 'active'")->fetchColumn();
        $stats['total_suppliers'] = $this->pdo->query("SELECT COUNT(id) FROM suppliers")->fetchColumn();
        $stats['active_suppliers'] = $this->pdo->query("SELECT COUNT(id) FROM suppliers WHERE status = 'active'")->fetchColumn();

        // --- Estatísticas de Estoque (Contagens) ---
        // Estoque BAIXO: quantidade abaixo ou igual ao mínimo definido para o produto
        $stats['low_stock_count'] =
            $this->pdo->query("SELECT COUNT(id) FROM products WHERE quantity <= min_quantity")
            ->fetchColumn();

        // Estoque CRÍTICO: quantidade abaixo ou igual ao limite crítico global (CRITICAL_STOCK_THRESHOLD).
        // CORRIGIDO: usa CRITICAL_STOCK_THRESHOLD, não a mesma condição do baixo.
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(id) FROM products WHERE quantity <= ?"
        );
        $stmt->execute([$criticalStock]);
        $stats['critical_stock_count'] = $stmt->fetchColumn();

        // --- Estatísticas de Valor (Soma) ---
        $stockValues = $this->pdo->query("
            SELECT
                SUM(cost_price * quantity)  AS total_stock_value,
                SUM(sale_price * quantity)  AS total_potential_revenue
            FROM products
        ")->fetch();

        // CORRIGIDO: valor em estoque = preço de CUSTO (critério contábil correto).
        // total_potential_revenue usa preço de venda e é exibido separadamente.
        $stats['total_stock_value']       = $stockValues['total_stock_value']       ?? 0;
        $stats['total_potential_revenue'] = $stockValues['total_potential_revenue']  ?? 0;

        // --- Movimentações Recentes ---
        // Buscamos o nome do produto e do usuário usando LEFT JOIN
        $sql = "SELECT m.*, p.name as product_name, u.name as user_name
                FROM stock_movements m
                LEFT JOIN products p ON m.product_id = p.id
                LEFT JOIN users u ON m.user_id = u.id
                ORDER BY m.date DESC
                LIMIT 10";
        $stats['recent_movements'] = $this->pdo->query($sql)->fetchAll();

        // --- Produtos com Estoque Baixo ---
        // [CORREÇÃO] Adicionamos 'sku' na consulta
        // --- Produtos com Estoque Baixo ---
        // [CORREÇÃO] Fazemos o JOIN com fornecedores (suppliers) para pegar o nome
        $sql = "SELECT 
                    p.id, 
                    p.name, 
                    p.sku, 
                    p.quantity, 
                    p.min_quantity, 
                    s.name as supplier_name  
                FROM products p 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.quantity <= p.min_quantity
                ORDER BY p.quantity ASC";

        $stats['low_stock_products'] = $this->pdo->query($sql)->fetchAll();
        // <-- ESTA LINHA FALTAVA

        // --- Produtos Mais Valiosos (em estoque) ---
        $sql = "SELECT id, name, quantity, sale_price, (sale_price * quantity) as total_value
                FROM products
                ORDER BY total_value DESC
                LIMIT 5";
        $stats['top_valuable_products'] = $this->pdo->query($sql)->fetchAll();

        // --- Movimentações por Tipo (Últimos 30 dias) ---
        $sql = "SELECT type, COUNT(id) as count
                FROM stock_movements
                WHERE date >= CURDATE() - INTERVAL 30 DAY
                GROUP BY type";
        $movementsByType = $this->pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['movements_by_type'] = $movementsByType;

        require_once APP_PATH . '/views/dashboard/index.php';
    }
}
