<?php
$paginaAtual = basename($_SERVER['PHP_SELF']);
$itensMenu = [
    ['agenda.php', '📅', 'Agenda'],
    ['financeiro.php', '💰', 'Financeiro'],
    ['admin_pacotes.php', '📦', 'Pacotes'],
    ['admin_profissionais.php', '👤', 'Profissionais'],
    ['admin_servicos.php', '💈', 'Serviços'],
    ['admin_funcionamento.php', '⏰', 'Funcionamento'],
    ['assinatura.php', '⭐', 'Assinatura'],
];
?>
<button class="menu-lateral-toggle" type="button" aria-label="Abrir menu" aria-controls="menu-lateral" aria-expanded="false">
    <span></span>
    <span></span>
    <span></span>
</button>
<button class="menu-lateral-fundo" type="button" aria-label="Fechar menu" hidden></button>
<nav class="menu-lateral" id="menu-lateral" aria-label="Menu do administrador" hidden>
    <div class="menu-lateral-cabecalho">
        <strong>Menu do administrador</strong>
        <button class="menu-lateral-fechar" type="button" aria-label="Fechar menu">&times;</button>
    </div>
    <div class="menu-lateral-links">
        <?php foreach ($itensMenu as [$pagina, $icone, $rotulo]): ?>
            <a href="<?= htmlspecialchars($pagina, ENT_QUOTES, 'UTF-8') ?>"
               <?= $paginaAtual === $pagina ? 'class="ativo" aria-current="page"' : '' ?>>
                <span aria-hidden="true"><?= $icone ?></span>
                <?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
        <a class="menu-lateral-sair" href="login_admin.php">
            <span aria-hidden="true">🚪</span>
            Sair
        </a>
    </div>
</nav>
<script src="../js/menu-lateral.js" defer></script>
