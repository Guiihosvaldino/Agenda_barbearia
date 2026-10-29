<?php
require_once("../../backend/conexao.php");
session_start();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar = $_POST['confirmar'];

    if ($senha !== $confirmar) {
        $msg = "❌ As senhas não coincidem!";
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO admin (nome, email, senha) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$nome, $email, $hash]);
            header("Location: login_admin.php");
            exit;
        } catch (PDOException $e) {
            $msg = "❌ Este email já está cadastrado!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Cadastro Admin 💈</title>
<style>
    body {
        margin: 0; 
        background-image: url('../imagem/FUNDO.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        font-family: 'Segoe UI', Arial; 
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
        color: #222; 
        max-width: 500px; 
        width: 100%; 
        padding: 30px 35px; 
        border-radius: 16px; 
        box-shadow: 0 9px 30px rgba(0,0,0,0.25); 
        display: flex; flex-direction: column; 
        gap:30px; 
    }
    .logo {
    width: 500px;   /* quase o tamanho do formulário */
    margin-bottom: -100px;
        }
    h2 { 
        text-align: center; 
        color: #660a18; 
    }
    input { 
        padding: 12px; 
        border-radius: 8px; 
        border: 1px solid #999; 
        font-size: 1.4em; 
    }
    button { 
        padding: 12px; 
        background: linear-gradient(135deg, #660a18, #8a1224); 
        color: #fff; 
        border: none; 
        border-radius: 8px; 
        font-size: 1.1em; 
        font-weight: bold; 
        cursor: pointer; t
        ransition: 0.2s; 
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
    <h2>💈 Cadastro Para Administrador 💈</h2>

<?php if($msg): ?><div class="msg-erro"><?= $msg ?></div><?php endif; ?>

    <form method="POST">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="password" name="confirmar" placeholder="Confirmar Senha" required>

        <button type="submit">Cadastrar</button>
    
    </form>


        <a href="login_admin.php">Voltar para login</a>
    </div>
</body>
</html>
