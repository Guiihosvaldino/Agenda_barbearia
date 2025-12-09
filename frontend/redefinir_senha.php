<?php
require_once("../backend/cliente.php");

$id = $_GET['id'] ?? null;
$msg = "";

if (!$id) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senha = $_POST['senha'];
    $confirmar = $_POST['confirmar'];

    if ($senha !== $confirmar) {
        $msg = "❌ As senhas não coincidem!";
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        global $pdo;
        $stmt = $pdo->prepare("UPDATE cliente SET senha = ? WHERE id_cliente = ?");
        $stmt->execute([$hash, $id]);

        header("Location: index.php?recuperado=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
</head>
<body>

<h2>Redefinir Senha</h2>

<form method="POST">

    <?php if ($msg): ?>
        <p class="erro"><?= $msg ?></p>
    <?php endif; ?>

    <label>Nova Senha:</label>
    <input type="password" name="senha" required>

    <label>Confirmar Senha:</label>
    <input type="password" name="confirmar" required>

    <button type="submit">Salvar Nova Senha</button>

</form>

<style>
body {
    background: linear-gradient(135deg, #1e3c72 0%, #fff 100%);
    font-family: Arial;
}

form {
    background: #fff;
    max-width: 350px;
    margin: 40px auto;
    padding: 28px;
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input, button {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #222;
    font-size: 1em;
}

button {
    background: #1e3c72;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}

.erro {
    text-align: center;
    color: red;
    font-weight: bold;
}
</style>

</body>
</html>
