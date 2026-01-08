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
function listarFuncionamento() {
    global $pdo;
    return $pdo->query("SELECT * FROM funcionamento")->fetchAll(PDO::FETCH_ASSOC);
}
function salvarFuncionamento($dia, $abre, $fecha, $ativo) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE funcionamento 
        SET abre=?, fecha=?, ativo=? 
        WHERE dia_semana=?
    ");
    return $stmt->execute([$abre, $fecha, $ativo, $dia]);
}
function funcionamentoDoDia($data) {
    global $pdo;

    $diaSemana = strtoupper(strftime('%A', strtotime($data)));

    $map = [
        'MONDAY'=>'SEGUNDA',
        'TUESDAY'=>'TERCA',
        'WEDNESDAY'=>'QUARTA',
        'THURSDAY'=>'QUINTA',
        'FRIDAY'=>'SEXTA',
        'SATURDAY'=>'SABADO',
        'SUNDAY'=>'DOMINGO'
    ];

    $diaSemana = $map[$diaSemana];

    $stmt = $pdo->prepare("SELECT * FROM funcionamento WHERE dia_semana=?");
    $stmt->execute([$diaSemana]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function barbeariaEstaAberta($data) {
    global $pdo;

    $diaSemana = date('N', strtotime($data)); // 1=Segunda ... 7=Domingo

    $stmt = $pdo->prepare("
        SELECT * FROM funcionamento 
        WHERE dia_semana = ? AND ativo = 1
        LIMIT 1
    ");
    $stmt->execute([$diaSemana]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
