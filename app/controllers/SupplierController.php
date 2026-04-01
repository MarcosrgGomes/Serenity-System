<?php
/**
 * Controlador de Fornecedores (Versão SQL/PDO)
 */

class SupplierController {
    
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
     * Lista de fornecedores com filtros
     */
    public function index() {
        // --- LÓGICA SQL para Filtros ---
        $sql = "SELECT id, name, cnpj, email, phone, city, state, status, created_at FROM suppliers";
        $params = [];
        $whereClauses = [];

        $search = sanitize($_GET['search'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        
        if (!empty($search)) {
            $whereClauses[] = "(name LIKE ? OR cnpj LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($status)) {
            $whereClauses[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $suppliers = $stmt->fetchAll();
        // --- FIM DA LÓGICA SQL ---
        
        require_once APP_PATH . '/views/suppliers/list.php';
    }
    
    /**
     * Formulário de adição
     */
    public function add() {
        require_once APP_PATH . '/views/suppliers/add.php';
    }
    
    /**
     * Salvar novo fornecedor
     */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('suppliers', ['action' => 'add']);
        }

        Security::validateRequest();

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'cnpj' => sanitize($_POST['cnpj'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'address' => sanitize($_POST['address'] ?? ''),
            'city' => sanitize($_POST['city'] ?? ''),
            'state' => sanitize($_POST['state'] ?? ''),
            'status' => in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active',
        ];
        
        // --- LÓGICA SQL: Inserir ---
        $sql = "INSERT INTO suppliers (name, cnpj, email, phone, address, city, state, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        $newSupplierId = $this->pdo->lastInsertId();
        // -------------------------
        
        // (Security::auditLog... precisa ser migrado também)
        // Security::auditLog('supplier_created', ['supplier_id' => $newSupplierId, 'name' => $data['name']]);
        setFlashMessage('success', 'Fornecedor cadastrado com sucesso!');
        redirect('suppliers');
    }
    
    /**
     * Formulário de edição
     */
    public function edit() {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { setFlashMessage('error', 'Fornecedor não encontrado.'); redirect('suppliers'); }

        // --- LÓGICA SQL: Buscar ---
        $stmt = $this->pdo->prepare("SELECT id, name, cnpj, email, phone, address, city, state, status, created_at FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        $supplier = $stmt->fetch();
        // ------------------------
        
        if (!$supplier) {
            setFlashMessage('error', 'Fornecedor não encontrado.');
            redirect('suppliers');
        }
        
        require_once APP_PATH . '/views/suppliers/edit.php';
    }
    
    /**
     * Atualizar fornecedor
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('suppliers');
        }

        Security::validateRequest();

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            setFlashMessage('error', 'ID inválido.');
            redirect('suppliers');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'cnpj' => sanitize($_POST['cnpj'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'address' => sanitize($_POST['address'] ?? ''),
            'city' => sanitize($_POST['city'] ?? ''),
            'state' => sanitize($_POST['state'] ?? ''),
            'status' => in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active',
        ];
        
        // --- LÓGICA SQL: Atualizar ---
        $sql = "UPDATE suppliers SET 
                    name = ?, cnpj = ?, email = ?, phone = ?, 
                    address = ?, city = ?, state = ?, status = ?
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        // Adiciona o $id no final do array de dados
        $params = array_values($data);
        $params[] = $id;
        $stmt->execute($params);
        // -------------------------
        
        // Security::auditLog('supplier_updated', ['supplier_id' => $id]);
        setFlashMessage('success', 'Fornecedor atualizado com sucesso!');
        redirect('suppliers');
    }
    
    /**
     * Excluir fornecedor
     */
    public function delete() {
        // CSRF via GET: token incluído na URL pelo link de exclusão da listagem
        if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Ação não autorizada.');
            redirect('suppliers');
        }
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { setFlashMessage('error', 'Fornecedor não encontrado.'); redirect('suppliers'); }

        // --- LÓGICA SQL: Deletar ---
        $stmt = $this->pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        // -------------------------
        
        // Security::auditLog('supplier_deleted', ['supplier_id' => $id]);
        setFlashMessage('success', 'Fornecedor excluído com sucesso!');
        redirect('suppliers');
    }
}