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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Login Admin 💈</title>
<style>
    body {
    margin: 0;
    background-image: url('../imagem/FUNDO.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #fff;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    }
    body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
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
    .logo {
            width: 220px; 
            margin: 0 auto 20px auto;
            display: block;
    }
    h2 { 
    text-align: center; 
    color: #660a18; 
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
        label {
            text-align: left;
            font-weight: bold;
            color: #444;
        }
    button {
        padding: 12px;
        background: linear-gradient(135deg, #660a18, #8a1224);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 1.05em;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
        width: 100%;
    }
    button:hover { 
        transform: scale(1.03); 
    }
    a { 
        text-decoration: none; 
        color: #660a18; 
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
