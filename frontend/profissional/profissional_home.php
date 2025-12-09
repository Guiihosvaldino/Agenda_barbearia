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
    margin: 0;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    font-family: 'Segoe UI', Arial;
    color: white;
}

.menu {
    background: #0d2a52;
    padding: 14px 25px;
    display: flex;
    gap: 25px;
    position: sticky;
    top: 0;
}
.menu a {
    color: #fff;
    font-weight: bold;
    text-decoration: none;
    padding: 6px 10px;
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
</style>

</head>
<body>

<div class="menu">
    <a href="profissional_home.php">🏠 Home</a>
    <a href="profissional_agenda.php">📅 Minha Agenda</a>
    <a href="profissional_estatistica.php">📊 Estatísticas</a>
    <a href="profissional_perfil.php">👤 Meu Perfil</a>
    <a href="profissional_login.php">🚪 Sair</a>
</div>


<div class="container">
    <h1>Bem-vindo, <?= $nome ?> 👋</h1>

    <a class="option-btn" href="profissional_agenda.php">📅 Ver minha agenda</a>
    <a class="option-btn" href="profissional_perfil.php">👤 Editar meu perfil</a>
    <a class="option-btn"href="profissional_estatistica.php">📊 Estatísticas</a>
    <a class="option-btn" href="profissional_login.php">🚪 Sair</a>
</div>

</body>
</html>
