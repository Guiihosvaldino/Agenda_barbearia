<?php
require_once("../../backend/agenda.php");

$id = $_POST['id_agendamento'];
$data = $_POST['data'];
$hora = $_POST['hora'];
$servico = $_POST['id_servico'];

editarAgendamento($id, $data, $hora, $servico);

header("Location: agenda.php");
exit;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_agendamento = $_POST['id_agendamento'];
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $id_servico = $_POST['id_servico'];

    // Prepara a conexão (ajuste conforme sua variável de conexão no backend)
    global $conn; 

    // Query de atualização
    $sql = "UPDATE agendamentos SET 
            data = ?, 
            hora = ?, 
            id_servico = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $data, $hora, $id_servico, $id_agendamento);

    if ($stmt->execute()) {
        // Sucesso: Volta para a agenda na data que estava sendo editada
        header("Location: agenda.php?data=$data&status=sucesso_edit");
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }

    $stmt->close();
}