<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Confirmação - Barbearia 💈</title>

    <style>
    /* ——— IDENTIDADE VISUAL UNIFICADA ——— */
    body {
        margin: 0;
        background-image: url('imagem/FUNDO.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        font-family: 'Segoe UI', Arial, sans-serif;
        color: #fff;
        min-height: 100vh;
    }
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 0;
    }

    .menu {
        background: #380309;
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
        background: #660a18;
    }

    .container {
        position: relative;
        z-index: 1;
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
        color: #660a18;
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
        background: linear-gradient(135deg, #660a18, #8a1224);
        padding: 12px 28px;
        border-radius: 8px;
        font-weight: bold;
        transition: 0.2s;
    }

    a.btn:hover {
        transform: scale(1.03);
    }
        .logo {
        width: 350px;   /* quase o tamanho do formulário */
        margin-bottom: -100px;
        margin-left: auto;
        margin-right: auto;
        display: block;
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

<div class="menu">
    <a href="index.php">⟵ Voltar</a>
</div>


<div class="container">
    <img src="../frontend/imagem/logo1.png" class="logo">
    <h2>✅ Agendamento realizado com sucesso!</h2>
    <p>Seu horário foi registrado no sistema. Aguarde a confirmação do salão.</p>
    <a class="btn" href="index.php">Voltar para início</a>
</div>

</body>
</html>
