<?php
require_once("../backend/cliente.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $celular = $_POST['celular'];

    $cliente = buscarClientePorCelular($celular);

    if ($cliente) {

        $token = rand(100000, 999999);
        $expira = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        salvarTokenCliente($cliente['id_cliente'], $token, $expira);

        // ⚠️ TESTE ACADÊMICO
        // echo "Token: $token";

        /*
        // ====================================================================================
        // FUNÇÃO DE ENVIO DE WHATSAPP (DESCOMENTE QUANDO O SISTEMA ESTIVER HOSPEDADO)
        // Substitua a URL e os dados de autenticação pela API que você for utilizar (Ex: Z-API, Evolution, Twilio)
        // ====================================================================================
        $mensagem = "💈 Olá! Seu token para recuperar a senha na Barbearia é: *" . $token . "*";
        $url_api = "https://sua-api-whatsapp.com/v1/messages"; // Coloque a URL da sua API aqui
        
        $dados = [
            "number" => $celular, // O número do cliente digitado no formulário
            "message" => $mensagem
        ];
        
        $ch = curl_init($url_api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer SEU_TOKEN_DE_API_AQUI' // Seu token de acesso da API
        ]);
        
        $resultado = curl_exec($ch);
        curl_close($ch);
        // ====================================================================================
        */

        header("Location: redefinir_senha.php?id=" . $cliente['id_cliente']);
        exit;

    } else {
        $msg = "❌ Celular não encontrado!";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Recuperar Senha 💈</title>

    <style>

        body {
            margin: 0;
            background-image: url('imagem/FUNDO.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
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
            max-width: 450px;
            margin: 60px auto;
            padding: 30px 35px;
            border-radius: 16px;
            color: #222;
            box-shadow: 0 8px 22px rgba(0,0,0,0.25);
        }

        h2 {
            text-align: center;
            color: #660a18;
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
            background: linear-gradient(135deg, #660a18, #8a1224);
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
                    .logo {
        width: 350px;  
        margin-bottom: -30px;
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
    <h2>💈 Recuperar Senha 💈</h2>

    <form method="POST">
        <?php if ($msg): ?>
            <div class="msg-erro"><?= $msg ?></div>
        <?php endif; ?>

        <input type="text" name="celular" placeholder="Digite seu celular" required>
        <button type="submit">Continuar</button>
    </form>
</div>

</body>
</html>
