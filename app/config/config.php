<?php
/**
 * Configurações Gerais do Sistema Serenity
 */

// Configurações da aplicação
define('APP_NAME', 'Serenity');
define('APP_VERSION', '1.0.0 - Sprint 1');
define('APP_DESCRIPTION', 'Sistema de Gerenciamento de Estoque');

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão (antes de session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// Ativa cookie seguro apenas quando a requisição já é HTTPS
ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax'); // protege contra CSRF em navegadores modernos

// Configurações de erro
// Em produção (APP_ENV=production), erros nunca são exibidos na tela —
// apenas registrados em log. Isso evita vazar caminhos, nomes de tabela
// e stack traces para usuários mal-intencionados.
// Padrão 'development' para facilitar diagnóstico em novos ambientes.
// Mude para 'production' no .env em servidores públicos.
$_isProduction = (getenv('APP_ENV') ?: 'development') === 'production';
error_reporting($_isProduction ? 0 : E_ALL);
ini_set('display_errors', $_isProduction ? '0' : '1');
ini_set('log_errors',     '1'); // sempre loga, nunca exibe em produção
unset($_isProduction);          // limpa a variável temporária do escopo global

// Configurações de segurança
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hora

// Configurações de paginação
define('ITEMS_PER_PAGE', 10);

// Configurações de upload
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Configurações de estoque
define('LOW_STOCK_THRESHOLD', 10);
define('CRITICAL_STOCK_THRESHOLD', 5);

// Perfis de usuário
define('USER_ROLES', [
    'admin'   => 'Administrador',
    'manager' => 'Gerenciador',
    'operator'=> 'Usuário'
]);

// Tipos de movimentação de estoque
define('STOCK_MOVEMENT_TYPES', [
    'entry'      => 'Entrada',
    'exit'       => 'Saída',
    'adjustment' => 'Ajuste',
    'return'     => 'Devolução',
    'loss'       => 'Perda'
]);

// Status de produtos
define('PRODUCT_STATUS', [
    'active'       => 'Ativo',
    'inactive'     => 'Inativo',
    'discontinued' => 'Emprestado'
]);