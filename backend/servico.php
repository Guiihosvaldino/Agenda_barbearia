<?php
require_once("conexao.php"); // Certifique-se de que este arquivo conecta ao seu banco de dados usando $pdo

// -----------------------------
// LISTAR TODOS OS SERVIÇOS
// -----------------------------
function listarServicosAdmin() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM servico ORDER BY nome ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// -----------------------------
// BUSCAR SERVIÇO POR ID
// -----------------------------
function buscarServicoPorId($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM servico WHERE id_servico = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// -----------------------------
// ADICIONAR SERVIÇO
// -----------------------------
function adicionarServico($nome, $duracao, $valor) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO servico (nome, duracao, valor) VALUES (?, ?, ?)");
    return $stmt->execute([$nome, $duracao, $valor]);
}

// -----------------------------
// EDITAR SERVIÇO
// -----------------------------
function editarServico($id, $nome, $duracao, $valor) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE servico SET nome = ?, duracao = ?, valor = ? WHERE id_servico = ?");
    return $stmt->execute([$nome, $duracao, $valor, $id]);
}

// -----------------------------
// EXCLUIR SERVIÇO
// -----------------------------
function excluirServico($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM servico WHERE id_servico = ?");
    return $stmt->execute([$id]);
}

