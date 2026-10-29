<?php
require_once("../../backend/funcionamento.php");
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Funcionamento 💈</title>

<style>
    body {
        background-image: url('../imagem/FUNDO.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 0;
        color: #fff;
        }
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 0;
    }

/* MENU SUPERIOR */
.menu {
    background: #380309;
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
    background: #660a18;
}

/* CONTAINER */
.container {
    position: relative;
    z-index: 1;
    background: #ffffff;
    max-width: 900px;
    margin: 40px auto;
    padding: 30px 35px;
    border-radius: 16px;
    color: #222;
    box-shadow: 0 8px 22px rgba(0,0,0,0.25);
}

h2 {
    text-align: center;
    color: #660a18;
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
    background: #660a18;
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
    background: linear-gradient(135deg, #660a18, #8a1224);
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
    background: linear-gradient(135deg, #8a1224, #660a18);
}
    .logo {
        width: 350px;   /* quase o tamanho do formulário */
        margin-bottom: -70px;
        margin-left: auto;
        margin-right: auto;
        display: block;
        }

/* ========== RESPONSIVIDADE (CELULARES) ========== */
@media (max-width: 768px) {
    .container {
        width: 90% !important;
        max-width: none !important;
        margin: 20px auto !important;
        padding: 20px !important;
    }
    .menu {
        flex-direction: column !important;
        align-items: center !important;
        gap: 10px !important;
        padding: 15px !important;
    }
    .menu a {
        width: 100% !important;
        text-align: center !important;
        box-sizing: border-box !important;
    }
    .logo {
        width: 100% !important;
        max-width: 250px !important;
        margin-bottom: -40px !important;
    }
    table {
        display: block !important;
        overflow-x: auto !important;
        white-space: nowrap !important;
    }
}
</style>

</style>
    <link rel="stylesheet" href="../css/menu-lateral.css">
</head>
<body>
<?php require __DIR__ . '/menu_lateral.php'; ?>


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
    <strong><?= $f['dia_semana'] ?></strong>
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
<button type="submit">Salvar Alterações</button>

</form>

</div>
</body>
</html>
