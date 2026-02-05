<?php
require_once("../../backend/admin.php");

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
// DETALHAMENTO DOS ATENDIMENTOS
// =============================
$stmt = $pdo->prepare("SELECT a.data, a.hora, c.nome AS cliente, 
                              p.nome AS profissional, 
                              s.nome AS servico, s.valor
                       FROM agenda a
                       INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                       INNER JOIN profissional p ON a.id_profissional = p.id_profissional
                       INNER JOIN servico s ON a.id_servico = s.id_servico
                       WHERE YEAR(a.data) = ? AND MONTH(a.data) = ? AND a.status = 'CONFIRMADO'
                       ORDER BY a.data, a.hora ASC");
$stmt->execute([$ano, $mes]);
$detalhes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalGeral = calcularFinanceiroTotal($ano, $mes);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Financeiro</title>

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
            color: #1e3c72;
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
            background: #1e3c72;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }
        button:hover {
            background: #16315c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #1e3c72;
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
            color: #1e3c72;
        }

        .voltar {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 14px;
            background: #1e3c72;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .voltar:hover {
            background: #16315c;
        }
    </style>
</head>

<body>

<div class="menu">
    <a href="agenda.php">📅 Agenda</a>
    <a href="financeiro.php">💰 Financeiro</a>
    <a href="admin_agendar.php">➕ Novo Agendamento</a>
    <a href="admin_profissionais.php">👤 Profissionais</a>
    <a href="admin_servicos.php">💈 Serviços</a>
    <a href="admin_funcionamento.php">⏰ Funcionamento</a>
    <a href="login_admin.php">🚪 Sair</a>
</div>

<div class="container">

    <h2>📊 Financeiro - <?= $mes ?>/<?= $ano ?></h2>

    <form method="GET">
        <label>Mês:</label>
        <input type="number" name="mes" min="1" max="12" value="<?= $mes ?>" required>

        <label>Ano:</label>
        <input type="number" name="ano" value="<?= $ano ?>" required>

        <button type="submit">Filtrar</button>
    </form>

    <!-- RESUMO FINANCEIRO -->
    <h3>💈 Resumo por Profissional</h3>
    <table>
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
            <td class="total-geral">TOTAL GERAL</td>
            <td class="total-geral">R$ <?= number_format($totalGeral, 2, ',', '.') ?></td>
        </tr>
    </table>

    <!-- TABELA NOVA: SERVIÇOS POR PROFISSIONAL -->
    <h3>🧾 Serviços Realizados por Profissional</h3>

    <table>
        <tr>
            <th>Profissional</th>
            <th>Serviço</th>
            <th>Quantidade</th>
            <th>Total R$</th>
        </tr>

        <?php foreach ($servicosProfissionais as $s): ?>
        <tr>
            <td><?= $s['profissional'] ?></td>
            <td><?= $s['servico'] ?></td>
            <td><?= $s['quantidade'] ?></td>
            <td>R$ <?= number_format($s['total'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>📋 Detalhamento dos Atendimentos</h3>
    <table>
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

    <a class="voltar" href="agenda.php">⬅ Voltar para Agenda</a>

</div>

</body>
</html>
