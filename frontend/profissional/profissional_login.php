<?php
require_once("../../backend/profissional.php");
session_start();

$erro = $_GET['erro'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $prof = buscarProfissionalPorEmail($email);

    if ($prof && password_verify($senha, $prof['senha'])) {

        $_SESSION['profissional_id'] = $prof['id_profissional'];
        $_SESSION['profissional_nome'] = $prof['nome'];

        header("Location: profissional_home.php");
        exit;

    } else {
        header("Location: profissional_login.php?erro=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Profissional</title>
    

    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        .card {
            background: #fff;
            color: #333;
            width: 550px;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
            text-align: center;
            
        }


        h2 { 
            text-align: center; 
            color: #1e3c72; 
        }
        

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
        }

        label {
            text-align: left;
            font-weight: bold;
            color: #444;
        }

        input {
            padding: 12px;
            border-radius: 8px;
            border: 2px solid #aaa;
            font-size: 1.3em;
        }

        input:focus {
            outline: none;
            border-color: #2a5298;
            background: #eef4ff;
        }

        button {
            background: #1e3c72;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }
        .logo {
    width: 350px; 
    margin-bottom: -70px;
        }
        button:hover {
            background: #16315c;
        }

        .erro {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-left: 5px solid #dc3545;
            border-radius: 6px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .voltar {
            display: block;
            margin-top: 12px;
            text-decoration: none;
            font-weight: bold;
            color: #1e3c72;
            font-size: 0.9em;
        }

        .voltar:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="card">
    <img src="../../frontend/imagem/logo1.png" class="logo">

    <h2> 💈 Área do Profissional 💈</h2>


    <?php if ($erro): ?>
        <div class="erro">❌ E-mail ou senha incorretos!</div>
    <?php endif; ?>

    <form method="POST">
        <label>E-mail:</label>
        <input type="email" name="email" required>

        <label>Senha:</label>
        <input type="password" name="senha" required>

        <button type="submit">Entrar</button>
    </form>

    
    <a href="profissional_esqueci_senha.php" class="voltar">❓ Esqueci minha senha</a>

    
</div>


</body>
</html>
