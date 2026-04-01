<?php
$pageTitle = 'Inventário';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">Inventário</h1>
            </div>
            <div class="topbar-right">
                <div class="user-menu">
                    <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr(getCurrentUser()['name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        </header>

        <div class="content-area">
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Valor em Estoque (Custo)</div>
                            <div class="stat-value"><?= formatMoney(isset($stats['total_stock_value']) && is_numeric($stats['total_stock_value']) ? (float) $stats['total_stock_value'] : 0) ?></div>
                        </div>
                        <div class="stat-icon" style="background-color: var(--success-light); color: var(--success);">💰</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Total de Itens</div>
                            <div class="stat-value"><?= formatNumber(isset($stats['total_items']) && is_numeric($stats['total_items']) ? (int) $stats['total_items'] : 0) ?></div>
                        </div>
                        <div class="stat-icon">📦</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Quantidade Total</div>
                            <div class="stat-value"><?= formatNumber(isset($stats['total_quantity']) && is_numeric($stats['total_quantity']) ? (int) $stats['total_quantity'] : 0) ?></div>
                        </div>
                        <div class="stat-icon">📊</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Produtos em Estoque</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Mín.</th>
                                    <th>Alerta</th>
                                    <th>Preço Custo</th>
                                    <th>Preço de Venda</th>
                                    <th>Valor Total (Custo)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center;">Nenhum produto em estoque encontrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <?php $invStock = productStockAlertLevel($product); ?>
                                        <tr>
                                            <td style="font-weight: 600;"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td style="font-weight: 600; color: <?= $invStock === 'critical' ? 'var(--error)' : ($invStock === 'low' ? 'var(--warning)' : 'inherit') ?>;">
                                                <?= formatNumber(is_numeric($product['quantity']) ? (int) $product['quantity'] : 0) ?>
                                            </td>
                                            <td><?= formatNumber(is_numeric($product['min_quantity'] ?? 0) ? (int) $product['min_quantity'] : 0) ?></td>
                                            <td>
                                                <?php if ($invStock === 'critical'): ?>
                                                    <span class="badge badge-error">Crítico</span>
                                                <?php elseif ($invStock === 'low'): ?>
                                                    <span class="badge badge-warning">Baixo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">OK</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= formatMoney(is_numeric($product['cost_price']) ? (float) $product['cost_price'] : 0) ?></td>
                                            <td><?= formatMoney(is_numeric($product['sale_price']) ? (float) $product['sale_price'] : 0) ?></td>
                                            <td style="font-weight: 700; color: var(--success);">
                                                <?php
                                                    $cost_price = is_numeric($product['cost_price']) ? (float) $product['cost_price'] : 0;
                                                    $quantity = is_numeric($product['quantity']) ? (int) $product['quantity'] : 0;
                                                    echo formatMoney($cost_price * $quantity);
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
