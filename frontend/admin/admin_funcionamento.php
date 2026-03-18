<?php
require_once("../../backend/funcionamento.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    foreach ($_POST['dia'] as $i => $dia) {

        $abre  = $_POST['abre'][$i];
        $fecha = $_POST['fecha'][$i];
        $ativo = isset($_POST['ativo'][$i]) ? 1 : 0;

        salvarFuncionamento($dia, $abre, $fecha, $ativo);
    }

    $msg = "Funcionamento atualizado com sucesso!";
}

$funcionamento = listarFuncionamento();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Funcionamento 💈</title>

<style>
    body {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 0;
        color: #fff;
        }

/* MENU SUPERIOR */
     .menu {
    background: #0d2a52;
    padding: 14px 25px;
    display: flex;
    gap: 25px;
    font-size: 1.1em;
    position: sticky;
    top: 0;
    z-index: 50;
}

.menu a {
    color: #fff;
    text-decoration: none;
    padding: 6px 12px;
    font-weight: bold;
    border-radius: 6px;
}

.menu a:hover {
    background: #1e3c72;
}

/* CONTAINER */
.container {
    background: #ffffff;
    max-width: 850px;
    margin: 40px auto;
    padding: 30px 35px;
    border-radius: 16px;
    color: #222;
    box-shadow: 0 8px 22px rgba(0,0,0,0.25);
}

h2 {
    text-align: center;
    color: #1e3c72;
    margin-bottom: 25px;
    font-size: 2em;
}

/* MENSAGEM */
.msg {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-left: 5px solid #28a745;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: bold;
    text-align: center;
}

/* TABELA */
table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #1e3c72;
    color: #fff;
    padding: 12px;
}

td {
    padding: 12px;
    text-align: center;
}

tr:nth-child(even) { background: #f6f6f6; }
tr:nth-child(odd) { background: #ffffff; }

/* INPUTS */
input[type="time"] {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #999;
}

/* BOTÃO */
button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 1.1em;
    cursor: pointer;
    font-weight: bold;
    transition: 0.2s;
}

button:hover {
    transform: scale(1.03);
    background: linear-gradient(135deg, #2a5298, #1e3c72);
}
    .logo {
        width: 350px;   /* quase o tamanho do formulário */
        margin-bottom: -100px;
        margin-left: auto;
        margin-right: auto;
        display: block;
        }
</style>

</style>
</head>
<body>
<div class="menu">
    <a href="agenda.php">📅 Agenda</a>
    <a href="financeiro.php">💰 Financeiro</a>
    <a href="admin_profissionais.php">👤 Profissionais</a>
    <a href="admin_servicos.php">💈 Serviços</a>
    <a href="admin_funcionamento.php">⏰ Funcionamento</a>
    <a href="login_admin.php">🚪 Sair</a>
</div>


<div class="container">
    
    <img src="../../frontend/imagem/logo1.png" class="logo">

<h2>Funcionamento da Barbearia</h2>
<?php if ($msg): ?>
<div class="msg"><?= $msg ?></div>
<?php endif; ?>


<form method="POST">

<table border="1" cellpadding="8">
<tr>
<th>Dia</th>
<th>Abre</th>
<th>Fecha</th>
<th>Ativo</th>
</tr>

<?php foreach ($funcionamento as $i => $f): ?>
<tr>
<td>
    <?= $f['dia_semana'] ?>
    <input type="hidden" name="dia[]" value="<?= $f['dia_semana'] ?>">
</td>

<td>
<input type="time" name="abre[]" value="<?= $f['abre'] ?>">
</td>

<td>
<input type="time" name="fecha[]" value="<?= $f['fecha'] ?>">
</td>

<td>
<input type="checkbox" name="ativo[<?= $i ?>]" <?= $f['ativo'] ? 'checked' : '' ?>>
</td>
</tr>
<?php endforeach; ?>

</table>

<br>
<button type="submit">Salvar</button>

</form>

</div>
</body>
</html>
