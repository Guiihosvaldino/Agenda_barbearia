<?php
require_once("../../backend/admin.php");
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

// Mês/ano atual como padrão
$ano = $_GET['ano'] ?? date("Y");
$mes = $_GET['mes'] ?? date("m");

// =============================
// RESUMO FINANCEIRO POR PROFISSIONAL
// =============================
$resumo = calcularFinanceiro($ano, $mes);

// =============================
// SERVIÇOS POR PROFISSIONAL
// =============================
global $pdo;

$stmtServ = $pdo->prepare("
    SELECT 
        p.nome AS profissional,
        s.nome AS servico,
        COUNT(*) AS quantidade,
        SUM(s.valor) AS total
    FROM agenda a
    INNER JOIN profissional p ON p.id_profissional = a.id_profissional
    INNER JOIN servico s ON s.id_servico = a.id_servico
    WHERE 
        a.status = 'CONFIRMADO'
        AND YEAR(a.data) = ?
        AND MONTH(a.data) = ?
    GROUP BY p.nome, s.nome
    ORDER BY p.nome, total DESC
");

$stmtServ->execute([$ano, $mes]);
$servicosProfissionais = $stmtServ->fetchAll(PDO::FETCH_ASSOC);

// =============================
// AGRUPAR POR PROFISSIONAL
// =============================
$servicosAgrupados = [];

foreach ($servicosProfissionais as $s) {
    $servicosAgrupados[$s['profissional']][] = $s;
}

$profissionalSelecionado = $_GET['profissional'] ?? null;

// =============================
// DETALHAMENTO DOS ATENDIMENTOS
// =============================
$stmt = $pdo->prepare("SELECT a.data, a.hora, c.nome AS cliente, 
                              p.nome AS profissional, 
                              s.nome AS servico, s.valor
                       FROM agenda a
                       INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                       INNER JOIN profissional p ON a.id_profissional = p.id_profissional
                       INNER JOIN servico s ON a.id_servico = s.id_servico
                       WHERE YEAR(a.data) = ? 
                       AND MONTH(a.data) = ? 
                       AND a.status = 'CONFIRMADO'
                       ORDER BY a.data, a.hora ASC");

$stmt->execute([$ano, $mes]);
$detalhes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalGeral = calcularFinanceiroTotal($ano, $mes);

// =============================
// PACOTES DE CORTES PAGOS NO MÊS
// =============================
$stmtPacotes = $pdo->prepare("
    SELECT p.*, c.nome AS cliente, c.celular
    FROM pacote_cliente p
    INNER JOIN cliente c ON c.id_cliente = p.id_cliente
    WHERE YEAR(p.data_pagamento) = ? AND MONTH(p.data_pagamento) = ?
    ORDER BY p.data_pagamento DESC, p.id_pacote DESC
");
$stmtPacotes->execute([$ano, $mes]);
$pacotesMes = $stmtPacotes->fetchAll(PDO::FETCH_ASSOC);

$totalPacotesMes = 0;
foreach ($pacotesMes as $pac) {
    $totalPacotesMes += floatval($pac['valor']);
}

$totalServicosMes = 0;
foreach ($resumo as $r) {
    $totalServicosMes += floatval($r['total']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Financeiro</title>

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
    background: #fff;
    color: #333;
    max-width: 1000px;
    margin: 30px auto;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

h2, h3 {
    text-align: center;
    color: #660a18;
}

form {
    text-align: center;
    margin-bottom: 25px;
}

input[type="number"] {
    padding: 8px;
    width: 90px;
    border-radius: 6px;
    border: 1px solid #999;
    font-size: 14px;
}

button {
    padding: 8px 15px;
    background: #660a18;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

button:hover {
    background: #480610;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th {
    background: #660a18;
    color: #fff;
    padding: 10px;
}

td {
    padding: 10px;
    border: 1px solid #ddd;
}

tr:nth-child(even) { background: #f8f8f8; }
tr:nth-child(odd) { background: #ffffff; }

.total-geral {
    font-size: 1.2em;
    font-weight: bold;
    color: #660a18;
}

.voltar {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 14px;
    background: #660a18;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
}

.voltar:hover {
    background: #480610;
}

.logo {
    width: 350px;
    margin-bottom: -70px;
    margin-left: auto;
    margin-right: auto;
    display: block;
}

.link-profissional {
    text-decoration: none;
    color: #660a18;
    font-weight: bold;
}

.link-profissional:hover {
    text-decoration: underline;
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

<h2>📊 Financeiro - <?= $mes ?>/<?= $ano ?></h2>

<form method="GET">
    <label>Mês:</label>
    <input type="number" name="mes" min="1" max="12" value="<?= $mes ?>" required>

    <label>Ano:</label>
    <input type="number" name="ano" value="<?= $ano ?>" required>

    <button type="submit">Filtrar</button>
</form>

<h3>💈 Resumo por Profissional</h3>

<table>
<tr>
    <th>Profissional</th>
    <th>Total R$</th>
</tr>

<?php foreach ($servicosAgrupados as $nome => $lista): 
    $totalProf = array_sum(array_column($lista, 'total'));
?>
<tr>
    <td>
        <a class="link-profissional"
           href="?mes=<?= $mes ?>&ano=<?= $ano ?>&profissional=<?= urlencode($nome) ?>">
           <?= $nome ?>
        </a>
    </td>
    <td>R$ <?= number_format($totalProf, 2, ',', '.') ?></td>
</tr>
<?php endforeach; ?>

<tr>
    <td><strong>TOTAL EM CORTES / SERVIÇOS</strong></td>
    <td><strong>R$ <?= number_format($totalServicosMes, 2, ',', '.') ?></strong></td>
</tr>
<tr>
    <td><strong>TOTAL EM PACOTES VENDIDOS</strong></td>
    <td><strong style="color: #1e7e34;">+ R$ <?= number_format($totalPacotesMes, 2, ',', '.') ?></strong></td>
</tr>
<tr style="background: #fdf2f4;">
    <td class="total-geral" style="font-size: 1.25em;">TOTAL GERAL CONSOLIDADO</td>
    <td class="total-geral" style="font-size: 1.25em; color: #660a18;">R$ <?= number_format($totalGeral, 2, ',', '.') ?></td>
</tr>
</table>

<?php
if ($profissionalSelecionado):

    // ================================
    // RESUMO DE SERVIÇOS DO PROFISSIONAL
    // ================================
    $stmtResumoProf = $pdo->prepare("
        SELECT 
            s.nome AS servico,
            COUNT(*) AS quantidade,
            SUM(s.valor) AS total
        FROM agenda a
        INNER JOIN profissional p ON p.id_profissional = a.id_profissional
        INNER JOIN servico s ON s.id_servico = a.id_servico
        WHERE 
            p.nome = ?
            AND a.status = 'CONFIRMADO'
            AND YEAR(a.data) = ?
            AND MONTH(a.data) = ?
        GROUP BY s.nome
        ORDER BY total DESC
    ");

    $stmtResumoProf->execute([$profissionalSelecionado, $ano, $mes]);
    $resumoProf = $stmtResumoProf->fetchAll(PDO::FETCH_ASSOC);

    // ================================
    // CLIENTES ATENDIDOS PELO PROFISSIONAL
    // ================================
    $stmtClientes = $pdo->prepare("
        SELECT 
            a.data,
            a.hora,
            c.nome AS cliente,
            s.nome AS servico,
            s.valor
        FROM agenda a
        INNER JOIN cliente c ON c.id_cliente = a.id_cliente
        INNER JOIN profissional p ON p.id_profissional = a.id_profissional
        INNER JOIN servico s ON s.id_servico = a.id_servico
        WHERE 
            p.nome = ?
            AND a.status = 'CONFIRMADO'
            AND YEAR(a.data) = ?
            AND MONTH(a.data) = ?
        ORDER BY a.data, a.hora ASC
    ");

    $stmtClientes->execute([$profissionalSelecionado, $ano, $mes]);
    $clientesProf = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>📌 Resumo de Serviços - <?= $profissionalSelecionado ?></h3>

<table>
<tr>
    <th>Serviço</th>
    <th>Quantidade</th>
    <th>Total R$</th>
</tr>

<?php foreach ($resumoProf as $r): ?>
<tr>
    <td><?= $r['servico'] ?></td>
    <td><?= $r['quantidade'] ?></td>
    <td>R$ <?= number_format($r['total'], 2, ',', '.') ?></td>
</tr>
<?php endforeach; ?>

</table>

<br>

<h3>👥 Clientes Atendidos</h3>

<table>
<tr>
    <th>Data</th>
    <th>Hora</th>
    <th>Cliente</th>
    <th>Serviço</th>
    <th>Valor</th>
</tr>

<?php foreach ($clientesProf as $c): ?>
<tr>
    <td><?= date("d/m/Y", strtotime($c['data'])) ?></td>
    <td><?= $c['hora'] ?></td>
    <td><?= $c['cliente'] ?></td>
    <td><?= $c['servico'] ?></td>
    <td>R$ <?= number_format($c['valor'], 2, ',', '.') ?></td>
</tr>
<?php endforeach; ?>

</table>

<?php endif; ?>

<br>

<h3>📦 Pacotes de Cortes Vendidos / Pagos (Mês <?= str_pad($mes, 2, '0', STR_PAD_LEFT) ?>/<?= $ano ?>)</h3>

<?php if (empty($pacotesMes)): ?>
    <p style="text-align: center; color: #777; padding: 15px; background: #f9f9f9; border-radius: 8px;">Nenhum pacote vendido ou com pagamento lançado neste mês.</p>
<?php else: ?>
<table>
<tr>
    <th>Data Pagamento</th>
    <th>Cliente</th>
    <th>Descrição do Pacote</th>
    <th>Atendimentos</th>
    <th>Valor Pago</th>
    <th>Status</th>
</tr>
<?php foreach ($pacotesMes as $pac): ?>
<tr>
    <td><strong><?= date("d/m/Y", strtotime($pac['data_pagamento'])) ?></strong></td>
    <td><?= htmlspecialchars($pac['cliente']) ?> <small style="color: #777;">(<?= htmlspecialchars($pac['celular']) ?>)</small></td>
    <td><strong><?= htmlspecialchars($pac['descricao']) ?></strong></td>
    <td><?= $pac['qtd_usada'] ?> de <?= $pac['qtd_total'] ?> atendimentos</td>
    <td><strong style="color: #1e7e34;">R$ <?= number_format($pac['valor'], 2, ',', '.') ?></strong></td>
    <td>
        <span style="padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 0.85em; background: <?= $pac['status'] === 'ABERTO' ? '#e3f2fd; color: #1565c0;' : '#efebe9; color: #4e342e;' ?>">
            <?= $pac['status'] ?>
        </span>
    </td>
</tr>
<?php endforeach; ?>
<tr style="background: #fdf2f4;">
    <td colspan="4" style="text-align: right; font-weight: bold; color: #660a18;">SUBTOTAL EM PACOTES:</td>
    <td colspan="2" style="font-weight: bold; color: #1e7e34; font-size: 1.1em;">R$ <?= number_format($totalPacotesMes, 2, ',', '.') ?></td>
</tr>
</table>
<?php endif; ?>

<br>
<a class="voltar" href="agenda.php">⬅ Voltar para Agenda</a>
<a class="voltar" href="admin_pacotes.php" style="margin-left: 10px; background: #8a1224;">📦 Gerenciar Pacotes</a>

</div>

</body>
</html>