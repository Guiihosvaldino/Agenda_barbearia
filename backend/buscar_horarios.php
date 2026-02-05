<?php
header('Content-Type: application/json');

require_once("../backend/conexao.php"); // <-- IMPORTANTE

$data = $_GET['data'] ?? null;
$idServico = $_GET['servico'] ?? null;
$idProf = $_GET['profissional'] ?? null;

if (!$data || !$idServico || !$idProf) {
    echo json_encode([]);
    exit;
}

// duração do serviço
$stmt = $pdo->prepare("SELECT duracao FROM servico WHERE id_servico=?");
$stmt->execute([$idServico]);
$duracao = $stmt->fetchColumn();

if (!$duracao) {
    echo json_encode([]);
    exit;
}

// funcionamento do dia
$stmt = $pdo->query("SELECT abre, fecha FROM funcionamento WHERE ativo=1 LIMIT 1");
$func = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$func) {
    echo json_encode([]);
    exit;
}

$abre = strtotime($func['abre']);
$fecha = strtotime($func['fecha']);

$horarios = [];

while ($abre + ($duracao * 60) <= $fecha) {

    $horaFormatada = date("H:i", $abre);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM agenda 
        WHERE data=? AND hora=? AND id_profissional=?
    ");
    $stmt->execute([$data, $horaFormatada, $idProf]);
    $ocupado = $stmt->fetchColumn();

    if (!$ocupado) {
        $horarios[] = $horaFormatada;
    }

    $abre += $duracao * 60;
}

echo json_encode($horarios);
