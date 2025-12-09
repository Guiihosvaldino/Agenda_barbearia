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
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <style>
        body { background: linear-gradient(135deg,#1e3c72,#2a5298); font-family:'Segoe UI',Arial,sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; color:#fff; }
        .card { background:#fff; color:#333; padding:30px; border-radius:14px; width:360px; text-align:center; }
        input { width:100%; padding:12px; border-radius:8px; border:1px solid #aaa; margin-bottom:15px; }
        button { background:#1e3c72; color:#fff; padding:12px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; width:100%; }
        button:hover { background:#16315c; }
        .msg { margin-bottom:15px; font-weight:bold; color:red; }
        .msg a { color:#1e3c72; text-decoration:underline; }
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
