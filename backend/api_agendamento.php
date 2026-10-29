<?php
// API endpoint para salvar agendamento com validação avançada
// Recebe dados via POST (application/x-www-form-urlencoded ou JSON)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../backend/conexao.php';
require_once __DIR__.'/../backend/agenda.php';

// Função auxiliar para ler payload JSON quando necessário
function getRequestData() {
    $data = [];
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? [];
    } else {
        $data = $_POST;
    }
    return $data;
}

$req = getRequestData();

// Validação mínima dos campos recebidos
$required = ['id_profissional', 'data', 'hora', 'id_servico', 'nome_cliente_completo'];
foreach ($required as $field) {
    if (empty($req[$field])) {
        echo json_encode(['success' => false, 'code' => 'MISSING_FIELD', 'message' => "Campo obrigatório ausente: $field"]);
        exit;
    }
}

// Separar nome e telefone (ex.: "Guilherme - 3499-1234")
$partes = explode(' - ', $req['nome_cliente_completo']);
$nomeCliente = trim($partes[0]);

// Busca o cliente pelo nome (assumindo nome único)
$stmtCliente = $pdo->prepare('SELECT id_cliente FROM cliente WHERE nome = :nome LIMIT 1');
$stmtCliente->execute([':nome' => $nomeCliente]);
$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
if (!$cliente) {
    echo json_encode(['success' => false, 'code' => 'CLIENT_NOT_FOUND', 'message' => 'Cliente não encontrado.']);
    exit;
}
$idCliente = $cliente['id_cliente'];

$idProfissional = $req['id_profissional'];
$idServico = $req['id_servico'];
$data = $req['data'];
$hora = $req['hora']; // já esperado em HH:MM (5 min arredondado)

// ---------- NOVA LÓGICA DE VALIDAÇÃO ----------
// 1. Verifica se o horário está dentro do expediente
$dadosFunc = funcionamentoPorData($data);
if (!$dadosFunc || (int)$dadosFunc['ativo'] !== 1) {
    echo json_encode(['success' => false, 'code' => 'CLOSED', 'message' => 'Barbearia está fechada na data selecionada.']);
    exit;
}
$abertura = $dadosFunc['abre']; // ex.: "09:00"
$fechamento = $dadosFunc['fecha']; // ex.: "18:00"
// Converte para timestamps
$tsAbertura = strtotime($abertura);
$tsFechamento = strtotime($fechamento);
$tsHora = strtotime($hora);
if ($tsHora < $tsAbertura || $tsHora >= $tsFechamento) {
    echo json_encode(['success' => false, 'code' => 'OUT_OF_RANGE', 'message' => 'Horário fora do expediente.']);
    exit;
}

// 2. Busca duração do serviço
$stmtDur = $pdo->prepare('SELECT duracao FROM servico WHERE id_servico = ?');
$stmtDur->execute([$idServico]);
$duracao = (int) $stmtDur->fetchColumn(); // em minutos
if ($duracao <= 0) {
    echo json_encode(['success' => false, 'code' => 'INVALID_DURATION', 'message' => 'Duração do serviço inválida.']);
    exit;
}

// Calcula horário de término
$tsFim = $tsHora + ($duracao * 60);
if ($tsFim > $tsFechamento) {
    echo json_encode(['success' => false, 'code' => 'END_AFTER_CLOSE', 'message' => 'O agendamento ultrapassa o horário de fechamento.']);
    exit;
}

// 3. Verifica conflitos de sobreposição para o mesmo profissional
$stmtConflitos = $pdo->prepare(
    "SELECT a.hora, s.duracao FROM agenda a " .
    "JOIN servico s ON a.id_servico = s.id_servico " .
    "WHERE a.data = ? AND a.id_profissional = ? AND a.status != 'CANCELADO'"
);
$stmtConflitos->execute([$data, $idProfissional]);
$conflitos = $stmtConflitos->fetchAll(PDO::FETCH_ASSOC);
foreach ($conflitos as $c) {
    $tsIniExist = strtotime($c['hora']);
    $tsFimExist = $tsIniExist + ($c['duracao'] * 60);
    // Sobreposição: início novo < fim existente && fim novo > início existente
    if ($tsHora < $tsFimExist && $tsFim > $tsIniExist) {
        echo json_encode(['success' => false, 'code' => 'CONFLICT', 'message' => 'Horário já ocupado.']);
        exit;
    }
}

// 4. Inserir agendamento
try {
    $sql = "INSERT INTO agenda (id_cliente, id_profissional, id_servico, data, hora, status) " .
           "VALUES (?, ?, ?, ?, ?, 'PENDENTE')";
    $stmtInsert = $pdo->prepare($sql);
    $stmtInsert->execute([$idCliente, $idProfissional, $idServico, $data, $hora]);
    echo json_encode(['success' => true, 'code' => 'OK', 'message' => 'Agendamento salvo com sucesso.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'code' => 'DB_ERROR', 'message' => $e->getMessage()]);
}
?>
