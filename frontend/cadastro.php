<?php
require_once("../backend/cliente.php");
session_start();

$celular = $_GET['celular'] ?? null;
if (!$celular) {
    header("Location: index.php");
    exit;
}

$cliente = buscarClientePorCelular($celular);
if ($cliente) {
    $_SESSION['cliente_id'] = $cliente['id_cliente'];
    header("Location: agendamento.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST['nome'];
    $data = $_POST['data_nascimento'];
    $senha = $_POST['senha'];

    $id = cadastrarCliente($nome, $celular, $senha, $data);

    $_SESSION['cliente_id'] = $id;
    header("Location: agendamento.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro - Barbearia</title>
</head>
<body>

<h2>Cadastro de Cliente</h2>

<form method="POST">
    <label>Nome:</label>
    <input type="text" name="nome" required>

    <label>Data de Nascimento:</label>
    <input type="date" name="data_nascimento" required>

    <label>Senha:</label>
    <input type="password" name="senha" required>

    <button type="submit">Cadastrar</button>
</form>

<style>
    body {
        background: linear-gradient(135deg, #1e3c72, #fff);
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
        background: #2d89ef;
        color: #fff;
        font-weight: bold;
    }
</style>

</body>
</html>
