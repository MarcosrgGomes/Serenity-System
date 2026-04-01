<?php
$pageTitle = 'Categorias';
require_once APP_PATH . '/views/templates/header.php';
?>

<div class="app-container">
    <?php require_once APP_PATH . '/views/templates/navigation.php'; ?>
    
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">Categorias</h1>
            </div>
            <div class="topbar-right">
                <div class="user-menu">
                    <div class="user-avatar"><?php echo strtoupper(substr(getCurrentUser()['name'], 0, 1)); ?></div>
                </div>
            </div>
        </header>
        
        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista de Categorias</h3>
                    <!-- Futuramente, podemos adicionar um botão de "Nova Categoria" aqui -->
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <!-- <th>Ações</th> (Para Editar/Excluir no futuro) -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Verifica se há categorias antes de fazer o loop -->
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="1">Nenhuma categoria encontrada.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <!-- [LINHA CORRIGIDA] Mostra apenas o nome -->
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($category['name']); ?></td>
                                        
                                        <!-- 
                                            [LINHAS REMOVIDAS]
                                            As colunas "Descrição" e "Status" foram removidas
                                            para corrigir o erro 'Undefined array key'.
                                        -->
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