<?php

require_once('../../backend/conexao.php');
session_start();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($senha, $admin['senha'])) {
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['admin_nome'] = $admin['nome'];
        header("Location: agenda.php");
        exit;
    } else {
        $msg = "❌ Email ou senha incorretos!";
    }
    
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Login Admin 💈</title>
<style>
    body {
    margin: 0;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #fff;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    }
    .container {
    background: #fff;
            color: #333;
            width: 550px;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
            text-align: center;
            
    
    }
        .logo {
    width: 340px; 
    margin-bottom: -70px;
        }
    h2 { 
    text-align: center; 
    color: #1e3c72; 
    }
            input {
            padding: 12px;
            border-radius: 8px;
            border: 2px solid #aaa;
            font-size: 1.3em;
        }
        label {
            text-align: left;
            font-weight: bold;
            color: #444;
        }
    button {
        padding: 12px;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1.5em;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
    }
    button:hover { 
        transform: scale(1.03); 
    }
    a { 
        text-decoration: none; 
        color: #1e3c72; 
        font-weight: bold; 
        text-align: center; 
        display: block;
        margin-top: 10px; 
    }
    .msg-erro {
        background: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-left: 5px solid #dc3545;
        border-radius: 6px;
        text-align: center;
            font-weight: bold;
    }
    form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
        }
</style>
</head>
<body>
<div class="container">

 <img src="../../frontend/imagem/logo1.png" class="logo">
    <h2>💈 Login Administrador 💈</h2>

    <?php if($msg): ?><div class="msg-erro"><?= $msg ?></div><?php endif; ?>

    <form method="POST">
        <label>E-mail:</label>
        <input type="email" name="email" required>

        <label>Senha:</label>
        <input type="password" name="senha" required>

        <button type="submit">Entrar</button>
    </form>


    <a href="esqueci_senha_admin.php">Esqueci minha senha</a>
    <a href="cadastro_admin.php">Criar nova conta</a>

</div>
</body>
</html>
