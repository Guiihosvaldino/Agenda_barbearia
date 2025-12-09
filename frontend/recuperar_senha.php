<?php
require_once("../backend/cliente.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $celular = $_POST['celular'];

    $cliente = buscarClientePorCelular($celular);

    if ($cliente) {
        header("Location: redefinir_senha.php?id=" . $cliente['id_cliente']);
        exit;
    } else {
        $msg = "❌ Celular não encontrado!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
</head>
<body>

<h2>Recuperar Senha</h2>

<form method="POST">

    <?php if ($msg): ?>
        <p class="erro"><?= $msg ?></p>
    <?php endif; ?>

    <label>Digite seu celular:</label>
    <input type="text" name="celular" required>

    <button type="submit">Continuar</button>

</form>

<style>
body {
    background: linear-gradient(135deg, #1e3c72 0%, #fff 100%);
    font-family: Arial;
}

form {
    background: #fff;
    max-width: 350px;
    margin: 40px auto;
    padding: 28px;
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input, button {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #222;
    font-size: 1em;
}

button {
    background: #1e3c72;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}

.erro {
    text-align: center;
    color: red;
    font-weight: bold;
}
</style>
</body>
</html>
