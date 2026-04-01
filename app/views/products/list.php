<?php
$pageTitle = 'Produtos';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>
    
    <main class="main-content">
        <header class="topbar">
            
            <div class="topbar-left">
                <h1 class="topbar-title">Produtos</h1>
            </div>

            <div class="topbar-right">
                <a href="index.php?page=products&action=add" class="btn btn-primary">
                    ➕ Novo Produto
                </a>
                <div class="user-menu">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr(getCurrentUser()['name'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="content-area">
            <div class="filters-bar">
                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="products">
                    <div class="filters-grid">
                        <div class="form-group mb-0">
                            <label for="search" class="form-label">Pesquisar</label>
                            <input
                                style="width: 250px; height:48px; margin-top:1px;" 
                                type="text" 
                                id="search" 
                                name="search" 
                                class="form-control" 
                                placeholder="Nome, SKU ou descrição..."
                                value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                            >
                        </div>
                        
                        <div class="form-group mb-0">
                            <label for="category" class="form-label">Categoria</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">Todas</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo ($_GET['category'] ?? '') === $cat['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-0">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">Todos</option>
                                <?php foreach (PRODUCT_STATUS as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($_GET['status'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-0">
                            <label for="stock_alert" class="form-label">Alerta de Estoque</label>
                            <select id="stock_alert" name="stock_alert" class="form-control">
                                <option value="">Todos</option>
                                <option value="low" <?php echo ($_GET['stock_alert'] ?? '') === 'low' ? 'selected' : ''; ?>>Estoque Baixo</option>
                                <option value="critical" <?php echo ($_GET['stock_alert'] ?? '') === 'critical' ? 'selected' : ''; ?>>Crítico</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top: var(--space-4); display: flex; gap: var(--space-3);">
                        <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                        <a href="index.php?page=products" class="btn btn-secondary">🔄 Limpar</a>
                    </div>
                </form>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista de Produtos (<?php echo count($products); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <div class="alert alert-info">
                            Nenhum produto encontrado. <a href="index.php?page=products&action=add">Cadastre o primeiro produto</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th data-sortable>SKU</th>
                                        <th data-sortable>Produto</th>
                                        <th data-sortable>Categoria</th>
                                        <th data-sortable>Fornecedor</th>
                                        <th data-sortable>Preço de Venda</th>
                                        <th data-sortable>Quantidade</th>
                                        <th data-sortable>Mín.</th>
                                        <th>Alerta</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($product['sku']); ?></code></td>
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></td>
                                        
                                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'Sem fornecedor'); ?></td>
                                        <td><?php echo formatMoney((float) ($product['sale_price'] ?? 0)); ?></td>
                                        <?php
                                        $stockLevel = productStockAlertLevel($product);
                                        $qtyColor = $stockLevel === 'critical'
                                            ? 'var(--error)'
                                            : ($stockLevel === 'low' ? 'var(--warning)' : 'var(--success)');
                                        ?>
                                        <td>
                                            <span style="font-weight: 700; color: <?php echo $qtyColor; ?>;">
                                                <?php echo formatNumber((int) ($product['quantity'] ?? 0)); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatNumber((int) ($product['min_quantity'] ?? 0)); ?></td>
                                        <td>
                                            <?php if ($stockLevel === 'critical'): ?>
                                                <span class="badge badge-error">Crítico</span>
                                            <?php elseif ($stockLevel === 'low'): ?>
                                                <span class="badge badge-warning">Baixo</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">OK</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $product['status'] === 'active' ? 'success' : 'neutral'; ?>">
                                                <?php echo PRODUCT_STATUS[$product['status']] ?? htmlspecialchars($product['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: var(--space-2);">
                                                <a href="index.php?page=products&action=view&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary" title="Ver detalhes">
                                                    👁️
                                                </a>
                                                <a href="index.php?page=products&action=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    ✏️
                                                </a>
                                                <a href="index.php?page=products&action=delete&id=<?php echo $product['id']; ?>&csrf_token=<?php echo generateCsrfToken(); ?>" class="btn btn-sm btn-danger" data-confirm="Tem certeza que deseja excluir este produto?" title="Excluir">
                                                    🗑️
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>