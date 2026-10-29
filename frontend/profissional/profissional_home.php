<?php
session_start();

if (!isset($_SESSION['profissional_id'])) {
    header("Location: profissional_login.php?erro=login");
    exit;
}

$nome = $_SESSION['profissional_nome'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Área do Profissional</title>

<style>
    body {
        background-image: url('../imagem/FUNDO.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 0;
        color: #fff;
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
    background: white;
    max-width: 600px;
    margin: 40px auto;
    padding: 25px;
    color: #222;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

h1 {
    color: #660a18;
}
.option-btn {
    display: block;
    background: #660a18;
    color: white;
    padding: 14px;
    margin: 10px 0;
    font-size: 1.1em;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
}
.option-btn:hover {
    background: #8a1224;
}
    .logo {
        width: 350px; 
        margin-bottom: -70px;
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

    <link rel="stylesheet" href="../css/menu-lateral.css">
</head>
<body>

<?php require __DIR__ . '/menu_lateral.php'; ?>


<div class="container">
        <img src="../../frontend/imagem/logo1.png" class="logo">
    <h1>Bem-vindo, <?= $nome ?> </h1>
    <a class="option-btn" href="profissional_agenda.php">📅 Ver minha agenda</a>
    <a class="option-btn" href="profissional_perfil.php">👤 Editar meu perfil</a>
    <a class="option-btn"href="profissional_estatistica.php">📊 Estatísticas</a>
    <a class="option-btn" href="profissional_login.php">🚪 Sair</a>
</div>

</body>
</html>
