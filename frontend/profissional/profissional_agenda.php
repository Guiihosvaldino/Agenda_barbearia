<?php

session_start();

if (!isset($_SESSION['profissional_id'])) {
    header("Location: ../admin/profissional_login.php?erro=login");
    exit;
}

$nome = $_SESSION['profissional_nome'];
$id_profissional = $_SESSION['profissional_id'];

require_once("../../backend/profissional.php");

// Buscar agenda do profissional
$agenda = listarAgendaProfissional($id_profissional);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Agenda</title>

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
    max-width: 900px;
    margin: 40px auto;
    padding: 25px;
    color: #222;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

h1 {
    color: #1e3c72;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th {
    background: #1e3c72;
    color: white;
    padding: 12px;
}

td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

tr:nth-child(even) {
    background: #f2f2f2;
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
    <h1>📅 Minha Agenda</h1>

    <?php if (count($agenda) == 0): ?>
        <p style="text-align:center; font-size:1.2em;">Nenhum agendamento encontrado.</p>
    <?php else: ?>

    <table>
        <tr>
            <th>Cliente</th>
            <th>Serviço</th>
            <th>Data</th>
            <th>Horário</th>
            <th>Status</th>
        </tr>

        <?php foreach ($agenda as $a): ?>
        <tr>
            <td><?= $a['cliente'] ?></td>
            <td><?= $a['servico'] ?></td>
            <td><?= date('d/m/Y', strtotime($a['data'])) ?></td>
            <td><?= $a['horario'] ?></td>
            <td><?= $a['status'] ?></td>
        </tr>
        <?php endforeach; ?>

    </table>

    <?php endif; ?>
</div>

</body>
</html>
