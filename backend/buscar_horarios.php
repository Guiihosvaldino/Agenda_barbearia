<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

require_once("../backend/conexao.php");

$data = $_GET['data'] ?? null;
$idServico = $_GET['servico'] ?? null;
$idProf = $_GET['profissional'] ?? null;

if (!$data || !$idServico || !$idProf) {
    echo json_encode([]);
    exit;
}

// 1. Duração do serviço que o cliente QUER agendar agora
$stmt = $pdo->prepare("SELECT duracao FROM servico WHERE id_servico=?");
$stmt->execute([$idServico]);
$duracaoNova = (int)$stmt->fetchColumn();

if (!$duracaoNova) {
    echo json_encode([]);
    exit;
}

// 2. Identificar o dia da semana
$dias_map = [0 => 'DOMINGO', 1 => 'SEGUNDA', 2 => 'TERCA', 3 => 'QUARTA', 4 => 'QUINTA', 5 => 'SEXTA', 6 => 'SABADO'];
$dia_nome = $dias_map[date('w', strtotime($data))];

// 3. Buscar funcionamento
$stmt = $pdo->prepare("SELECT abre, fecha FROM funcionamento WHERE dia_semana = ? AND ativo = 1");
$stmt->execute([$dia_nome]);
$func = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$func) {
    echo json_encode([]);
    exit;
}

$inicioExpediente = strtotime($func['abre']);
$fimExpediente = strtotime($func['fecha']);
$horariosDisponiveis = [];

// 4. Buscar TODOS os agendamentos já existentes para este profissional neste dia
// Precisamos trazer a duração de cada um para calcular o fim de cada compromisso
$stmt = $pdo->prepare("
    SELECT a.hora, s.duracao 
    FROM agenda a
    JOIN servico s ON a.id_servico = s.id_servico
    WHERE a.data = ? AND a.id_profissional = ? AND a.status != 'CANCELADO'
");
$stmt->execute([$data, $idProf]);
$agendamentosDoDia = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Gerar horários e testar sobreposição
// Aqui definimos de quanto em quanto tempo o cliente vê as opções (ex: de 15 em 15 min)
$passo = 15; 
$atual = $inicioExpediente;

while ($atual + ($duracaoNova * 60) <= $fimExpediente) {
    $horaInicioNova = date("H:i:s", $atual);
    $horaFimNova = date("H:i:s", $atual + ($duracaoNova * 60));
    
    $colidiu = false;

    foreach ($agendamentosDoDia as $agendado) {
        $inicioExistente = $agendado['hora'];
        // Calcula o fim do agendamento que já está no banco
        $fimExistente = date("H:i:s", strtotime($inicioExistente) + ($agendado['duracao'] * 60));

        // Lógica de Sobreposição: 
        // Um novo serviço (A) conflita com um existente (B) se:
        // O início de A for antes do fim de B  E  o fim de A for depois do início de B
        if ($horaInicioNova < $fimExistente && $horaFimNova > $inicioExistente) {
            $colidiu = true;
            break;
        }
    }

    if (!$colidiu) {
        $horariosDisponiveis[] = date("H:i", $atual);
    }

    // Avança o ponteiro (sugestão: avançar de 15 em 15 min para dar mais opções ao cliente)
    $atual += $passo * 60;
}

echo json_encode($horariosDisponiveis);