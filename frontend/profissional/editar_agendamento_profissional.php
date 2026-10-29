<?php
require_once("../../backend/conexao.php");
require_once("../../backend/pacote.php");
session_start();

if (!isset($_SESSION['profissional_id'])) {
    http_response_code(403);
    exit("Acesso negado");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profissional_agenda.php");
    exit();
}

$id_agenda = filter_input(INPUT_POST, 'id_agenda', FILTER_VALIDATE_INT);
$data = $_POST['data'] ?? '';
$hora = $_POST['hora'] ?? '';
$id_servico = filter_input(INPUT_POST, 'id_servico', FILTER_VALIDATE_INT);
$id_profissional = (int)$_SESSION['profissional_id'];
$id_pacote = filter_input(INPUT_POST, 'id_pacote_vinculado', FILTER_VALIDATE_INT);
$abater_corte = isset($_POST['abater_corte']) && $_POST['abater_corte'] === '1';

if (!$id_agenda || !$id_servico || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) || !preg_match('/^\d{2}:\d{2}$/', $hora)) {
    header("Location: profissional_agenda.php?erro=editar&msg=" . urlencode("Confira a data, o horário e o serviço."));
    exit();
}

try {
    garantirTabelaUsosPacote();
    $pdo->beginTransaction();

    $stmt_agenda = $pdo->prepare("
        SELECT id_agenda, id_cliente
        FROM agenda
        WHERE id_agenda = :id_agenda AND id_profissional = :id_profissional
        FOR UPDATE
    ");
    $stmt_agenda->execute([
        ':id_agenda' => $id_agenda,
        ':id_profissional' => $id_profissional,
    ]);
    $agendamento = $stmt_agenda->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        throw new RuntimeException("Agendamento não encontrado para este profissional.");
    }

    $stmt_atualizar = $pdo->prepare("
        UPDATE agenda
        SET data = :data, hora = :hora, id_servico = :id_servico
        WHERE id_agenda = :id_agenda AND id_profissional = :id_profissional
    ");
    $stmt_atualizar->execute([
        ':data' => $data,
        ':hora' => $hora,
        ':id_servico' => $id_servico,
        ':id_agenda' => $id_agenda,
        ':id_profissional' => $id_profissional,
    ]);

    $stmt_uso = $pdo->prepare("SELECT id_pacote FROM pacote_uso_agenda WHERE id_agenda = ?");
    $stmt_uso->execute([$id_agenda]);
    $pacoteJaUsado = $stmt_uso->fetchColumn();

    if ($pacoteJaUsado) {
        if (!sincronizarUsoPacoteComAgendamento($id_agenda, (int)$agendamento['id_cliente'], $data, $hora)) {
            throw new RuntimeException("Não foi possível atualizar o atendimento vinculado ao pacote.");
        }
    } elseif ($abater_corte) {
        if (!$id_pacote) {
            throw new RuntimeException("Selecione um pacote aberto para abater o atendimento.");
        }

        if (!registrarUsoPacoteNoAgendamento(
            (int)$id_pacote,
            (int)$id_agenda,
            (int)$agendamento['id_cliente'],
            $data,
            $hora
        )) {
            throw new RuntimeException("O pacote selecionado não está aberto, não pertence ao cliente ou não possui atendimentos disponíveis.");
        }
    }

    $pdo->commit();
    header("Location: profissional_agenda.php?data=" . urlencode($data) . "&sucesso=1");
    exit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header("Location: profissional_agenda.php?data=" . urlencode($data) . "&erro=editar&msg=" . urlencode($e->getMessage()));
    exit();
}
