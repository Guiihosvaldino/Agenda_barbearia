<?php
require_once("conexao.php");

/**
 * Retorna funcionamento do dia baseado na data
 */
function funcionamentoPorData($data) {
    global $pdo;

    // Descobre o dia da semana em inglês
    $diaSemana = strtoupper(date('l', strtotime($data)));

    // Converte para o ENUM do banco
    $map = [
        'MONDAY'    => 'SEGUNDA',
        'TUESDAY'   => 'TERCA',
        'WEDNESDAY' => 'QUARTA',
        'THURSDAY'  => 'QUINTA',
        'FRIDAY'    => 'SEXTA',
        'SATURDAY'  => 'SABADO',
        'SUNDAY'    => 'DOMINGO'
    ];

    if (!isset($map[$diaSemana])) {
        return false;
    }

    $dia = $map[$diaSemana];

    $stmt = $pdo->prepare("SELECT * FROM funcionamento WHERE dia_semana = ? LIMIT 1");
    $stmt->execute([$dia]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Salva o agendamento com validação de funcionamento
 */
function salvarAgendamento($idCliente, $idProfissional, $idServico, $data, $hora) {
    global $pdo;

    // 🔒 VERIFICA SE A BARBEARIA FUNCIONA NO DIA
    $func = funcionamentoPorData($data);

    if (!$func || $func['ativo'] != 1) {
        return "FECHADO";
    }

    // 🔒 VERIFICA HORÁRIO
    if ($hora < $func['abre'] || $hora > $func['fecha']) {
        return "FORA_HORARIO";
    }

    // 🔒 VERIFICA SE HORÁRIO JÁ ESTÁ OCUPADO
    $stmt = $pdo->prepare("
        SELECT 1 FROM agenda 
        WHERE id_profissional = ? 
        AND data = ? 
        AND hora = ?
    ");
    $stmt->execute([$idProfissional, $data, $hora]);

    if ($stmt->rowCount() > 0) {
        return "OCUPADO";
    }

    // ✅ INSERE AGENDAMENTO
    $stmt = $pdo->prepare("
        INSERT INTO agenda 
        (id_cliente, id_profissional, id_servico, data, hora, status) 
        VALUES (?, ?, ?, ?, ?, 'PENDENTE')
    ");

    $stmt->execute([
        $idCliente,
        $idProfissional,
        $idServico,
        $data,
        $hora
    ]);

    return true;
}

/**
 * ADMIN — LISTA FUNCIONAMENTO
 */
function listarFuncionamento() {
    global $pdo;
    return $pdo->query("SELECT * FROM funcionamento ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * ADMIN — SALVA FUNCIONAMENTO
 */
function salvarFuncionamento($dia, $abre, $fecha, $ativo) {
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE funcionamento 
        SET abre = ?, fecha = ?, ativo = ?
        WHERE dia_semana = ?
    ");

    return $stmt->execute([$abre, $fecha, $ativo, $dia]);
}
function gerarHorariosDisponiveis($data) {
    $horarios = [];

    for ($h = 9; $h <= 18; $h++) {
        $horarios[] = sprintf("%02d:00", $h);
    }

    return $horarios;
}
function listarAgendamentosFuturosCliente($idCliente) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            a.data,
            a.hora,
            p.nome AS profissional,
            s.nome AS servico,
            s.valor
        FROM agenda a
        INNER JOIN profissional p ON p.id_profissional = a.id_profissional
        INNER JOIN servico s ON s.id_servico = a.id_servico
        WHERE a.id_cliente = ?
        AND (a.data > CURDATE() 
            OR (a.data = CURDATE() AND a.hora >= CURTIME()))
        AND a.status != 'CANCELADO'
        ORDER BY a.data ASC, a.hora ASC
    ");

    $stmt->execute([$idCliente]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
