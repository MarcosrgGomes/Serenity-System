<?php
$pageTitle = 'Novo Produto';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">Novo Produto</h1>
            </div>
            <div class="topbar-right">
                <a href="index.php?page=products" class="btn btn-secondary">
                    ← Voltar
                </a>
                <div class="user-menu">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr(getCurrentUser()['name'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </header>

        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informações do Produto</h3>
                </div>
                <div class="card-body">
                    <form action="index.php?page=products&action=save" method="POST" data-validate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-6);">
                            <div class="form-group">
                                <label for="sku" class="form-label">SKU</label>
                                <input
                                    type="text"
                                    id="sku"
                                    name="sku"
                                    class="form-control"
                                    placeholder="Deixe em branco para gerar automaticamente"
                                    value="<?php echo generateSKU(); ?>">
                                <small class="form-text">Código único de identificação do produto</small>
                            </div>

                            <div class="form-group">
                                <label for="name" class="form-label required">Nome do Produto</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    placeholder="Ex: Notebook Dell Inspiron"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="category" class="form-label required">Categoria</label>
                                <select id="category" name="category" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="supplier_id">Fornecedor</label>
                                <select name="supplier_id" id="supplier_id" class="form-control" required>
                                    <option value="">Selecione um fornecedor...</option>

                                    <?php if (!empty($suppliers)): ?>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?php echo $supplier['id']; ?>">
                                                <?php echo htmlspecialchars($supplier['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                </select>
                            </div>

                            <div class="form-group">
                                <label for="cost_price" class="form-label required">Preço de Custo</label>
                                <input
                                    type="number"
                                    id="cost_price"
                                    name="cost_price"
                                    class="form-control"
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="sale_price" class="form-label required">Preço de Venda</label>
                                <input
                                    type="number"
                                    id="sale_price"
                                    name="sale_price"
                                    class="form-control"
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0"
                                    required>
                                <small class="form-text">
                                    Markup: <span id="markup_display" style="font-weight: 700;">0%</span>
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="quantity" class="form-label required">Quantidade Inicial</label>
                                <input
                                    type="number"
                                    id="quantity"
                                    name="quantity"
                                    class="form-control"
                                    placeholder="0"
                                    min="0"
                                    value="0"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="min_quantity" class="form-label required">Quantidade Mínima</label>
                                <input
                                    type="number"
                                    id="min_quantity"
                                    name="min_quantity"
                                    class="form-control"
                                    placeholder="<?php echo LOW_STOCK_THRESHOLD; ?>"
                                    min="0"
                                    value="<?php echo LOW_STOCK_THRESHOLD; ?>"
                                    required>
                                <small class="form-text">Alerta quando atingir este valor</small>
                            </div>

                            <div class="form-group">
                                <label for="status" class="form-label required">Status</label>
                                <select id="status" name="status" class="form-control" required>
                                    <?php foreach (PRODUCT_STATUS as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $key === 'active' ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea
                                id="description"
                                name="description"
                                class="form-control"
                                rows="4"
                                placeholder="Descrição detalhada do produto..."></textarea>
                        </div>

                        <div style="display: flex; gap: var(--space-3); justify-content: flex-end; margin-top: var(--space-8);">
                            <a href="index.php?page=products" class="btn btn-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                💾 Salvar Produto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Validação adicional de fornecedor
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[data-validate]');
        const supplierSelect = document.getElementById('supplier_id');

        form.addEventListener('submit', function(e) {
            if (!supplierSelect.value) {
                e.preventDefault();
                alert('Por favor, selecione um fornecedor!');
                supplierSelect.focus();
                return false;
            }
        });
    });
</script>