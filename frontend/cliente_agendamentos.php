<?php



session_start();
require_once("../backend/agenda.php");

if (!isset($_SESSION['cliente_id'])) {
    header("Location: index.php?erro=login");
    exit;
}

$idCliente = $_SESSION['cliente_id'];

$agendamentos = listarAgendamentosFuturosCliente($idCliente);
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Meus Agendamentos</title>
<style>
body {
    background-image: url('imagem/FUNDO.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    font-family: Arial;
    color: white;
    margin: 0;
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
    background: white;
    color: #222;
    max-width: 800px;
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
