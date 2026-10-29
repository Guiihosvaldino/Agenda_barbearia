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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Login Profissional</title>
    

    <style>
        body {
            background-image: url('../imagem/FUNDO.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 0;
        }

        .card {
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


        h2 { 
            text-align: center; 
            color: #660a18; 
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
            background: #eef4ff;
        }

        button {
            background: #660a18;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 1.05em;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
            width: 100%;
        }
        .logo {
            width: 220px; 
            margin: 0 auto 20px auto;
            display: block;
        }
        button:hover {
            background: #480610;
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
            color: #660a18;
            font-size: 0.9em;
        }

        .voltar:hover {
            text-decoration: underline;
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
