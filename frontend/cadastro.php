<?php
require_once("../backend/cliente.php");

$celular = $_GET['celular'] ?? null;
if (!$celular) {
    header("Location: index.php");
    exit;
}

// Verifica se já existe cliente
$cliente = buscarClientePorCelular($celular);
if ($cliente) {
    header("Location: agendamento.php?id=" . $cliente['id_cliente']);
    exit;
}

// Se o form foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $data = $_POST['data_nascimento'];

    $id = cadastrarCliente($nome, $celular, $data);
    header("Location: agendamento.php?id=" . $id);
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
        <label>Nome:</label><br>
        <input type="text" name="nome" required><br><br>
        <label>Data de Nascimento:</label><br>
        <input type="date" name="data_nascimento" required><br><br>
        <button type="submit">Cadastrar</button>
    </form>
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #fff 100%);
        font-family: 'Segoe UI', Arial, sans-serif;
        color: #fff;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-top: 40px;
        }
        form {
            background: #fff;
            max-width: 400px;
            margin: 40px auto;
            padding: 30px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        label {
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="date"] {
            width: 100%;
            padding: 8px 10px;
            margin-top: 5px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button[type="submit"] {
            background: #2d89ef;
            color: #fff;
            border: none;
            padding: 10px 0;
            width: 100%;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: #1b5fa7;
        }
    </style>
</body>
</html>
