<?php
/**
 * Serenity — Conexão com o Banco de Dados
 *
 * Credenciais lidas do .env (nunca hardcoded aqui).
 * Copie .env.example para .env e preencha antes de rodar.
 */

// ── Carrega .env se existir ────────────────────────────────
// Nota: putenv('CHAVE=') com valor vazio REMOVE a variável no PHP; por isso
// gravamos sempre em $_ENV e só usamos putenv quando o valor não é vazio.
// O .env tem prioridade sobre variáveis já definidas no SO/servidor web.
$_envFile = BASE_PATH . '/.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        $_line = trim($_line);
        if ($_line === '' || str_starts_with($_line, '#')) continue;
        $_parts = explode('=', $_line, 2);
        $_k = trim($_parts[0]);
        $_v = isset($_parts[1]) ? trim($_parts[1]) : '';
        $_ENV[$_k] = $_v;
        if ($_v !== '') {
            putenv("$_k=$_v");
        }
    }
}
unset($_envFile, $_line, $_k, $_v, $_parts);

// ── Credenciais — obrigatoriamente via .env ────────────────
$_env = static function (string $key, string $default = ''): string {
    if (array_key_exists($key, $_ENV)) {
        return (string) $_ENV[$key];
    }
    $g = getenv($key);
    return $g !== false ? (string) $g : $default;
};

define('DB_HOST',    $_env('DB_HOST', 'localhost'));
define('DB_NAME',    $_env('DB_NAME', 'serenity'));
define('DB_USER',    $_env('DB_USER', 'root'));
define('DB_PASS',    $_env('DB_PASS', ''));
unset($_env);
define('DB_CHARSET', 'utf8mb4');

$_dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    DB_HOST, DB_NAME, DB_CHARSET
);

$_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($_dsn, DB_USER, DB_PASS, $_options);
} catch (\PDOException $e) {
    // Em produção nunca exibe detalhes da conexão (host, usuário, senha)
    $isDev = (getenv('APP_ENV') ?: 'production') === 'development';
    error_log('[Serenity] Falha na conexão com o banco: ' . $e->getMessage());
    if ($isDev) {
        die('<pre>Erro de banco (dev): ' . htmlspecialchars($e->getMessage()) . '</pre>');
    }
    die('Não foi possível conectar ao banco de dados. Contate o administrador.');
}

unset($_dsn, $_options);
