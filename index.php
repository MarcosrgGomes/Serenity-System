<?php
/**
 * Serenity - Sistema de Gerenciamento de Estoque
 *
 * Arquivo principal de entrada do sistema
 */

// Definir constantes do sistema
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('DATA_PATH', BASE_PATH . '/data'); // Manter por enquanto
define('PUBLIC_PATH', BASE_PATH . '/public');

// Incluir arquivos de configuração (antes de session_start)
require_once APP_PATH . '/config/config.php';

// Iniciar sessão (após as configurações)
session_start();

// Incluir helpers e segurança
require_once APP_PATH . '/config/helpers.php';
require_once APP_PATH . '/config/security.php';

// -------- NOVA LÓGICA DE BANCO DE DADOS --------
// A variável $pdo agora está disponível globalmente
require_once APP_PATH . '/config/database.php';
// -----------------------------------------------

// ── Resíduo da migração JSON → SQL ─────────────────────────
// O DataManager (JSON) ainda é usado APENAS pelo Security::auditLog().
// Todos os outros controladores já usam $pdo (SQL).
// TODO: criar tabela audit_logs no banco e migrar Security::auditLog() para SQL.
// Enquanto isso, garantimos que a pasta de audit existe para evitar erros silenciosos.
if (!is_dir(DATA_PATH . '/audit')) {
    mkdir(DATA_PATH . '/audit', 0755, true);
}
// DataManager::init() removido — dados de seed agora vêm do schema.sql

// ── Headers de segurança HTTP ────────────────────────────────
// Aplicados em toda requisição PHP, complementando o .htaccess
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Roteamento simples
$page = isset($_GET['page']) ? $_GET['page'] : 'login';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Verificar autenticação (exceto para páginas públicas)
$publicPages = ['login', 'register', 'forgot-password', 'reset-password'];
if (!in_array($page, $publicPages) && !isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

// Verificar timeout de sessão — expira sessão inativa após SESSION_TIMEOUT segundos
if (isLoggedIn()) {
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        session_start();
        setFlashMessage('warning', 'Sua sessão expirou. Faça login novamente.');
        header('Location: index.php?page=login');
        exit;
    }
    // Renova o timer a cada requisição (sessão ativa = timer reinicia)
    $_SESSION['login_time'] = time();
}

// Roteamento de controladores
switch ($page) {
    case 'login':
    case 'register':
    case 'forgot-password':
    case 'reset-password':
    case 'logout':
        require_once APP_PATH . '/controllers/AuthController.php';
        $controller = new AuthController($pdo); // Injeta o $pdo
        break;
    
    // --- ATUALIZAÇÃO: Injetando $pdo em todos os controladores ---
    
    case 'dashboard':
        require_once APP_PATH . '/controllers/DashboardController.php';
        $controller = new DashboardController($pdo); // Injeta o $pdo
        break;
    
    case 'products':
        require_once APP_PATH . '/controllers/ProductController.php';
        $controller = new ProductController($pdo); // Injeta o $pdo
        break;
    
    case 'suppliers':
        require_once APP_PATH . '/controllers/SupplierController.php';
        $controller = new SupplierController($pdo); // Injeta o $pdo
        break;
    
    case 'stock':
        require_once APP_PATH . '/controllers/StockController.php';
        $controller = new StockController($pdo); // Injeta o $pdo
        break;
    
    case 'users':
        require_once APP_PATH . '/controllers/UserController.php';
        $controller = new UserController($pdo); // Injeta o $pdo
        break;
    
    case 'categories':
        require_once APP_PATH . '/controllers/CategoryController.php';
        $controller = new CategoryController($pdo); // Injeta o $pdo
        break;
    
    default:
        require_once APP_PATH . '/views/errors/404.php';
        exit;
}

// ── Whitelist de ações permitidas por página ─────────────────────────────
// Sem isso, um atacante poderia chamar métodos internos via ?action=findById
// ou ?action=__construct. Só ações explicitamente listadas são executadas.
$allowedActions = [
    'login'          => ['login', 'authenticate', 'index'],
    'register'       => ['register', 'doRegister'],
    'forgot-password'=> ['forgotPassword', 'sendResetLink'],
    'reset-password' => ['resetPassword'],
    'logout'         => ['logout'],
    'dashboard'      => ['index'],
    'products'       => ['index', 'add', 'save', 'edit', 'update', 'delete', 'view'],
    'suppliers'      => ['index', 'add', 'save', 'edit', 'update', 'delete'],
    'stock'          => ['index', 'movements', 'deleteMovement', 'adjustment', 'saveAdjustment', 'inventory', 'lowStock'],
    'users'          => ['index', 'delete'],
    'categories'     => ['index'],
];

$permitted = $allowedActions[$page] ?? [];

if (!empty($action) && in_array($action, $permitted) && method_exists($controller, $action)) {
    $controller->$action();
} elseif (in_array($page, $permitted) && method_exists($controller, $page)) {
    $controller->$page();
} elseif (method_exists($controller, 'index')) {
    $controller->index();
} else {
    require_once APP_PATH . '/views/errors/404.php';
}