<?php
/**
 * Controlador de Categorias (Versão SQL/PDO)
 */

class CategoryController {
    
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

    /**
     * Exibe a lista de categorias.
     * No futuro, esta página permitirá CRUD.
     */
    public function index() {
        // --- LÓGICA SQL ---
        // Busca todas as categorias do banco, ordenadas por nome
        $stmt = $this->pdo->query("SELECT id, name, description, status FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll();
        // --- FIM DA LÓGICA SQL ---
        
        // A view (categories.php) não precisa mudar
        require_once APP_PATH . '/views/products/categories.php';
    }

    // (No futuro, adicionaremos aqui as funções 'save', 'update', 'delete')
}