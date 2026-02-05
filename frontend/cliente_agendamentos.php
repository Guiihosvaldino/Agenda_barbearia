<?php



session_start();
require_once("../backend/agenda.php");

if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../index.php");
    exit;
}

$idCliente = $_SESSION['cliente_id'];

$agendamentos = listarAgendamentosFuturosCliente($idCliente);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Meus Agendamentos</title>
<style>
body {
    background: #1e3c72;
    font-family: Arial;
    color: white;
}
.container {
    background: white;
    color: #222;
    max-width: 700px;
    margin: 40px auto;
    padding: 25px;
    border-radius: 12px;
}
.card {
    background: #f1f1f1;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}
</style>
</head>
<body>

<div class="container">
<h2>📅 Próximos Horários</h2>

<?php if (empty($agendamentos)): ?>
<p>Você não possui agendamentos futuros.</p>
<?php endif; ?>

<?php foreach ($agendamentos as $a): ?>
<div class="card">
    <b>Data:</b> <?= date("d/m/Y", strtotime($a['data'])) ?><br>
    <b>Hora:</b> <?= $a['hora'] ?><br>
    <b>Serviço:</b> <?= $a['servico'] ?><br>
    <b>Profissional:</b> <?= $a['profissional'] ?><br>
    <b>Valor:</b> R$ <?= number_format($a['valor'],2,",",".") ?>
</div>
<?php endforeach; ?>

</div>

</body>
</html>
