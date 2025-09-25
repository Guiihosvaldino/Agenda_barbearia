<?php
require_once("../../backend/admin.php");

$data = date("Y-m-d");

// Se foi clicado em confirmar/cancelar
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $acao = $_GET['acao'];
    $id = $_GET['id'];

    if ($acao == "confirmar") {
        atualizarStatusAgendamento($id, "CONFIRMADO");
    } elseif ($acao == "cancelar") {
        atualizarStatusAgendamento($id, "CANCELADO");
    }
}

// Carregar agenda do dia
$agendamentos = listarAgendamentosDoDia($data);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Agenda do Dia</title>
</head>
<body>
    <h2>📅 Agenda do Dia (<?= date("d/m/Y", strtotime($data)) ?>)</h2>

    <table border="1" cellpadding="5">
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
            <td><?= $a['status'] ?></td>
            <td>
                <?php if ($a['status'] == "PENDENTE"): ?>
                    <a href="?acao=confirmar&id=<?= $a['id_agenda'] ?>">✅ Confirmar</a> | 
                    <a href="?acao=cancelar&id=<?= $a['id_agenda'] ?>">❌ Cancelar</a>
                <?php elseif ($a['status'] == "CONFIRMADO"): ?>
                    <span style="color:green;">✔ Confirmado</span>
                <?php elseif ($a['status'] == "CANCELADO"): ?>
                    <span style="color:red;">✖ Cancelado</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
