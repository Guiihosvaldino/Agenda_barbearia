<?php
require_once("conexao.php");

function buscarClientePorCelular($celular) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE celular = ?");
    $stmt->execute([$celular]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function cadastrarCliente($nome, $celular, $senha, $dataNascimento) {
    global $pdo;

    // gera hash seguro
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO cliente (nome, celular, senha, data_nascimento) 
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$nome, $celular, $senhaHash, $dataNascimento]);
    
    return $pdo->lastInsertId();
}
function buscarClientePorNome($nome) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE nome LIKE ?");
    $stmt->execute([$nome]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
