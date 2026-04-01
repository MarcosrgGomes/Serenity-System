<?php
/**
 * Funções Auxiliares do Sistema
 */

/**
 * Verifica se o usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtém o usuário logado
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['user_data'] ?? null;
}

/**
 * Verifica se o usuário tem uma determinada permissão
 */
function hasPermission($requiredRoles) {
    if (!isLoggedIn()) {
        return false;
    }
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    $userRole = $user['role'] ?? '';

    // Definir a hierarquia de papéis
    $roleHierarchy = [
        'admin' => 3, // Administrador
        'manager' => 2, // Gerenciador
        'operator' => 1, // Usuário (Operador)
        'viewer' => 0 // Visualizador (nível mais baixo)
    ];

    $userLevel = $roleHierarchy[$userRole] ?? -1; // -1 para papéis não definidos

    if (is_array($requiredRoles)) {
        foreach ($requiredRoles as $role) {
            $requiredLevel = $roleHierarchy[$role] ?? -1;
            if ($userLevel >= $requiredLevel) {
                return true;
            }
        }
        return false;
    } else {
        $requiredLevel = $roleHierarchy[$requiredRoles] ?? -1;
        return $userLevel >= $requiredLevel;
    }
}

/**
 * Redireciona para uma página
 */
function redirect($page, $params = []) {
    $url = 'index.php?page=' . $page;
    foreach ($params as $key => $value) {
        $url .= '&' . $key . '=' . urlencode($value);
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Sanitiza entrada de dados
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Formata data para exibição
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date)) {
        return '-';
    }
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formata valor monetário
 * (declaração canônica — protegida contra redeclaração)
 */
if (!function_exists('formatMoney')) {
    function formatMoney(float $value): string {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}

/**
 * Formata número inteiro ou decimal com separadores BR
 * (declaração canônica — protegida contra redeclaração)
 */
if (!function_exists('formatNumber')) {
    function formatNumber($value, int $decimals = 0): string {
        return number_format((float)$value, $decimals, ',', '.');
    }
}

/**
 * Alerta de estoque por produto (alinhado ao dashboard e estoque baixo).
 * critical: quantidade ≤ CRITICAL_STOCK_THRESHOLD (risco absoluto)
 * low: quantidade ≤ mínimo cadastrado (e acima do limite crítico global, se aplicável)
 */
function productStockAlertLevel(array $product): string {
    $qty = (int) ($product['quantity'] ?? 0);
    if ($qty <= CRITICAL_STOCK_THRESHOLD) {
        return 'critical';
    }
    $min = (int) ($product['min_quantity'] ?? 0);
    if ($min > 0 && $qty <= $min) {
        return 'low';
    }
    return 'ok';
}

/**
 * Gera um ID único
 */
function generateId() {
    return uniqid('', true);
}

/**
 * Gera um token CSRF
 */
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verifica token CSRF
 */
function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Define mensagem flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtém e limpa mensagem flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Gera SKU automático
 */
function generateSKU($prefix = 'PRD') {
    return $prefix . '-' . strtoupper(substr(uniqid(), -8));
}

/**
 * Calcula markup
 */
function calculateMarkup($cost, $price) {
    if ($cost <= 0) {
        return 0;
    }
    return (($price - $cost) / $cost) * 100;
}

/**
 * Calcula preço com markup
 */
function calculatePriceWithMarkup($cost, $markup) {
    return $cost * (1 + ($markup / 100));
}

/**
 * Valida email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida senha forte
 */
function isStrongPassword($password) {
    // Mínimo 8 caracteres, 1 maiúscula, 1 minúscula, 1 número
    return strlen($password) >= 8 
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

/**
 * Gerenciador de dados JSON
 */
class DataManager {
    /**
     * Inicializa o sistema de dados
     */
    public static function init() {
        // Criar usuário admin padrão se não existir
        $usersFile = DATA_PATH . '/users/users.json';
        if (!file_exists($usersFile)) {
            $defaultUser = [
                [
                    'id' => generateId(),
                    'name' => 'Administrador',
                    'email' => 'admin@serenity.com',
                    // Senha gerada via: php -r "echo password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);"
                    // TROQUE IMEDIATAMENTE após o primeiro acesso em qualquer ambiente
                    'password' => password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]),
                    'role' => 'admin',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            self::save($usersFile, $defaultUser);
        }
        
        // Criar categorias padrão se não existirem
        $categoriesFile = DATA_PATH . '/categories/categories.json';
        if (!file_exists($categoriesFile)) {
            $defaultCategories = [
                ['id' => generateId(), 'name' => 'Eletrônicos', 'description' => 'Produtos eletrônicos', 'status' => 'active'],
                ['id' => generateId(), 'name' => 'Roupas', 'description' => 'Vestuário em geral', 'status' => 'active'],
                ['id' => generateId(), 'name' => 'Alimentos', 'description' => 'Produtos alimentícios', 'status' => 'active'],
                ['id' => generateId(), 'name' => 'Livros', 'description' => 'Livros e publicações', 'status' => 'active'],
            ];
            self::save($categoriesFile, $defaultCategories);
        }
        
        // Criar produtos de exemplo se não existirem
        $productsFile = DATA_PATH . '/products/products.json';
        if (!file_exists($productsFile)) {
            $defaultProducts = [
                [
                    'id' => generateId(),
                    'sku' => 'PRD-001',
                    'name' => 'Notebook Dell Inspiron',
                    'description' => 'Notebook com processador Intel Core i5',
                    'category' => 'Eletrônicos',
                    'cost_price' => 2500.00,
                    'sale_price' => 3500.00,
                    'quantity' => 15,
                    'min_quantity' => 5,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => generateId(),
                    'sku' => 'PRD-002',
                    'name' => 'Mouse Logitech MX Master',
                    'description' => 'Mouse sem fio de alta precisão',
                    'category' => 'Eletrônicos',
                    'cost_price' => 200.00,
                    'sale_price' => 350.00,
                    'quantity' => 8,
                    'min_quantity' => 10,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => generateId(),
                    'sku' => 'PRD-003',
                    'name' => 'Teclado Mecânico RGB',
                    'description' => 'Teclado mecânico com iluminação RGB',
                    'category' => 'Eletrônicos',
                    'cost_price' => 300.00,
                    'sale_price' => 500.00,
                    'quantity' => 3,
                    'min_quantity' => 5,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            self::save($productsFile, $defaultProducts);
        }
        
        // Criar fornecedores de exemplo
        $suppliersFile = DATA_PATH . '/suppliers/suppliers.json';
        if (!file_exists($suppliersFile)) {
            $defaultSuppliers = [
                [
                    'id' => generateId(),
                    'name' => 'Tech Distribuidora LTDA',
                    'cnpj' => '12.345.678/0001-90',
                    'email' => 'contato@techdist.com.br',
                    'phone' => '(11) 3456-7890',
                    'address' => 'Rua das Tecnologias, 123',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => generateId(),
                    'name' => 'Mega Importadora S.A.',
                    'cnpj' => '98.765.432/0001-10',
                    'email' => 'vendas@megaimport.com.br',
                    'phone' => '(11) 9876-5432',
                    'address' => 'Av. Importação, 456',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            self::save($suppliersFile, $defaultSuppliers);
        }
        
        // Criar movimentações de estoque de exemplo
        $stockFile = DATA_PATH . '/stock/movements.json';
        if (!file_exists($stockFile)) {
            self::save($stockFile, []);
        }
    }
    
    /**
     * Carrega dados de um arquivo JSON
     */
    public static function load($file) {
        if (!file_exists($file)) {
            return [];
        }
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * Salva dados em um arquivo JSON
     */
    public static function save($file, $data) {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

// (formatMoney e formatNumber já declarados no início do arquivo)
