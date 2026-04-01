<?php
/**
 * Controlador de Usuários (Versão SQL/PDO)
 */

class UserController {
    
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
     * Lista de usuários
     */
    public function index() {
        Security::checkPermissions('admin');
        
        // --- LÓGICA SQL: Buscar ---
        $users = $this->pdo->query("SELECT id, name, email, role, status, created_at 
                                   FROM users 
                                   ORDER BY name ASC")->fetchAll();
        // ------------------------
        
        require_once APP_PATH . '/views/admin/users.php';
    }
    
    /**
     * Excluir usuário
     */
    public function delete() {
        Security::checkPermissions('admin');

        // CSRF via GET: token incluído na URL pelo link de exclusão da listagem
        if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Ação não autorizada.');
            redirect('users');
        }
        $id = intval($_GET['id'] ?? 0);
        $currentUserId = $_SESSION['user_id'];
        
        if ($id <= 0) { setFlashMessage('error', 'ID inválido.'); redirect('users'); }
        if ($id === (int)$currentUserId) {
            setFlashMessage('error', 'Você não pode excluir sua própria conta.');
            redirect('users');
        }
        
        // --- LÓGICA SQL: Deletar ---
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        // -------------------------
        
        // Security::auditLog('user_deleted', ['user_id' => $id]);
        setFlashMessage('success', 'Usuário excluído com sucesso!');
        redirect('users');
    }

    // (Outras funções como edit/update de usuário podem ser adicionadas aqui)
}