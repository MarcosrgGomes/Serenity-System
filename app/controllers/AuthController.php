    <?php
    /**
    * Controlador de Autenticação (Versão SQL/PDO)
    */

    class AuthController {
        
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
        * Página de login
        */
        public function index() {
            $this->login();
        }
        
        /**
        * Exibe formulário de login
        */
        public function login() {
            if (isLoggedIn()) {
                redirect('dashboard');
            }
            require_once APP_PATH . '/views/auth/login.php';
        }
        
        /**
        * Processa login
        */
        public function authenticate() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('login');
            }

            Security::validateRequest();

            if (!Security::rateLimit('login', 5, 300)) {
                setFlashMessage('error', 'Muitas tentativas de login. Tente novamente em 5 minutos.');
                redirect('login');
            }
            
            $email = strtolower(trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL)));
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            if (empty($email) || empty($password)) {
                setFlashMessage('error', 'Por favor, preencha todos os campos.');
                redirect('login');
            }
            
            // --- INÍCIO DA LÓGICA SQL ---
            // 1. Buscar usuário no banco de dados
            $sql = "SELECT id, name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(); // Retorna o usuário (array) ou false se não encontrar
            
            // 2. Verificar senha
            // Usamos $user['password_hash'] (nome da coluna no SQL)
            if (!$user || !password_verify($password, $user['password_hash'])) {
                Security::auditLog('login_failed', ['email' => $email]);
                setFlashMessage('error', 'Email ou senha incorretos.');
                redirect('login');
            }
            // --- FIM DA LÓGICA SQL ---
            
            // Verificar status
            if ($user['status'] !== 'active') {
                setFlashMessage('error', 'Sua conta está inativa. Entre em contato com o administrador.');
                redirect('login');
            }
            
            // Regenera o ID da sessão antes de gravar dados sensíveis
            // Previne Session Fixation Attack
            session_regenerate_id(true);

            // Criar sessão (O ID agora é um INT do SQL, o que é ótimo)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_data'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            $_SESSION['login_time'] = time();
            
            // Lembrar-me
            if ($remember) {
                setcookie('remember_token', $user['id'], [
                    'expires'  => time() + (86400 * 30),
                    'path'     => '/',
                    'httponly' => true,   // inacessível via JS — protege contra XSS
                    'samesite' => 'Lax',  // protege contra CSRF em navegadores modernos
                ]);
            }
            
            Security::auditLog('login_success', ['user_id' => $user['id']]);
            setFlashMessage('success', 'Bem-vindo(a), ' . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . '!');
            redirect('dashboard');
        }
        
        /**
        * Exibe formulário de registro
        */
        public function register() {
            if (isLoggedIn()) {
                redirect('dashboard');
            }
            require_once APP_PATH . '/views/auth/register.php';
        }
        
        /**
        * Processa registro
        */
        public function doRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('register');
        }

        Security::validateRequest();

        $name = sanitize($_POST['name'] ?? '');
        // Email: filter_var antes do sanitize para não corromper o endereço
        $email = strtolower(trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL)));
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        if (empty($name)) { $errors[] = 'Nome é obrigatório'; }
        if (empty($email) || !isValidEmail($email)) { $errors[] = 'Email inválido'; }
        if ($password !== $confirmPassword) { $errors[] = 'As senhas não coincidem'; }
        
        $passwordErrors = Security::validatePasswordStrength($password);
        $errors = array_merge($errors, $passwordErrors);
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect('register');
        }

        try {
            // Verificar email único
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                setFlashMessage('error', 'Este email já está cadastrado');
                redirect('register');
            }
            
            // Criar usuário
            $sql = "INSERT INTO users (name, email, password_hash, role, status) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                'operator',
                'active'
            ]);
            
            $newUserId = $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[Serenity] Erro ao registrar usuário: ' . $e->getMessage());
            setFlashMessage('error', 'Erro ao criar conta. Tente novamente ou contate o administrador.');
            redirect('register');
        }
        
        Security::auditLog('user_registered', ['user_id' => $newUserId, 'email' => $email]);
        setFlashMessage('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
        redirect('login');
    }
        
        /**
        * Exibe formulário de recuperação de senha
        */
        public function forgotPassword() {
            if (isLoggedIn()) {
                redirect('dashboard');
            }
            require_once APP_PATH . '/views/auth/forgot-password.php';
        }
        
        /**
        * Processa recuperação de senha
        */
        public function sendResetLink() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('forgot-password');
            }

            Security::validateRequest();

            $email = strtolower(trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL)));
            
            if (empty($email) || !isValidEmail($email)) {
                setFlashMessage('error', 'Email inválido.');
                redirect('forgot-password');
            }
            
            // --- INÍCIO DA LÓGICA SQL ---
            // 1. Verificar se o usuário existe (não faremos nada, mas é bom checar)
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);
            $userExists = $stmt->fetch();
            
            // if ($userExists) {
            //     Aqui você colocaria a lógica real de enviar email
            // }
            // --- FIM DA LÓGICA SQL ---
            
            // Sempre mostrar mensagem de sucesso (segurança)
            Security::auditLog('password_reset_requested', ['email' => $email]);
            setFlashMessage('success', 'Se o email estiver cadastrado, você receberá um link de recuperação.');
            redirect('login');
        }
        
        /**
        * Logout
        */
        public function logout() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                Security::auditLog('logout', ['user_id' => $userId]);
            }

            // Ordem correta: limpar dados ANTES de destruir a sessão
            session_unset();

            // Expirar cookies ANTES de destruir (ainda temos session_name())
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', [
                    'expires'  => time() - 3600,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }

            // Destruir a sessão atual
            session_destroy();

            // Iniciar nova sessão limpa para o flash message funcionar
            session_start();
            redirect('login');
            exit;
        }
    }