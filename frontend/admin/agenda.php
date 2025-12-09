<?php

require_once("../../backend/admin.php");

// Verifica se uma data foi selecionada
$data = $_GET['data'] ?? null;

// Ações de confirmar/cancelar
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $acao = $_GET['acao'];
    $id = $_GET['id'];

    if ($acao == "confirmar") {
        atualizarStatusAgendamento($id, "CONFIRMADO");
    } elseif ($acao == "cancelar") {
        atualizarStatusAgendamento($id, "CANCELADO");
    }
}

// Carrega agendamentos somente se houver data
$agendamentos = [];
if ($data) {
    $agendamentos = listarAgendamentosDoDia($data);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Agenda do Dia</title>

    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            font-family: Arial, Helvetica, sans-serif;
            color: #fff;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        h2, h3 {
            text-align: center;
            margin-bottom: 15px;
        }

        .container {
            background: #ffffff;
            color: #333;
            max-width: 900px;
            margin: 0 auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        form {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="date"] {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #aaa;
        }

        button {
            padding: 8px 15px;
            background: #1e3c72;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
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

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr:nth-child(odd) {
            background: #ffffff;
        }

        .btn-confirmar {
            background: #28a745;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
        }

        .btn-confirmar:hover {
            background: #1e7e34;
        }

        .btn-cancelar {
            background: #dc3545;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
        }

        .btn-cancelar:hover {
            background: #b02a37;
        }

        .status-confirmado {
            color: green;
            font-weight: bold;
        }

        .status-cancelado {
            color: red;
            font-weight: bold;
        }

        .alert {
            background: #ffeeba;
            color: #856404;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            margin-top: 20px;
            border: 1px solid #ffeeba;
        }

    </style>

</head>
<body>
    <!-- MENU SUPERIOR -->
<div class="menu">
    <a href="agenda.php">📅 Agenda</a>
    <a href="financeiro.php">💰 Financeiro</a>
    <a href="admin_agendar.php">➕ Novo Agendamento</a>
    <a href="admin_profissionais.php">👤 Profissionais</a>
    <a href="admin_servicos.php">💈 Serviços</a>
</div>

<style>
.menu {
    background: #0d2a52;
    padding: 12px 20px;
    display: flex;
    gap: 20px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.menu a {
    color: #fff;
    text-decoration: none;
    font-size: 1.1em;
    font-weight: bold;
    padding: 6px 10px;
    border-radius: 6px;
}

.menu a:hover {
    background: #1e3c72;
}
</style>


<div class="container">

    <h2>📅 Consulta de Agenda</h2>

    <!-- Formulário para escolher data -->
    <form method="GET">
        <label><b>Selecione uma data:</b></label><br><br>
        <input type="date" name="data" value="<?= $data ?>" required>
        <button type="submit">Buscar</button>
    </form>

    <hr><br>

    <?php if ($data): ?>

        <h3>📌 Agenda do dia <?= date("d/m/Y", strtotime($data)) ?></h3>

        <?php if (count($agendamentos) > 0): ?>

            <table>
                <tr>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Profissional</th>
                    <th>Serviço</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>

                <?php foreach ($agendamentos as $a): ?>
                <tr>
                    <td><?= $a['hora'] ?></td>
                    <td><?= $a['cliente'] ?></td>
                    <td><?= $a['profissional'] ?></td>
                    <td><?= $a['servico'] ?></td>
                    <td>R$ <?= number_format($a['valor'], 2, ',', '.') ?></td>

                    <td>
                        <?php if ($a['status'] == "PENDENTE"): ?>
                            <span>Pendente</span>
                        <?php elseif ($a['status'] == "CONFIRMADO"): ?>
                            <span class="status-confirmado">✔ Confirmado</span>
                        <?php else: ?>
                            <span class="status-cancelado">✖ Cancelado</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($a['status'] == "PENDENTE"): ?>
                            <a class="btn-confirmar" href="?acao=confirmar&id=<?= $a['id_agenda'] ?>&data=<?= $data ?>">Confirmar</a>
                            <a class="btn-cancelar" href="?acao=cancelar&id=<?= $a['id_agenda'] ?>&data=<?= $data ?>">Cancelar</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table>

        <?php else: ?>

            <div class="alert">⚠ Nenhum agendamento encontrado para esta data.</div>

        <?php endif; ?>

    <?php else: ?>

        <div class="alert">Selecione um dia acima para visualizar a agenda.</div>

    <?php endif; ?>

</div>

</body>
</html>
