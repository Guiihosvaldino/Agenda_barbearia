<?php
require_once("../../backend/admin.php");

// Mês/ano atual como padrão
$ano = $_GET['ano'] ?? date("Y");
$mes = $_GET['mes'] ?? date("m");

// Financeiro resumido por profissional
$resumo = calcularFinanceiro($ano, $mes);

// Financeiro detalhado (todos os atendimentos confirmados do mês)
global $pdo;
$stmt = $pdo->prepare("SELECT a.data, a.hora, c.nome AS cliente, p.nome AS profissional, 
                              s.nome AS servico, s.valor
                       FROM agenda a
                       INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                       INNER JOIN profissional p ON a.id_profissional = p.id_profissional
                       INNER JOIN servico s ON a.id_servico = s.id_servico
                       WHERE YEAR(a.data) = ? AND MONTH(a.data) = ? AND a.status = 'CONFIRMADO'
                       ORDER BY a.data, a.hora");
$stmt->execute([$ano, $mes]);
$detalhes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalGeral = calcularFinanceiroTotal($ano, $mes);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Financeiro</title>
</head>
<body>
    <h2>📊 Financeiro - <?= $mes ?>/<?= $ano ?></h2>

    <!-- Formulário para escolher mês/ano -->
    <form method="GET">
        <label>Mês:</label>
        <input type="number" name="mes" value="<?= $mes ?>" min="1" max="12" required>
        <label>Ano:</label>
        <input type="number" name="ano" value="<?= $ano ?>" required>
        <button type="submit">Filtrar</button>
    </form>
    <br>

    <h3>💈 Resumo por Profissional</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Profissional</th>
            <th>Total R$</th>
        </tr>
        <?php foreach ($resumo as $r): ?>
        <tr>
            <td><?= $r['profissional'] ?></td>
            <td>R$ <?= number_format($r['total'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <th>Total Geral</th>
            <th>R$ <?= number_format($totalGeral, 2, ',', '.') ?></th>
        </tr>
    </table>

    <h3>📋 Detalhamento</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Data</th>
            <th>Hora</th>
            <th>Cliente</th>
            <th>Profissional</th>
            <th>Serviço</th>
            <th>Valor</th>
        </tr>
        <?php foreach ($detalhes as $d): ?>
        <tr>
            <td><?= date("d/m/Y", strtotime($d['data'])) ?></td>
            <td><?= $d['hora'] ?></td>
            <td><?= $d['cliente'] ?></td>
            <td><?= $d['profissional'] ?></td>
            <td><?= $d['servico'] ?></td>
            <td>R$ <?= number_format($d['valor'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <a href="agenda.php">📅 Voltar para Agenda</a>
</body>
</html>
