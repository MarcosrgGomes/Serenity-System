<?php
/**
 * Funções de Segurança do Sistema
 */

class Security {
    /**
     * Valida requisição com CSRF token
     */
    public static function validateRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!verifyCsrfToken($token)) {
                // Mensagem genérica — não revela o mecanismo de proteção
                http_response_code(403);
                // Regenera o token para invalidar o antigo
                unset($_SESSION[CSRF_TOKEN_NAME]);
                setFlashMessage('error', 'Sessão expirada. Tente novamente.');
                // Volta para a página anterior ou para o dashboard
                $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard';
                header('Location: ' . $referer);
                exit;
            }
        }
        return true;
    }
    
    /**
     * Sanitiza dados de entrada
     */
    public static function sanitizeData($data, $type = 'string') {
        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var($data, FILTER_SANITIZE_URL);
            default:
                return sanitize($data);
        }
    }
    
    /**
     * Registra ação no log de auditoria
     *
     * user_id é sempre int (ID do banco) ou 0 para visitantes,
     * eliminando o conflito de tipos entre o SQL (int) e o JSON (uniqid/string).
     */
    public static function auditLog($action, $details = []) {
        // Envolvido em try/catch para nunca causar tela branca.
        // O audit log é auxiliar — um erro aqui não deve derrubar a aplicação.
        try {
            $user = getCurrentUser();

            // Garante que user_id é sempre inteiro — compatível com a coluna INT do banco.
            // Visitantes/sistemas recebem 0 (não uma string de uniqid).
            $userId = isset($user['id']) ? (int)$user['id'] : 0;

            $logEntry = [
                'id'         => generateId(),
                'user_id'    => $userId,
                'user_name'  => $user['name'] ?? 'Visitante',
                'action'     => $action,
                'details'    => $details,
                'ip'         => self::getClientIp(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 300),
                'timestamp'  => date('Y-m-d H:i:s'),
            ];

            $logFile = DATA_PATH . '/audit/audit.json';

            // Garante que o diretório existe antes de tentar ler/gravar
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $logs = DataManager::load($logFile);
            $logs[] = $logEntry;

            // Manter apenas os últimos 1000 registros
            if (count($logs) > 1000) {
                $logs = array_slice($logs, -1000);
            }

            DataManager::save($logFile, $logs);
        } catch (\Throwable $e) {
            // Registra no log do servidor mas não interrompe o fluxo
            error_log('[Serenity] auditLog falhou: ' . $e->getMessage());
        }
    }
    
    /**
     * Verifica permissões de acesso
     */
    public static function checkPermissions($requiredRole) {
        if (!hasPermission($requiredRole)) {
            setFlashMessage('error', 'Você não tem permissão para acessar esta área.');
            redirect('dashboard');
        }
        return true;
    }
    
    /**
     * Obtém o IP real do cliente de forma segura.
     * Valida o formato antes de aceitar para evitar log injection.
     */
    public static function getClientIp(): string {
        // Em ambientes com proxy reverso confiável, use HTTP_CF_CONNECTING_IP ou HTTP_X_REAL_IP
        // Por padrão usamos REMOTE_ADDR que não pode ser forjado pelo cliente
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    /**
     * Gera token seguro
     */
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Valida força da senha
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'A senha deve ter no mínimo 8 caracteres';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos uma letra maiúscula';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos uma letra minúscula';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos um número';
        }
        
        return $errors;
    }
    
    /**
     * Rate limiting simples baseado em sessão
     */
    public static function rateLimit($action, $maxAttempts = 5, $timeWindow = 300) {
        $key = 'rate_limit_' . $action;
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }
        
        $data = $_SESSION[$key];
        
        // Resetar se passou o tempo
        if (time() - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }
        
        // Verificar limite
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$key]['attempts']++;
        return true;
    }
}

