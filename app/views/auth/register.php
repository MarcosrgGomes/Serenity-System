<?php
$pageTitle = 'Cadastro';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="auth-container">
    <div class="auth-card fade-in">
        <div class="auth-header">
            <h1 class="auth-logo">📦 Serenity</h1>
            <p class="auth-subtitle">Criar nova conta</p>
        </div>
        
        <form action="index.php?page=register&action=doRegister" method="POST" data-validate>

            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="name" class="form-label required">Nome Completo</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control" 
                    placeholder="Seu nome completo"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label required">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="seu@email.com"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label required">Senha</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="••••••••"
                    required
                    data-strong-password
                >
                <small class="form-text">Mínimo 8 caracteres, 1 maiúscula, 1 minúscula e 1 número</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="form-label required">Confirmar Senha</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="form-control" 
                    placeholder="••••••••"
                    required
                    data-confirm-password="#password"
                >
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Criar Conta
            </button>
        </form>
        
        <div class="auth-footer">
            <p style="color: var(--neutral-600);">
                Já tem uma conta? 
                <a href="index.php?page=login" style="color: var(--primary-600); font-weight: 600;">
                    Faça login
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/templates/footer.php'; ?>
