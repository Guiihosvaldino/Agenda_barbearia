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
    <title>Redefinir Senha 💈</title>

    <style>
        /* ——— IDENTIDADE VISUAL UNIFICADA ——— */
        body {
            margin: 0;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            color: #fff;
            min-height: 100vh;
        }

        .menu {
            background: #0d2a52;
            padding: 14px 25px;
            display: flex;
            gap: 25px;
            font-size: 1.1em;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .menu a {
            color: #fff;
            text-decoration: none;
            padding: 6px 12px;
            font-weight: bold;
            border-radius: 6px;
        }

        .menu a:hover {
            background: #1e3c72;
        }

        .container {
            background: #ffffff;
            max-width: 450px;
            margin: 60px auto;
            padding: 30px 35px;
            border-radius: 16px;
            color: #222;
            box-shadow: 0 8px 22px rgba(0,0,0,0.25);
        }

        h2 {
            text-align: center;
            color: #1e3c72;
            margin-bottom: 30px;
            font-size: 2em;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        input {
            padding: 12px;
            font-size: 1em;
            border-radius: 8px;
            border: 1px solid #999;
        }

        button {
            padding: 12px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }

        button:hover {
            transform: scale(1.03);
        }

        .msg-erro {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-left: 5px solid #dc3545;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="menu">
    <a href="../index.php">⟵ Voltar</a>
</div>

<div class="container">
    <h2>💈 Redefinir Senha</h2>

    <form method="POST">
        <?php if ($msg): ?>
            <div class="msg-erro"><?= $msg ?></div>
        <?php endif; ?>

        <input type="password" name="senha" placeholder="Nova Senha" required>
        <input type="password" name="confirmar" placeholder="Confirmar Senha" required>

        <button type="submit">Salvar Nova Senha</button>
    </form>
</div>

</body>
</html>
