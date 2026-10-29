<?php
require_once("../../backend/profissional.php");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $prof = buscarProfissionalPorEmail($email);

    if ($prof) {
        // Gerar token único para redefinir senha
        $token = bin2hex(random_bytes(20));

        // Salvar token no banco 
        salvarTokenResetSenha($prof['id_profissional'], $token);

        // Link para redefinir senha
        $link = "http://seusite.com/profissional_nova_senha.php?token=$token";

        // Enviar e-mail
        $assunto = "Redefinir senha";
        $mensagem = "Olá {$prof['nome']},\n\nClique no link abaixo para redefinir sua senha:\n$link\n\nSe você não solicitou, ignore este e-mail.";
        $headers = "From: no-reply@seusite.com";

        if (mail($email, $assunto, $mensagem, $headers)) {
            $msg = "✅ E-mail enviado! Verifique sua caixa de entrada.";
        } else {
            $msg = "❌ Erro ao enviar o e-mail.";
        }
    } else {
        $msg = "❌ E-mail não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Esqueci minha senha</title>
    <style>
        body { background-image: url('../imagem/FUNDO.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; font-family: 'Segoe UI', Arial; display:flex; justify-content:center; align-items:center; height:100vh; color:#fff; margin:0; }
        body::before { content: ''; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 0; }
        .card { position: relative; z-index: 1; background:#fff; color:#333; padding:30px; border-radius:14px; width:360px; text-align:center; }
        input { width:100%; padding:12px; border-radius:8px; border:1px solid #aaa; margin-bottom:15px; }
        button { background:#660a18; color:#fff; padding:12px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; }
        button:hover { background:#480610; }
        .msg { margin-bottom:15px; font-weight:bold; }
    
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
        <input type="email" name="email" placeholder="Digite seu e-mail" required>
        <button type="submit">Enviar link de redefinição</button>
    </form>

    <a href="profissional_login.php" class="voltar">⟵ Voltar para login</a>
</div>
</body>
</html>
