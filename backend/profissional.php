<?php
require_once("conexao.php");

function listarProfissionais() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM profissional ORDER BY nome");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarServicos() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM servico ORDER BY nome");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
