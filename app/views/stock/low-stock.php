<?php
$pageTitle = 'Estoque Baixo';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>
    
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">⚠️ Alertas de Estoque Baixo</h1>
            </div>
            <div class="topbar-right">
                <a href="index.php?page=stock&action=adjustment" class="btn btn-primary">➕ Ajustar Estoque</a>
                <div class="user-menu">
                    <div class="user-avatar"><?php echo strtoupper(substr(getCurrentUser()['name'], 0, 1)); ?></div>
                </div>
            </div>
        </header>
        
        <div class="content-area">
            <?php if (empty($lowStockProducts)): ?>
                <div class="alert alert-success">
                    ✅ Nenhum produto com estoque baixo no momento!
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    ⚠️ <?php echo count($lowStockProducts); ?> produto(s) com estoque abaixo do mínimo
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Produtos com Estoque Baixo</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>SKU</th>
                                        <th>Quantidade Atual</th>
                                        <th>Quantidade Mínima</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><code><?php echo htmlspecialchars($product['sku']); ?></code></td>
                                        <td>
                                            <span style="font-weight: 700; font-size: var(--text-lg); color: <?php 
                                                echo $product['quantity'] <= CRITICAL_STOCK_THRESHOLD ? 'var(--error)' : 'var(--warning)'; 
                                            ?>;">
                                                <?php echo $product['quantity']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $product['min_quantity']; ?></td>
                                        <td>
                                            <?php if ($product['quantity'] <= CRITICAL_STOCK_THRESHOLD): ?>
                                                <span class="badge badge-error">🔴 Crítico</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">🟡 Baixo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="index.php?page=stock&action=adjustment" class="btn btn-sm btn-primary">
                                                Ajustar Estoque
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

