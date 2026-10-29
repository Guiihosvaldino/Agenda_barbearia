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
$clientesAtendidos = clientesAtendidosMes($idProf, $ano, $mes);

// converter tempo total
$horas = floor($tempoTotal / 60);
$minutos = $tempoTotal % 60;

// preparar dados para os gráficos e cores
$cores_base = ["#1e723e","#8a1224","#b53c3c","#c0b754", "#0c9132","#ae9be8","#c84e4e","#b3b324"];
$cor_por_servico = [];

$servicosNomes = [];
$servicosQtd = [];
$servicosTotais = [];
$servicosCores = [];

foreach ($resumoServicos as $i => $item) {
    $servicosNomes[] = $item['servico'];
    $servicosQtd[] = $item['qtd'];
    $servicosTotais[] = $item['total'];
    
    $cor = $cores_base[$i % count($cores_base)];
    $cor_por_servico[$item['servico']] = $cor;
    $servicosCores[] = $cor;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Estatísticas do Profissional</title>

    <!-- CHART JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    max-width: 900px;
    padding: 30px;
    margin: 40px auto;
    color: #222;
    border-radius: 12px;
}

h1 {
    color: #660a18;
    text-align: center;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 12px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid #eef2f5;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.stat-card.destaque {
    background: linear-gradient(135deg, #660a18 0%, #8a1224 100%);
    color: white;
}

.stat-card.destaque h3, .stat-card.destaque strong {
    color: white;
}

.stat-icon {
    font-size: 2.5em;
    background: rgba(30, 60, 114, 0.08);
    width: 65px;
    height: 65px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.stat-card.destaque .stat-icon {
    background: rgba(255, 255, 255, 0.15);
}

.stat-info h3 {
    margin: 0;
    font-size: 0.9em;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.stat-info strong {
    font-size: 1.8em;
    color: #660a18;
    display: block;
    margin-top: 5px;
}

.section-title {
    color: #660a18;
    margin-top: 40px;
    margin-bottom: 20px;
    font-size: 1.5em;
    border-bottom: 2px solid #eef2f5;
    padding-bottom: 10px;
}

.charts-container {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 40px;
}

.chart-box {
    flex: 1;
    min-width: 300px;
    max-width: 400px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid #eef2f5;
}

.table-wrapper {
    overflow-x: auto;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.styled-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.styled-table thead tr {
    background-color: #660a18;
    color: #ffffff;
    text-align: left;
}

.styled-table th, .styled-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #dddddd;
}

.styled-table tbody tr:hover {
    background-color: #f8f9fa;
}

.styled-table tbody tr:last-of-type {
    border-bottom: 2px solid #660a18;
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
            color: #660a18;
        }

        form input {
            padding: 8px 12px;
            border: 2px solid #660a18;
            border-radius: 6px;
            font-size: 1em;
        }

        form input:focus {
            outline: none;
            border-color: #8a1224;
            box-shadow: 0 0 5px rgba(138, 18, 36, 0.35);
        }

        form button {
            padding: 8px 20px;
            background: #660a18;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1em;
        }

        form button:hover {
            background: #8a1224;
        }
       .logo {
        width: 350px;   /* quase o tamanho do formulário */
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
<h1>📊 Estatísticas de <?= $mes ?>/<?= $ano ?></h1>


<form method="GET">
    <label>Mês:</label>
    <input type="number" name="mes" min="1" max="12" value="<?= $mes ?>">
    <label>Ano:</label>
    <input type="number" name="ano" value="<?= $ano ?>">
    <button type="submit">Filtrar</button>
   
</form>

<br>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">✂️</div>
        <div class="stat-info">
            <h3>Atendimentos</h3>
            <strong><?= $totalAtend ?></strong>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏱️</div>
        <div class="stat-info">
            <h3>Tempo Trabalhado</h3>
            <strong><?= $horas ?>h <?= $minutos ?>m</strong>
        </div>
    </div>
    <div class="stat-card destaque">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <h3>Faturamento</h3>
            <strong>R$ <?= number_format($totalFinanceiro, 2, ',', '.') ?></strong>
        </div>
    </div>
</div>

<h2 class="section-title">📊 Gráficos</h2>

<div class="charts-container">
    <div class="chart-box">
        <!-- Gráfico de Pizza -->
        <canvas id="graficoPizza"></canvas>
    </div>
    <div class="chart-box">
        <p style="text-align: center; font-size: 0.85em; color: #666; margin-bottom: 10px;">Clique numa barra para filtrar a tabela abaixo</p>
        <!-- Gráfico de Barras -->
        <canvas id="graficoBarras"></canvas>
    </div>
</div>

<script>

const servicosNomes = <?= json_encode($servicosNomes) ?>;
const servicosQtd = <?= json_encode($servicosQtd) ?>;
const servicosTotais = <?= json_encode($servicosTotais) ?>;
const cores = <?= json_encode($servicosCores) ?>;

let filtroAtivo = null;

function aplicarFiltro(servicoNome) {
    if (filtroAtivo === servicoNome) {
        filtroAtivo = null;
    } else {
        filtroAtivo = servicoNome;
    }

    const rows = document.querySelectorAll('.client-row');
    rows.forEach(row => {
        if (!filtroAtivo) {
            row.style.display = '';
        } else {
            if (row.getAttribute('data-servico') === filtroAtivo) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

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
            backgroundColor: cores
        }]
    },
    options: {
        onClick: (e, elements, chart) => {
            if (elements.length > 0) {
                const idx = elements[0].index;
                const label = chart.data.labels[idx];
                aplicarFiltro(label);
            } else {
                aplicarFiltro(null);
            }
        },
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

<h2 class="section-title">📋 Clientes Atendidos no Mês</h2>
<div class="table-wrapper">
    <table class="styled-table">
        <thead>
            <tr>
                <th>Data / Hora</th>
                <th>Cliente</th>
                <th>Serviço</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clientesAtendidos)): ?>
                <tr><td colspan="4" style="text-align:center;">Nenhum atendimento finalizado neste mês.</td></tr>
            <?php else: ?>
                <?php foreach($clientesAtendidos as $c): 
                    $corBadge = $cor_por_servico[$c['servico']] ?? '#660a18';
                ?>
                    <tr class="client-row" data-servico="<?= htmlspecialchars($c['servico']) ?>">
                        <td><?= date('d/m/Y', strtotime($c['data'])) ?> às <?= date('H:i', strtotime($c['hora'])) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($c['cliente']) ?></strong><br>
                            <small style="color:#666;"><?= htmlspecialchars($c['celular']) ?></small>
                        </td>
                        <td>
                            <span style="background: <?= $corBadge ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold;">
                                <?= htmlspecialchars($c['servico']) ?>
                            </span>
                        </td>
                        <td><strong>R$ <?= number_format($c['valor'], 2, ',', '.') ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div>

</body>
</html>
