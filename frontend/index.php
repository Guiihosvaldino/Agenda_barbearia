<?php
require_once("../backend/cliente.php");
session_start();

$erro = $_GET['erro'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $celular = $_POST['celular'];
    $senha   = $_POST['senha'];

    $cliente = buscarClientePorCelular($celular);

    if ($cliente && password_verify($senha, $cliente['senha'])) {
        $_SESSION['cliente_id'] = $cliente['id_cliente'];
        header("Location: agendamento.php");
        exit;
    } else {
        header("Location: index.php?erro=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Barbearia</title>

    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #fff;
        }

        .container {
        
            background: #fff;
            color: #333;
            width: 360px;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
            text-align: center;
        }

        h1 {
            margin: 0 0 10px;
            color: #1e3c72;
            font-size: 1.8em;
        }

        h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 25px;
            color: #2a5298;
            font-size: 1.3em;
        }

        .erro {
            background: #ffdddd;
            color: #c20000;
            border-left: 4px solid #c20000;
            padding: 10px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }

        label {
            font-size: 1em;
            margin-bottom: 5px;
            display: block;
            font-weight: bold;
            color: #222;
        }

        input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #333;
            margin-bottom: 18px;
            font-size: 1em;
        }

        button {
            width: 100%;
            padding: 12px 0;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: 0.2s;
            margin-bottom: 12px;
        }

        button:hover {
            transform: scale(1.03);
            background: linear-gradient(135deg, #2a5298, #1e3c72);
        }

        .novo-btn {
            display: block;
            text-align: center;
            background: #1e3c72;
            padding: 10px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s;
        }

        .novo-btn:hover {
            background: #16315c;
            transform: scale(1.02);
        }
        .link-recuperar {
            margin-top: -10px;
            text-align: right;
            font-size: 0.9em;
            color: #1e3c72;
            text-decoration: none;
        }

        .link-recuperar:hover {
            text-decoration: underline;
        }


    </style>
</head>

<body>

<div class="container">

    <h1>💈Meus Agendamento💈</h1>
    <h2>Agende Seu Horário</h2>

    <form method="POST">

        <?php if ($erro): ?>
            <p class="erro">❌ Celular ou senha incorretos!</p>
        <?php endif; ?>

        <label>Celular:</label>
        <input type="text" name="celular" placeholder="Digite seu celular" required>

        <label>Senha:</label>
        <input type="password" name="senha" placeholder="Digite sua senha" required>

        <button type="submit">Entrar</button>

        <a href="novo_cliente.php" class="novo-btn">Sou novo cliente</a>
        <a href="recuperar_senha.php" class="link-recuperar">esqueci minha senha</a>

    </form>

</div>

</body>
</html>
