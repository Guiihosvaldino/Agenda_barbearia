<?php
require_once("../../backend/admin.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['dia'] as $dia => $dados) {
        salvarFuncionamento(
            $dia,
            $dados['abre'],
            $dados['fecha'],
            isset($dados['ativo']) ? 1 : 0
        );
    }
}

$funcionamento = listarFuncionamento();
?>
<div class="container">
<h2>⏰ Horário de Funcionamento</h2>

<form method="POST">
<?php foreach ($funcionamento as $f): ?>
    <div class="linha">
        <b><?= $f['dia_semana'] ?></b>

        <input type="time" name="dia[<?= $f['dia_semana'] ?>][abre]" value="<?= $f['abre'] ?>">
        <input type="time" name="dia[<?= $f['dia_semana'] ?>][fecha]" value="<?= $f['fecha'] ?>">

        <label>
            <input type="checkbox" name="dia[<?= $f['dia_semana'] ?>][ativo]" <?= $f['ativo'] ? 'checked' : '' ?>>
            Aberto
        </label>
    </div>
<?php endforeach; ?>

<button>Salvar</button>
</form>
</div>
