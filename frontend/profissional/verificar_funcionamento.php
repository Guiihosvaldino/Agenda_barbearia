<?php
require_once("../../backend/agenda.php");

$data = $_GET['data'] ?? null;

if (!$data) {
    echo json_encode(['ativo' => false]);
    exit;
}

$func = funcionamentoDoDia($data);

if (!$func || $func['ativo'] == 0) {
    echo json_encode(['ativo' => false]);
} else {
    echo json_encode([
        'ativo' => true,
        'abre' => $func['abre'],
        'fecha' => $func['fecha']
    ]);
}
?>