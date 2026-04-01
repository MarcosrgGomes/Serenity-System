<?php
$pageTitle = 'Editar Fornecedor';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>
    
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">Editar Fornecedor</h1>
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
                    <form action="index.php?page=suppliers&action=update" method="POST" data-validate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
                        
                        <div class="form-grid-2col">
                            <div class="form-group">
                                <label for="name" class="form-label required">Nome</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($supplier['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cnpj" class="form-label">CNPJ</label>
                                <input type="text" id="cnpj" name="cnpj" class="form-control" value="<?php echo htmlspecialchars($supplier['cnpj']); ?>" data-mask="cnpj">
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($supplier['email']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($supplier['phone']); ?>" data-mask="phone">
                            </div>
                            
                            <div class="form-group">
                                <label for="city" class="form-label">Cidade</label>
                                <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($supplier['city']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="state" class="form-label">Estado</label>
                                <select id="state" name="state" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="SP" <?php echo $supplier['state'] === 'SP' ? 'selected' : ''; ?>>São Paulo</option>
                                    <option value="RJ" <?php echo $supplier['state'] === 'RJ' ? 'selected' : ''; ?>>Rio de Janeiro</option>
                                    <option value="MG" <?php echo $supplier['state'] === 'MG' ? 'selected' : ''; ?>>Minas Gerais</option>
                                    <option value="RS" <?php echo $supplier['state'] === 'RS' ? 'selected' : ''; ?>>Rio Grande do Sul</option>
                                    <option value="PR" <?php echo $supplier['state'] === 'PR' ? 'selected' : ''; ?>>Paraná</option>
                                    <option value="SC" <?php echo $supplier['state'] === 'SC' ? 'selected' : ''; ?>>Santa Catarina</option>
                                    <option value="BA" <?php echo $supplier['state'] === 'BA' ? 'selected' : ''; ?>>Bahia</option>
                                    <option value="GO" <?php echo $supplier['state'] === 'GO' ? 'selected' : ''; ?>>Goiás</option>
                                    <option value="DF" <?php echo $supplier['state'] === 'DF' ? 'selected' : ''; ?>>Distrito Federal</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status" class="form-label required">Status</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="active"   <?php echo $supplier['status'] === 'active'   ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="inactive" <?php echo $supplier['status'] === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">Endereço</label>
                                <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($supplier['address']); ?>">
                            </div>
                            </div> <div style="display: flex; gap: var(--space-3); justify-content: flex-end; margin-top: var(--space-8);">
                            <a href="index.php?page=suppliers" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">💾 Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>