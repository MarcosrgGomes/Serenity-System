<?php
$pageTitle = 'Novo Fornecedor';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>
    
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">Novo Fornecedor</h1>
            </div>
            <div class="topbar-right">
                <a href="index.php?page=suppliers" class="btn btn-secondary">← Voltar</a>
                <div class="user-menu">
                    <div class="user-avatar"><?php echo strtoupper(substr(getCurrentUser()['name'], 0, 1)); ?></div>
                </div>
            </div>
        </header>
        
        <div class="content-area">
            <div class="card">
                <div class="card-body">
                    <form action="index.php?page=suppliers&action=save" method="POST" data-validate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-6);">
                            <div class="form-group">
                                <label for="name" class="form-label required">Nome</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cnpj" class="form-label">CNPJ</label>
                                <input type="text" id="cnpj" name="cnpj" class="form-control" data-mask="cnpj">
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" id="phone" name="phone" class="form-control" data-mask="phone">
                            </div>
                            
                            <div class="form-group">
                                <label for="city" class="form-label">Cidade</label>
                                <input type="text" id="city" name="city" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="state" class="form-label">Estado</label>
                                <select id="state" name="state" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="PR">Paraná</option>
                                    <option value="SC">Santa Catarina</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Endereço</label>
                            <input type="text" id="address" name="address" class="form-control">
                        </div>
                        
                        <div style="display: flex; gap: var(--space-3); justify-content: flex-end; margin-top: var(--space-8);">
                            <a href="index.php?page=suppliers" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">💾 Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>



