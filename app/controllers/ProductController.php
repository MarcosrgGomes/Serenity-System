<?php

/**
 * Controlador de Produtos (Versão SQL/PDO)
 */

class ProductController
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
     * Lista de produtos (com filtros e ordenação)
     */
    public function index()
    {
        // --- LÓGICA SQL para Filtros e Ordenação ---

        // Base da consulta: Junta Produtos com Categorias
        $sql = "SELECT 
            p.*, 
            c.name AS category_name,
            s.name AS supplier_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id";


        $params = [];
        $whereClauses = [];

        // Filtros
        $search = sanitize($_GET['search'] ?? '');
        $category = sanitize($_GET['category'] ?? ''); // Recebe o NOME da categoria
        $status = sanitize($_GET['status'] ?? '');
        $stockAlert = sanitize($_GET['stock_alert'] ?? '');
        $supplier = sanitize($_GET['supplier'] ?? '');

        if (!empty($search)) {
            $whereClauses[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($category)) {
            $whereClauses[] = "c.name = ?";
            $params[] = $category;
        }
        if (!empty($supplier)) {
            $whereClauses[] = "s.name = ?";
            $params[] = $supplier;
        }
        if (!empty($status)) {
            $whereClauses[] = "p.status = ?";
            $params[] = $status;
        }
        if ($stockAlert === 'low') {
            $whereClauses[] = "p.quantity <= p.min_quantity";
        } elseif ($stockAlert === 'critical') {
            // Assumindo que CRITICAL_STOCK_THRESHOLD é uma constante (ex: 0)
            $whereClauses[] = "p.quantity <= ?";
            $params[] = CRITICAL_STOCK_THRESHOLD;
        }

        // Monta a cláusula WHERE
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        // Ordenação
        $sort = $_GET['sort'] ?? 'name';
        $order = $_GET['order'] ?? 'asc';

        // Whitelist para segurança
        $allowedSorts = ['name', 'sku', 'sale_price', 'quantity', 'status'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC'; // Segurança

        $sql .= " ORDER BY p.$sort $order";

        // Executa a busca
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        $suppliers = $this->pdo->query("SELECT name FROM suppliers ORDER BY name ASC")->fetchAll();


        // Busca categorias para o dropdown de filtro
        $categories = $this->pdo->query("SELECT name FROM categories ORDER BY name ASC")->fetchAll();

        require_once APP_PATH . '/views/products/list.php';
    }

    /**
     * Formulário de adição
     */
    public function add()
    {
        // Busca categorias
        $categories = $this->pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

        // [IMPORTANTE] Esta linha deve estar aqui:
        $suppliers = $this->pdo->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name ASC")->fetchAll();

        require_once APP_PATH . '/views/products/add.php';
    }

    /**
     * Salvar novo produto
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('products', ['action' => 'add']);
        }

        // Valida token CSRF antes de qualquer alteração no banco
        Security::validateRequest();
        $categoryName = sanitize($_POST['category'] ?? '');
        $categoryId = null;
        if (!empty($categoryName)) {
            $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$categoryName]);
            $categoryRow = $stmt->fetch();
            if ($categoryRow) {
                $categoryId = $categoryRow['id'];
            }
            // Opcional: E se a categoria não existir?
            // Por enquanto, $categoryId ficará null (Sem Categoria)
        }
        // ------------------------------------------

        $data = [
            'sku' => sanitize($_POST['sku'] ?? generateSKU()),
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'category_id' => $categoryId, // <-- Usando o ID
            'supplier_id' => intval($_POST['supplier_id'] ?? 0),
            'cost_price' => floatval($_POST['cost_price'] ?? 0),
            'sale_price' => floatval($_POST['sale_price'] ?? 0),
            'quantity' => intval($_POST['quantity'] ?? 0),
            'min_quantity' => intval($_POST['min_quantity'] ?? LOW_STOCK_THRESHOLD),
            'status' => in_array($_POST['status'] ?? '', ['active', 'inactive', 'discontinued']) ? $_POST['status'] : 'active',
        ];

        // (Validações - seu código original é bom, mas vamos simplificar por agora)
        if (empty($data['name'])) {
            setFlashMessage('error', 'Nome é obrigatório');
            redirect('products', ['action' => 'add']);
        }

        // --- LÓGICA SQL: Inserir ---
        $sql = "INSERT INTO products (
    sku, name, description, category_id, supplier_id,
    cost_price, sale_price, quantity, min_quantity, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['sku'],
            $data['name'],
            $data['description'],
            $data['category_id'],
            $data['supplier_id'],
            $data['cost_price'],
            $data['sale_price'],
            $data['quantity'],
            $data['min_quantity'],
            $data['status']
        ]);

        $newProductId = $this->pdo->lastInsertId();
        // -----------------------------

        // (O Security::auditLog também precisará ser migrado para SQL)
        // Security::auditLog('product_created', ['product_id' => $newProductId, 'name' => $data['name']]);
        setFlashMessage('success', 'Produto cadastrado com sucesso!');
        redirect('products');
    }

    public function edit()
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            setFlashMessage('error', 'Produto não encontrado.');
            redirect('products');
        }

        $stmt = $this->pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            setFlashMessage('error', 'Produto não encontrado.');
            redirect('products');
        }

        $categories = $this->pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

        // [IMPORTANTE] Esta linha deve estar aqui:
        $suppliers = $this->pdo->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name ASC")->fetchAll();

        require_once APP_PATH . '/views/products/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('products');
        }

        // Valida token CSRF antes de qualquer alteração no banco
        Security::validateRequest();

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            setFlashMessage('error', 'Produto não encontrado.');
            redirect('products');
        }

        // --- LÓGICA SQL: Encontrar o ID da Categoria ---
        $categoryName = sanitize($_POST['category'] ?? '');
        $categoryId = null;
        if (!empty($categoryName)) {
            $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$categoryName]);
            $categoryRow = $stmt->fetch();
            if ($categoryRow) {
                $categoryId = $categoryRow['id'];
            }
        }
        // ------------------------------------------

        // --- LÓGICA SQL: Atualizar (supplier_id incluído) ---
        $sql = "UPDATE products SET
                    sku = ?, name = ?, description = ?, category_id = ?,
                    supplier_id = ?,
                    cost_price = ?, sale_price = ?, quantity = ?,
                    min_quantity = ?, status = ?
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            sanitize($_POST['sku'] ?? ''),
            sanitize($_POST['name'] ?? ''),
            sanitize($_POST['description'] ?? ''),
            $categoryId,
            intval($_POST['supplier_id'] ?? 0),  // ← CORRIGIDO: supplier_id agora persiste
            floatval($_POST['cost_price'] ?? 0),
            floatval($_POST['sale_price'] ?? 0),
            intval($_POST['quantity'] ?? 0),
            intval($_POST['min_quantity'] ?? LOW_STOCK_THRESHOLD),
            sanitize($_POST['status'] ?? 'active'),
            $id
        ]);
        // -----------------------------

        setFlashMessage('success', 'Produto atualizado com sucesso!');
        redirect('products');
    }

    /**
     * Excluir produto
     */
    public function delete()
    {
        // CSRF via GET: token incluído na URL pelo link de exclusão da listagem
        if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Ação não autorizada.');
            redirect('products');
        }
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            setFlashMessage('error', 'Produto não encontrado.');
            redirect('products');
        }

        // --- LÓGICA SQL: Deletar ---
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        // ---------------------------

        // Security::auditLog('product_deleted', ['product_id' => $id]);
        setFlashMessage('success', 'Produto excluído com sucesso!');
        redirect('products');
    }
    public function findById($id)
    {
        $sql = "
        SELECT 
            p.*,
            c.name AS category_name,
            s.name AS supplier_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN suppliers s ON s.id = p.supplier_id
        WHERE p.id = ?
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Visualizar detalhes do produto
     */
    public function view()
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            setFlashMessage('error', 'Produto não encontrado.');
            redirect('products');
        }

        // --- LÓGICA SQL: Buscar produto e suas movimentações ---

        // Busca o produto e o nome da categoria (com JOIN)
        $stmt = $this->pdo->prepare("
    SELECT 
        p.*, 
        c.name AS category_name,
        s.name AS supplier_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.id = ?
");

        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            setFlashMessage('error', 'Produto não encontrado.');
            redirect('products');
        }

        // Busca movimentações e o nome do usuário (com JOIN)
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.name as user_name
            FROM stock_movements m
            LEFT JOIN users u ON m.user_id = u.id
            WHERE m.product_id = ?
            ORDER BY m.date DESC
        ");
        $stmt->execute([$id]);
        $productMovements = $stmt->fetchAll();
        // -------------------------------------------------

        require_once APP_PATH . '/views/products/view.php';
    }
}
