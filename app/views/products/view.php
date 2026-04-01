<?php
// Linhas de debug removidas daqui
$pageTitle = 'Detalhes do Produto';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>
    
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">Detalhes do Produto</h1>
            </div>
            <div class="topbar-right">
                <a href="index.php?page=products" class="btn btn-secondary">← Voltar</a>
                <a href="index.php?page=products&action=edit&id=<?php echo $product['id']; ?>" class="btn btn-primary">✏️ Editar</a>
                <div class="user-menu">
                    <div class="user-avatar"><?php echo strtoupper(substr(getCurrentUser()['name'], 0, 1)); ?></div>
                </div>
            </div>
        </header>
        
        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <span class="badge badge-<?php echo $product['status'] === 'active' ? 'success' : 'neutral'; ?>">
                        <?php echo PRODUCT_STATUS[$product['status']] ?? htmlspecialchars($product['status']); ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php
                    $stockLevel = productStockAlertLevel($product);
                    if ($stockLevel === 'critical'): ?>
                        <div class="alert alert-error" style="margin-bottom: var(--space-6);">
                            <strong>Estoque crítico:</strong> a quantidade atual (<?php echo formatNumber((int) $product['quantity']); ?>) está no ou abaixo do limite crítico global (≤ <?php echo formatNumber(CRITICAL_STOCK_THRESHOLD); ?> unidades). Repor o quanto antes.
                        </div>
                    <?php elseif ($stockLevel === 'low'): ?>
                        <div class="alert alert-warning" style="margin-bottom: var(--space-6);">
                            <strong>Estoque baixo:</strong> a quantidade atual (<?php echo formatNumber((int) $product['quantity']); ?>) está no ou abaixo do mínimo definido (<?php echo formatNumber((int) $product['min_quantity']); ?>). Considere repor.
                        </div>
                    <?php endif; ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--space-6);">
                        <div>
                            <strong>SKU:</strong><br>
                            <code style="font-size: var(--text-lg);"><?php echo htmlspecialchars($product['sku']); ?></code>
                        </div>
                        
                        <div>
                            <strong>Categoria:</strong><br>
                            <?php echo htmlspecialchars($product['category_name'] ?? 'Sem categoria', ENT_QUOTES, 'UTF-8'); ?>

                        </div>

                        <div>
                            <strong>Fornecedor:</strong><br>
                          <?= htmlspecialchars($product['supplier_name'] ?? 'Sem fornecedor', ENT_QUOTES, 'UTF-8') ?>


                        </div>
                        
                        <div>
                            <strong>Preço de Venda:</strong><br>
                            <span style="font-size: var(--text-xl); font-weight: 700; color: var(--success);">
                                <?php echo formatMoney((float) ($product['sale_price'] ?? 0)); ?>
                            </span>
                        </div>
                        <div>
                            <strong>Quantidade em Estoque:</strong><br>
                            <span style="font-size: var(--text-xl); font-weight: 700; color: <?php echo $stockLevel === 'critical' ? 'var(--error)' : ($stockLevel === 'low' ? 'var(--warning)' : 'inherit'); ?>;">
                                <?php echo formatNumber((int) $product['quantity']); ?>
                            </span>
                            <?php if ($stockLevel === 'critical'): ?>
                                <span class="badge badge-error" style="margin-left: var(--space-2);">Crítico</span>
                            <?php elseif ($stockLevel === 'low'): ?>
                                <span class="badge badge-warning" style="margin-left: var(--space-2);">Baixo</span>
                            <?php else: ?>
                                <span class="badge badge-success" style="margin-left: var(--space-2);">OK</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong>Quantidade Mínima:</strong><br>
                            <?php echo formatNumber((int) $product['min_quantity']); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($product['description'])): ?>
                    <div style="margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid var(--neutral-200);">
                        <strong>Descrição:</strong><br>
                        <p style="margin-top: var(--space-2);"><?php echo nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($productMovements)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Histórico de Movimentações</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Estoque Anterior</th>
                                    <th>Estoque Novo</th>
                                    <th>Usuário</th>
                                    <th class="table-actions-col">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productMovements as $movement): ?>
                                <tr>
                                    <td><?php echo formatDate($movement['date']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $movement['type'] === 'entry' ? 'success' : 'info'; ?>">
                                            <?php echo htmlspecialchars(STOCK_MOVEMENT_TYPES[$movement['type']] ?? $movement['type'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $movement['quantity']; ?></td>
                                    <td><?php echo $movement['old_quantity']; ?></td>
                                    <td><?php echo $movement['new_quantity']; ?></td>
                                    <td><?php echo htmlspecialchars($movement['user_name'] ?? '—'); ?></td>
                                    <td class="table-actions-col">
                                        <a href="index.php?page=stock&amp;action=deleteMovement&amp;id=<?php echo (int) $movement['id']; ?>&amp;csrf_token=<?php echo generateCsrfToken(); ?>&amp;return=view&amp;product_id=<?php echo (int) $product['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           data-confirm="Excluir esta movimentação e reverter o estoque? Só é permitido se for a última alteração de saldo deste produto."
                                           title="Excluir">🗑️</a>
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