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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Login - Barbearia</title>

    <style>
        body {
            background-image: url('imagem/FUNDO.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #fff;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            background: #fff;
            color: #333;
            width: 400px;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
            text-align: center;
            box-sizing: border-box;
        }

        h1 {
            margin: 0 0 10px;
            color: #660a18;
            font-size: 1.5em;
        }

        h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
            color: #8a1224;
            font-size: 1.2em;
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
            text-align: left;
            font-weight: bold;
            color: #444;
        }

        input {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #aaa;
            font-size: 1em;
            box-sizing: border-box;
            width: 100%;
        }
        
        input:focus {
            outline: none;
            border-color: #8a1224;
            box-shadow: 0 0 5px rgba(138, 18, 36, 0.35);
        }

        button {
            width: 100%;
            padding: 12px 0;
            background: linear-gradient(135deg, #660a18, #8a1224);
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            font-size: 1.05em;
            cursor: pointer;
            transition: 0.2s;
            margin-bottom: 12px;
        }

        button:hover {
            transform: scale(1.03);
            background: linear-gradient(135deg, #8a1224, #660a18);
        }

        .novo-btn {
            display: block;
            text-align: center;
            background: #660a18;
            padding: 10px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s;
        }

        .novo-btn:hover {
            background: #480610;
            transform: scale(1.02);
        }
        .link-recuperar {
            margin-top: -10px;
            text-align: right;
            font-size: 0.9em;
            color: #660a18;
            text-decoration: none;
        }

        .link-recuperar:hover {
            text-decoration: underline;
        }
        .logo {
            width: 300px; 
            margin-bottom: -40px;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
        }
        a { 
        text-decoration: none; 
        color: #660a18; 
        font-weight: bold; 
        text-align: center; 
        display: block;
        margin-top: 10px; 
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
</head>

<body>

<div class="container">

    <img src="imagem/logo1.png" class="logo">
    
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
        <a href="recuperar_senha.php" class="link-recuperar">Esqueci minha senha</a>

    </form>

</div>

</body>
</html>
