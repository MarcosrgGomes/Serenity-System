<?php
/**
 * Sidebar de navegação.
 *
 * Usa $currentPage (derivada do $_GET['page'] do index.php) para
 * marcar o link ativo com a classe CSS sidebar-nav-link--active.
 * Sem isso, o usuário não tem feedback visual de onde está.
 */
$_navPage   = $_GET['page']   ?? 'dashboard';
$_navAction = $_GET['action'] ?? '';

/**
 * Retorna 'sidebar-nav-link active' se a rota coincide com a página/ação atual,
 * ou apenas 'sidebar-nav-link'.
 * A classe 'active' corresponde ao seletor CSS: .sidebar-nav-link.active
 */
function navActive(string $page, string $action = ''): string {
    global $_navPage, $_navAction;
    $pageMatch   = $_navPage === $page;
    $actionMatch = $action === '' || $_navAction === $action;
    return 'sidebar-nav-link' . ($pageMatch && $actionMatch ? ' active' : '');
}
?>

<button type="button" class="sidebar-toggle-btn" id="hamburger" aria-label="Abrir menu" aria-expanded="false" aria-controls="sidebar">☰</button>
<div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button type="button" class="sidebar-close-btn" id="sidebar-close" aria-label="Fechar menu">&times;</button>
        <div class="sidebar-logo">📦 Serenity</div>
        <p style="color: rgba(255,255,255,0.7); font-size: var(--text-sm); margin-top: var(--space-2);">
            Sistema de Estoque
        </p>
    </div>

    <nav>
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="index.php?page=dashboard" class="<?= navActive('dashboard') ?>">
                    <span class="sidebar-nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="index.php?page=products" class="<?= navActive('products') ?>">
                    <span class="sidebar-nav-icon">📦</span>
                    <span>Produtos</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="index.php?page=suppliers" class="<?= navActive('suppliers') ?>">
                    <span class="sidebar-nav-icon">🏢</span>
                    <span>Fornecedores</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="index.php?page=stock&action=movements" class="<?= navActive('stock', 'movements') ?>">
                    <span class="sidebar-nav-icon">📋</span>
                    <span>Movimentações</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="index.php?page=stock&action=inventory" class="<?= navActive('stock', 'inventory') ?>">
                    <span class="sidebar-nav-icon">📊</span>
                    <span>Inventário</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="index.php?page=stock&action=lowStock" class="<?= navActive('stock', 'lowStock') ?>">
                    <span class="sidebar-nav-icon">⚠️</span>
                    <span>Estoque Baixo</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="index.php?page=categories" class="<?= navActive('categories') ?>">
                    <span class="sidebar-nav-icon">🏷️</span>
                    <span>Categorias</span>
                </a>
            </li>

            <?php if (hasPermission('admin')): ?>
            <li class="sidebar-nav-item" style="margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid rgba(255,255,255,0.1);">
                <a href="index.php?page=users" class="<?= navActive('users') ?>">
                    <span class="sidebar-nav-icon">👥</span>
                    <span>Usuários</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="sidebar-nav-item">
                <a href="index.php?page=logout&action=logout"
                   class="sidebar-nav-link"
                   data-confirm="Tem certeza que deseja sair?">
                    <span class="sidebar-nav-icon">🚪</span>
                    <span>Sair</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
