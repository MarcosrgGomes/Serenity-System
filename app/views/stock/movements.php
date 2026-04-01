<?php
$pageTitle = 'Movimentações de Estoque';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>
    
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">Movimentações de Estoque</h1>
            </div>
            <div class="topbar-right">
                <a href="index.php?page=stock&action=adjustment" class="btn btn-primary">➕ Nova Movimentação</a>
                <div class="user-menu">
                    <div class="user-avatar"><?php echo strtoupper(substr(getCurrentUser()['name'], 0, 1)); ?></div>
                </div>
            </div>
        </header>
        
        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Histórico de Movimentações</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($movements)): ?>
                        <div class="alert alert-info">Nenhuma movimentação registrada.</div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Produto</th>
                                        <th>Tipo</th>
                                        <th>Quantidade</th>
                                        <th>Estoque Anterior</th>
                                        <th>Estoque Novo</th>
                                        <th>Usuário</th>
                                        <th class="table-actions-col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($movements as $movement): ?>
                                    <tr>
                                        <td><?php echo formatDate($movement['date']); ?></td>
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($movement['product_name'] ?? '—'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $movement['type'] === 'entry' ? 'success' : 
                                                    ($movement['type'] === 'exit' ? 'info' : 'warning'); 
                                            ?>">
                                                <?php echo htmlspecialchars(STOCK_MOVEMENT_TYPES[$movement['type']] ?? $movement['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $movement['quantity']; ?></td>
                                        <td><?php echo $movement['old_quantity']; ?></td>
                                        <td><?php echo $movement['new_quantity']; ?></td>
                                        <td><?php echo htmlspecialchars($movement['user_name'] ?? '—'); ?></td>
                                        <td class="table-actions-col">
                                            <a href="index.php?page=stock&action=deleteMovement&amp;id=<?php echo (int) $movement['id']; ?>&amp;csrf_token=<?php echo generateCsrfToken(); ?>"
                                               class="btn btn-sm btn-danger"
                                               data-confirm="Excluir esta movimentação e reverter o estoque do produto para o valor anterior? Só é permitido se não houver movimentações mais recentes."
                                               title="Excluir movimentação">🗑️</a>
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


