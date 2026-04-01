<?php
$pageTitle = 'Recuperar Senha';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="auth-container">
    <div class="auth-card fade-in">
        <div class="auth-header">
            <h1 class="auth-logo">📦 Serenity</h1>
            <p class="auth-subtitle">Recuperar senha</p>
        </div>
        
        <form action="index.php?page=forgot-password&action=sendResetLink" method="POST" data-validate>
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
                >
                <small class="form-text">Enviaremos um link de recuperação para este email</small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Enviar Link de Recuperação
            </button>
        </form>
        
        <div class="auth-footer">
            <p style="color: var(--neutral-600);">
                <a href="index.php?page=login" style="color: var(--primary-600); font-weight: 600;">
                    ← Voltar para login
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/templates/footer.php'; ?>
