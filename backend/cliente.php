<?php
require_once("conexao.php");

function buscarClientePorCelular($celular) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE celular = ?");
    $stmt->execute([$celular]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function cadastrarCliente($nome, $celular, $dataNascimento) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO cliente (nome, celular, data_nascimento) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $celular, $dataNascimento]);
    return $pdo->lastInsertId();
}
?>
