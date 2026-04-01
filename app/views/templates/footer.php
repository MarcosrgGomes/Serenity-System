<footer class="footer">
    <div class="footer-content">
        <div class="footer-section about">
            <h4>Sobre este Sistema</h4>
            <p>Sistema de Gerenciamento de Estoque (SGE).</p>
        </div>
        <div class="footer-section contact">
            <h4>📦 Serenity</h4>
            <ul>
                <li>Rua Benedito Montenegro, 527 — Pq. Marajoara, Santo André - SP</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?php echo date('Y'); ?> Serenity — Sistema de Gerenciamento de Estoque. Todos os direitos reservados.
    </div>
</footer>

<?php
/*
 * app.js já é carregado no header.php com `defer`.
 * NÃO recarregar aqui — causaria dois registros de event listeners
 * (toggle-password, confirmações, auto-close de alertas, etc.).
 */
?>
</body>
</html>
