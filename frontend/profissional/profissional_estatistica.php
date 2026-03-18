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

        form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        form label {
            font-weight: bold;
            color: #1e3c72;
        }

        form input {
            padding: 8px 12px;
            border: 2px solid #1e3c72;
            border-radius: 6px;
            font-size: 1em;
        }

        form input:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 5px rgba(42, 82, 152, 0.3);
        }

        form button {
            padding: 8px 20px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1em;
        }

        form button:hover {
            background: #2a5298;
        }
       .logo {
        width: 350px;   /* quase o tamanho do formulário */
        margin-bottom: -70px;
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
<h1>📊 Estatísticas de <?= $mes ?>/<?= $ano ?></h1>


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

const servicosNomes = <?= json_encode($servicosNomes) ?>;
const servicosQtd = <?= json_encode($servicosQtd) ?>;
const servicosTotais = <?= json_encode($servicosTotais) ?>;


const cores = [
    "#1e723e","#2a5298","#b53c3c","#c0b754",
    "#0c9132","#ae9be8","#c84e4e","#b3b324"
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
