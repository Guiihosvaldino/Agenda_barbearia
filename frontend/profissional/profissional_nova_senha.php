<?php
require_once("../../backend/profissional.php");
$msg = "";
$token = $_GET['token'] ?? null;

if (!$token) {
    die("Token inválido!");
}

// Buscar profissional pelo token
$prof = buscarProfissionalPorToken($token);

if (!$prof) {
    die("Token inválido ou expirado!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nova_senha = $_POST['nova_senha'];
    $confirmar = $_POST['confirmar'];

    if ($nova_senha !== $confirmar) {
        $msg = "❌ A senha e a confirmação não coincidem!";
    } elseif (strlen($nova_senha) < 4) {
        $msg = "❌ A senha deve ter pelo menos 4 caracteres!";
    } else {
        if (atualizarSenhaProfissional($prof['id_profissional'], $nova_senha)) {
            // Limpar token após redefinição
            limparTokenResetSenha($prof['id_profissional']);
            $msg = "✅ Senha atualizada com sucesso! <a href='profissional_login.php'>Faça login</a>";
        } else {
            $msg = "❌ Erro ao atualizar a senha!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <style>
        body { background-image: url('../imagem/FUNDO.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; font-family:'Segoe UI',Arial,sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; color:#fff; }
        body::before { content: ''; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 0; }
        .card { position: relative; z-index: 1; background:#fff; color:#333; padding:30px; border-radius:14px; width:360px; text-align:center; }
        input { width:100%; padding:12px; border-radius:8px; border:1px solid #aaa; margin-bottom:15px; }
        button { background:#660a18; color:#fff; padding:12px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; width:100%; }
        button:hover { background:#480610; }
        .msg { margin-bottom:15px; font-weight:bold; color:red; }
        .msg a { color:#660a18; text-decoration:underline; }
    
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
    <h1>Redefinir Senha</h1>

    <?php if($msg): ?>
        <div class="msg"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="nova_senha" placeholder="Nova senha" required>
        <input type="password" name="confirmar" placeholder="Confirmar nova senha" required>
        <button type="submit">Atualizar Senha</button>
    </form>
</div>
</body>
</html>
