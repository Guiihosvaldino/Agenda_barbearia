<?php
header('Content-Type: application/json');
require_once("conexao.php");

$data = $_GET['data'] ?? null;

if (!$data) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        a.hora,
        c.nome AS cliente,
        s.nome AS servico,
        p.nome AS profissional
    FROM agenda a
    JOIN cliente c ON c.id_cliente = a.id_cliente
    JOIN servico s ON s.id_servico = a.id_servico
    JOIN profissional p ON p.id_profissional = a.id_profissional
    WHERE a.data = ?
    ORDER BY a.hora
");

$stmt->execute([$data]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
