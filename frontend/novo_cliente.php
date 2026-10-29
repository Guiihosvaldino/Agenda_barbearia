<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Novo Cliente - Barbearia</title>

    <style>
        body {
            background-image: url('imagem/FUNDO.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            background: #ffffff;
            width: 550px;
            padding: 30px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.25);
        }

        h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 25px;
            color: #660a18;
            font-size: 1.7em;
            font-weight: 700;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
            color: #222;
        }

        input {
            width: 100%;
            padding: 11px 12px;
            border-radius: 8px;
            border: 1px solid #333;
            font-size: 1em;
            margin-bottom: 18px;
            outline: none;
            transition: 0.2s;
        }

        input:focus {
            border-color: #660a18;
            background: #f2f6ff;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #660a18, #8a1224);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: transform 0.2s, background 0.2s;
        }

        button:hover {
            transform: scale(1.03);
            background: linear-gradient(135deg, #8a1224, #660a18);
        }

        .voltar {
            display: block;
            text-align: center;
            background: #660a18;
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            margin-top: 18px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s;
        }

        .voltar:hover {
            background: #480610;
            transform: scale(1.02);
        }
            .logo {
        width: 350px;  
        margin-bottom: -50px;
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

<div class="container">
    <img src="../frontend/imagem/logo1.png" class="logo">
    <h2>Cadastro Inicial</h2>

    <?php if (isset($_GET['erro']) && $_GET['erro'] === 'existente'): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb; margin-bottom: 20px; text-align: center; font-weight: bold;">
            ⚠️ Este número já está cadastrado!
        </div>
    <?php endif; ?>

    <form method="POST" action="../backend/cadastro.php">
        <label>Digite seu celular:</label>
        <input 
            type="tel" 
            name="celular" 
            value="<?php echo $_GET['celular'] ?? ''; ?>" 
            maxlength="11" 
            placeholder="Ex: 34999999999" 
            required
        >

        <button type="submit">Continuar</button>
    </form>

    <a href="index.php" class="voltar">⬅ Voltar ao Login</a>
</div>

</body>
</html>
