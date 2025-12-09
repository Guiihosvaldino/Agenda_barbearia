<?php
session_start();
require_once("../../backend/profissional.php");

if (!isset($_SESSION['profissional_id'])) {
    header("Location: profissional_login.php?erro=login");
    exit;
}

$idProf = $_SESSION['profissional_id'];
$nome = $_SESSION['profissional_nome'];

$ano = $_GET['ano'] ?? date("Y");
$mes = $_GET['mes'] ?? date("m");

$totalAtend = totalAtendimentosMes($idProf, $ano, $mes);
$tempoTotal = tempoTotalTrabalhado($idProf, $ano, $mes);
$totalFinanceiro = totalFinanceiroMes($idProf, $ano, $mes);
$resumoServicos = resumoPorServico($idProf, $ano, $mes);

// converter tempo total
$horas = floor($tempoTotal / 60);
$minutos = $tempoTotal % 60;

// preparar dados para os gráficos
$servicosNomes = [];
$servicosQtd = [];
$servicosTotais = [];

foreach ($resumoServicos as $item) {
    $servicosNomes[] = $item['servico'];
    $servicosQtd[] = $item['qtd'];
    $servicosTotais[] = $item['total'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Estatísticas do Profissional</title>

    <!-- CHART JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    margin: 0;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    font-family: 'Segoe UI';
    color: white;
}

.menu {
    background: #0d2a52;
    padding: 14px 25px;
    display: flex;
    gap: 25px;
}

.menu a {
    color: white;
    text-decoration: none;
    font-weight: bold;
}

.container {
    background: white;
    max-width: 900px;
    padding: 30px;
    margin: 40px auto;
    color: #222;
    border-radius: 12px;
}

h1 {
    color: #1e3c72;
    text-align: center;
}

.box {
    background: #eef3ff;
    border-left: 6px solid #1e3c72;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
}

canvas {
    margin-top: 20px;
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

<h1>📊 Estatísticas de <?= $mes ?>/<?= $ano ?></h1>

<!-- FILTRO DE MÊS/ANO -->
<form method="GET">
    <label>Mês:</label>
    <input type="number" name="mes" min="1" max="12" value="<?= $mes ?>">
    <label>Ano:</label>
    <input type="number" name="ano" value="<?= $ano ?>">
    <button type="submit">Filtrar</button>
</form>

<br>

<div class="box">
    <h3>Total de Atendimentos:</h3>
    <strong><?= $totalAtend ?> atendimentos</strong>
</div>

<div class="box">
    <h3>Tempo Total Trabalhado:</h3>
    <strong><?= $horas ?>h <?= $minutos ?>min</strong>
</div>

<div class="box">
    <h3>Faturamento Total:</h3>
    <strong>R$ <?= number_format($totalFinanceiro, 2, ',', '.') ?></strong>
</div>

<hr><br>

<h2 style="text-align:center;">Gráficos</h2>

<!-- Gráfico de Pizza -->
<canvas id="graficoPizza" height="120"></canvas>

<!-- Gráfico de Barras -->
<canvas id="graficoBarras" height="120"></canvas>

<script>
// Dados enviados pelo PHP
const servicosNomes = <?= json_encode($servicosNomes) ?>;
const servicosQtd = <?= json_encode($servicosQtd) ?>;
const servicosTotais = <?= json_encode($servicosTotais) ?>;

// 🎨 cores automáticas
const cores = [
    "#1e3c72","#2a5298","#3c6db5","#547ec0",
    "#7699d4","#9bb6e8","#c3d3f9","#d8e3ff"
];

// === PIE CHART ===
new Chart(document.getElementById('graficoPizza'), {
    type: 'pie',
    data: {
        labels: servicosNomes,
        datasets: [{
            data: servicosTotais,
            backgroundColor: cores
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: "Faturamento por Serviço"
            }
        }
    }
});

// === BAR CHART ===
new Chart(document.getElementById('graficoBarras'), {
    type: 'bar',
    data: {
        labels: servicosNomes,
        datasets: [{
            label: 'Quantidade realizada',
            data: servicosQtd,
            backgroundColor: "#1e3c72"
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: "Quantidade de Atendimentos por Serviço"
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</div>

</body>
</html>
