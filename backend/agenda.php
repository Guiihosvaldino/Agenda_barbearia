<?php
require_once("conexao.php");

function salvarAgendamento($idCliente, $idProfissional, $idServico, $data, $hora) {
    global $pdo;

    // Verificar se já existe agendamento nesse horário
    $stmt = $pdo->prepare("SELECT * FROM agenda 
                           WHERE id_profissional = ? AND data = ? AND hora = ?");
    $stmt->execute([$idProfissional, $data, $hora]);

    if ($stmt->rowCount() > 0) {
        return false; // horário ocupado
    }

    // Inserir agendamento
    $stmt = $pdo->prepare("INSERT INTO agenda 
        (id_cliente, id_profissional, id_servico, data, hora, status) 
        VALUES (?, ?, ?, ?, ?, 'PENDENTE')");
    $stmt->execute([$idCliente, $idProfissional, $idServico, $data, $hora]);
    return true;
}
?>
