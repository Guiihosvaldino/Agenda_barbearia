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
    <meta charset="UTF-8">
    <title>Área do Profissional</title>

<style>
    body {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 0;
        color: #fff;
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
    color: #1e3c72;
}
.option-btn {
    display: block;
    background: #1e3c72;
    color: white;
    padding: 14px;
    margin: 10px 0;
    font-size: 1.1em;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
}
.option-btn:hover {
    background: #2a5298;
}
    .logo {
        width: 350px; 
        margin-bottom: -100px;
        margin-left: auto;
        margin-right: auto;
        display: block;
        }
</style>

</head>
<body>

<div class="menu">
     <a href="profissional_home.php">🏠 Home</a>
    <a href="profissional_agenda.php">📅 Minha Agenda</a>
    <a href="profissional_perfil.php">👤 Meu Perfil</a>
    <a href="profissional_estatistica.php">📊 Estatísticas</a>
    <a href="profissional_agendar.php">➕ Novo Agendamento</a>
    <a href="profissional_login.php">🚪 Sair</a>
</div>


<div class="container">
        <img src="../../frontend/imagem/logo1.png" class="logo">
    <h1>Bem-vindo, <?= $nome ?> </h1>
    <a class="option-btn" href="profissional_agendar.php">➕ Novo Agendamento</a>
    <a class="option-btn" href="profissional_agenda.php">📅 Ver minha agenda</a>
    <a class="option-btn" href="profissional_perfil.php">👤 Editar meu perfil</a>
    <a class="option-btn"href="profissional_estatistica.php">📊 Estatísticas</a>
    <a class="option-btn" href="profissional_login.php">🚪 Sair</a>
</div>

</body>
</html>
