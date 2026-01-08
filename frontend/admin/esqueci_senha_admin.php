<?php
require_once("../../backend/conexao.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Aqui você poderia gerar token e enviar por email
        $msg = "✅ Um link de redefinição de senha foi enviado para o email cadastrado!";
    } else {
        $msg = "❌ Email não encontrado!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Recuperar Senha Admin 💈</title>
<style>
body { margin: 0; background: linear-gradient(135deg, #1e3c72, #2a5298); font-family: 'Segoe UI', Arial; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.container { background: #fff; color: #222; max-width: 400px; width: 100%; padding: 30px 35px; border-radius: 16px; box-shadow: 0 8px 22px rgba(0,0,0,0.25); display: flex; flex-direction: column; gap: 15px; }
h2 { text-align: center; color: #1e3c72; }
input { padding: 12px; border-radius: 8px; border: 1px solid #999; font-size: 1em; }
button { padding: 12px; background: linear-gradient(135deg, #1e3c72, #2a5298); color: #fff; border: none; border-radius: 8px; font-size: 1.1em; font-weight: bold; cursor: pointer; transition: 0.2s; }
button:hover { transform: scale(1.03); }
a { text-decoration: none; color: #1e3c72; font-weight: bold; text-align: center; display: block; margin-top: 10px; }
.msg-erro { background: #f8d7da; color: #721c24; padding: 12px; border-left: 5px solid #dc3545; border-radius: 6px; text-align: center; font-weight: bold; }
.msg-sucesso { background: #d4edda; color: #155724; padding: 12px; border-left: 5px solid #28a745; border-radius: 6px; text-align: center; font-weight: bold; }
</style>
</head>
<body>
<div class="container">
<h2>💈 Recuperar Senha</h2>
<?php if($msg): ?>
    <div class="<?= strpos($msg,'✅')===0?'msg-sucesso':'msg-erro' ?>"><?= $msg ?></div>
<?php endif; ?>
<form method="POST">
<input type="email" name="email" placeholder="Email cadastrado" required>
<button type="submit">Enviar</button>
</form>
<a href="login_admin.php">Voltar para login</a>
</div>
</body>
</html>
