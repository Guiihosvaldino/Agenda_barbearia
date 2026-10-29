<?php
require_once("../backend/cliente.php");
session_start();

$celular = $_GET['celular'] ?? null;
if (!$celular) {
    header("Location: index.php");
    exit;
}

$cliente = buscarClientePorCelular($celular);
if ($cliente) {
    $_SESSION['cliente_id'] = $cliente['id_cliente'];
    header("Location: agendamento.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $data = $_POST['data_nascimento'];
    $senha = $_POST['senha'];

    $id = cadastrarCliente($nome, $celular, $senha, $data);

    $_SESSION['cliente_id'] = $id;
    header("Location: agendamento.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Cadastro - Barbearia 💈</title>

<style>
/* ——— IDENTIDADE VISUAL UNIFICADA ——— */
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
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #fff;
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
    background: #ffffff;
    max-width: 550px;
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

<div class="menu">
    <a href="index.php">⟵ Voltar</a>
</div>

<div class="container">
        <img src="../frontend/imagem/logo1.png" class="logo">
    <h2>💈 Cadastro de Cliente 💈</h2>

    <form method="POST">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="date" name="data_nascimento" placeholder="Data de Nascimento" required>
        <input type="password" name="senha" placeholder="Senha" required>

        <button type="submit">Cadastrar</button>
    </form>
</div>

</body>
</html>
