<?php
$pageTitle = 'Fornecedores';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>
    
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">Fornecedores</h1>
            </div>
            <div class="topbar-right">
                <a href="index.php?page=suppliers&action=add" class="btn btn-primary">➕ Novo Fornecedor</a>
                <div class="user-menu">
                    <div class="user-avatar"><?php echo strtoupper(substr(getCurrentUser()['name'], 0, 1)); ?></div>
                </div>
            </div>
        </header>
        
        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista de Fornecedores (<?php echo count($suppliers); ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CNPJ</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Cidade/UF</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($supplier['name']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['cnpj']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['city']); ?>/<?php echo htmlspecialchars($supplier['state']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $supplier['status'] === 'active' ? 'success' : 'neutral'; ?>">
                                            <?php echo $supplier['status'] === 'active' ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: var(--space-2);">
                                            <a href="index.php?page=suppliers&action=edit&id=<?php echo $supplier['id']; ?>" class="btn btn-sm btn-primary">✏️</a>
                                            <a href="index.php?page=suppliers&action=delete&id=<?php echo $supplier['id']; ?>&csrf_token=<?php echo generateCsrfToken(); ?>" class="btn btn-sm btn-danger" data-confirm="Tem certeza que deseja excluir este fornecedor?">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>



