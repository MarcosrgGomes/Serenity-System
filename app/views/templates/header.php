<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo APP_DESCRIPTION; ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    
    <link rel="stylesheet" href="public/css/style.css">
    
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📦</text></svg>">

    <script src="public/js/app.js" defer></script>
</head>
<body> <?php
    $flashMessage = getFlashMessage();
    if ($flashMessage):
    ?>
    <?php
        // Valida o tipo contra whitelist para evitar injeção de classe CSS
        $allowedAlertTypes = ['success', 'error', 'warning', 'info'];
        $alertType = in_array($flashMessage['type'], $allowedAlertTypes) ? $flashMessage['type'] : 'info';
        // A mensagem pode conter <br> intencional (lista de erros) — escapa tudo exceto <br>
        $safeMessage = nl2br(htmlspecialchars(strip_tags($flashMessage['message'], '<br>'), ENT_QUOTES, 'UTF-8'));
    ?>
    <div class="alert alert-<?php echo $alertType; ?> fade-in flash-toast" style="position: fixed; z-index: 9999;" data-auto-close="5000">
        <?php echo $safeMessage; ?>
    </div>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const closeBtn = document.getElementById('sidebar-close');

        function openMenu() {
            if (!sidebar) return;
            sidebar.classList.add('active');
            if (overlay) overlay.classList.add('active');
            if (hamburger) hamburger.setAttribute('aria-expanded', 'true');
            if (overlay) overlay.setAttribute('aria-hidden', 'false');
        }

        function closeMenu() {
            if (!sidebar) return;
            sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            if (hamburger) hamburger.setAttribute('aria-expanded', 'false');
            if (overlay) overlay.setAttribute('aria-hidden', 'true');
        }

        if (hamburger && sidebar) {
            hamburger.addEventListener('click', function () {
                if (sidebar.classList.contains('active')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });
        }
        if (closeBtn && sidebar) {
            closeBtn.addEventListener('click', closeMenu);
        }
        if (overlay) {
            overlay.addEventListener('click', closeMenu);
        }
        if (sidebar) {
            sidebar.querySelectorAll('a.sidebar-nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.matchMedia('(max-width: 768px)').matches) {
                        closeMenu();
                    }
                });
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMenu();
        });
    });
    </script>
