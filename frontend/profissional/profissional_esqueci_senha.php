<?php
require_once("../../backend/profissional.php");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $prof = buscarProfissionalPorEmail($email);

    if ($prof) {
        // Gerar token único para redefinir senha
        $token = bin2hex(random_bytes(20));

        // Salvar token no banco (você precisa criar campo `reset_token` na tabela `profissional`)
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
    <meta charset="UTF-8">
    <title>Esqueci minha senha</title>
    <style>
        body { background: linear-gradient(135deg, #1e3c72, #2a5298); font-family: 'Segoe UI', Arial; display:flex; justify-content:center; align-items:center; height:100vh; color:#fff; margin:0; }
        .card { background:#fff; color:#333; padding:30px; border-radius:14px; width:360px; text-align:center; }
        input { width:100%; padding:12px; border-radius:8px; border:1px solid #aaa; margin-bottom:15px; }
        button { background:#1e3c72; color:#fff; padding:12px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; }
        button:hover { background:#16315c; }
        .msg { margin-bottom:15px; font-weight:bold; }
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
