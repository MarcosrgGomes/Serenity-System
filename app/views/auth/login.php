<?php
$pageTitle = 'Login';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="auth-container">
    <div class="auth-card fade-in">
        <div class="auth-header">
            <h1 class="auth-logo">📦 Serenity</h1>
            <p class="auth-subtitle">Sistema de Gerenciamento de Estoque</p>
        </div>
        
        <form action="index.php?page=login&action=authenticate" method="POST" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="email" class="form-label required">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="seu@email.com"
                    required
                    autocomplete="email"
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label required">Senha</label>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <button 
                        type="button" 
                        data-toggle-password="#password"
                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2em;"
                    >👁️</button>
                </div>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Lembrar-me</label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Entrar
            </button>
        </form>
        
        <div class="auth-footer">
            <p style="color: var(--neutral-600); margin-bottom: var(--space-3);">
                Não tem uma conta? 
                <a href="index.php?page=register" style="color: var(--primary-600); font-weight: 600;">
                    Cadastre-se
                </a>
            </p>
            <p style="color: var(--neutral-600);">
                <a href="index.php?page=forgot-password" style="color: var(--neutral-600); text-decoration: underline;">
                    Esqueceu sua senha?
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/templates/footer.php'; ?>

