<?php
$paginaAtual = basename($_SERVER['PHP_SELF']);
$itensMenu = [
    ['profissional_home.php', '🏠', 'Home'],
    ['profissional_agenda.php', '📅', 'Minha Agenda'],
    ['profissional_pacotes.php', '📦', 'Pacotes'],
    ['profissional_perfil.php', '👤', 'Meu Perfil'],
    ['profissional_estatistica.php', '📊', 'Estatísticas'],
];
?>
<button class="menu-lateral-toggle" type="button" aria-label="Abrir menu" aria-controls="menu-lateral" aria-expanded="false">
    <span></span>
    <span></span>
    <span></span>
</button>
<button class="menu-lateral-fundo" type="button" aria-label="Fechar menu" hidden></button>
<nav class="menu-lateral" id="menu-lateral" aria-label="Menu do profissional" hidden>
    <div class="menu-lateral-cabecalho">
        <strong>Menu do profissional</strong>
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
        <a class="menu-lateral-sair" href="profissional_login.php">
            <span aria-hidden="true">🚪</span>
            Sair
        </a>
    </div>
</nav>
<script src="../js/menu-lateral.js" defer></script>
