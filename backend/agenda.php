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

    // 1. Pegar a duração do serviço atual
    $stmt = $pdo->prepare("SELECT duracao FROM servico WHERE id_servico = ?");
    $stmt->execute([$idServico]);
    $duracaoNova = $stmt->fetchColumn();

    $inicioNovo = $hora; // Ex: "13:00"
    $fimNovo = date("H:i:s", strtotime($hora) + ($duracaoNova * 60));

    // 2. BUSCAR CONFLITOS
    // Verifica se existe algum agendamento onde o horário se sobrepõe
    $stmt = $pdo->prepare("
        SELECT a.hora, s.duracao 
        FROM agenda a
        JOIN servico s ON a.id_servico = s.id_servico
        WHERE a.data = ? 
        AND a.id_profissional = ? 
        AND a.status != 'CANCELADO'
    ");
    $stmt->execute([$data, $idProfissional]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($agendamentos as $ag) {
        $inicioExistente = $ag['hora'];
        $fimExistente = date("H:i:s", strtotime($inicioExistente) + ($ag['duracao'] * 60));

        // Lógica de colisão de horários
        if ($inicioNovo < $fimExistente && $fimNovo > $inicioExistente) {
            return "OCUPADO"; // Encontrou um conflito de horário!
        }
    }

    // 3. SE CHEGOU AQUI, ESTÁ LIVRE. PODE SALVAR.
    try {
        $sql = "INSERT INTO agenda (id_cliente, id_profissional, id_servico, data, hora, status) 
                VALUES (?, ?, ?, ?, ?, 'PENDENTE')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idCliente, $idProfissional, $idServico, $data, $hora]);
        return true;
    } catch (Exception $e) {
        return false;
    }
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
function editarAgendamento($id, $data, $hora, $idServico) {
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE agenda 
        SET data = ?, hora = ?, id_servico = ?
        WHERE id_agendamento = ?
    ");

    return $stmt->execute([$data, $hora, $idServico, $id]);
}
function excluirAgendamento($id) {
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM agenda WHERE id_agendamento = ?");
    return $stmt->execute([$id]);
}
?>
