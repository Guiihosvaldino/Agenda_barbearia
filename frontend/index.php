<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $celular = $_POST['celular'];
    header("Location: cadastro.php?celular=" . urlencode($celular));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Barbearia</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h2>Agende Seu Horario </h2>
    <form method="POST">
        <label>Digite seu celular:</label><br>
        <input type="text" name="celular" required><br><br>
        <button type="submit">Entrar</button>
    </form>
    <style>
    body {
        background: linear-gradient(135deg, #1e3c72 0%, #fff 100%);
        font-family: 'Segoe UI', Arial, sans-serif;
        color: #ffffff;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }

    h2 {
        text-align: center;
        margin-top: 40px;
        font-size: 2.2em;
        letter-spacing: 1px;
        font-weight: 600;
        color: #000000;
    }

    form {
        background: #ffffff;
        max-width: 350px;
        margin: 40px auto;
        padding: 32px 28px 24px 28px;
        border-radius: 16px;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.18);
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    label {
        font-size: 1.1em;
        margin-bottom: 8px;
        color: #000000;
    }

    /* Alteração para o campo de celular */
    input[type="text"] {
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #000000; /* Borda preta adicionada */
        outline: none;
        font-size: 1em;
        background: #ffffff; /* Fundo branco */
        color: #222; /* Cor do texto escura para contraste */
        transition: background 0.2s;
    }

    /* Efeito de foco para quando o usuário clicar no campo */
    input[type="text"]:focus {
        background: #f0f0f0; /* Fundo branco levemente acinzentado */
    }

    button[type="submit"] {
        background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 12px 0;
        font-size: 1.1em;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(30,60,114,0.12);
        transition: background 0.2s, transform 0.2s;
    }

    button[type="submit"]:hover {
        background: linear-gradient(90deg, #2a5298 0%, #1e3c72 100%);
        transform: translateY(-2px) scale(1.03);
    }
    </style>
</body>
</html>