<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Confirmação - Barbearia 💈</title>

    <style>
    /* ——— IDENTIDADE VISUAL UNIFICADA ——— */
    body {
        margin: 0;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        font-family: 'Segoe UI', Arial, sans-serif;
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
        max-width: 600px;
        margin: 60px auto;
        padding: 30px 35px;
        border-radius: 16px;
        color: #222;
        box-shadow: 0 8px 22px rgba(0,0,0,0.25);
        text-align: center;
    }

    h2 {
        color: #1e3c72;
        margin-bottom: 20px;
        font-size: 2em;
    }

    p {
        color: #333;
        font-size: 1.1em;
        margin-bottom: 25px;
    }

    a.btn {
        display: inline-block;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        padding: 12px 28px;
        border-radius: 8px;
        font-weight: bold;
        transition: 0.2s;
    }

    a.btn:hover {
        transform: scale(1.03);
    }
    </style>
</head>
<body>

<div class="menu">
    <a href="index.php">⟵ Voltar</a>
</div>

<div class="container">
    <h2>✅ Agendamento realizado com sucesso!</h2>
    <p>Seu horário foi registrado no sistema. Aguarde a confirmação do salão.</p>
    <a class="btn" href="index.php">Voltar para início</a>
</div>

</body>
</html>
