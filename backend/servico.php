<?php
require_once("conexao.php"); // Certifique-se de que este arquivo conecta ao seu banco de dados usando $pdo

// -----------------------------
// AUTO-MIGRAÇÃO: Adiciona a coluna de cor se não existir
// -----------------------------
try {
    $pdo->query("SELECT cor FROM servico LIMIT 1");
} catch (Exception $e) {
    try {
        $pdo->exec("ALTER TABLE servico ADD COLUMN cor VARCHAR(7) DEFAULT '#1e3c72'");
    } catch (Exception $ex) {}
}

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
function adicionarServico($nome, $duracao, $valor, $cor = '#1e3c72') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO servico (nome, duracao, valor, cor) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$nome, $duracao, $valor, $cor]);
}

// -----------------------------
// EDITAR SERVIÇO
// -----------------------------
function editarServico($id, $nome, $duracao, $valor, $cor = '#1e3c72') {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE servico SET nome = ?, duracao = ?, valor = ?, cor = ? WHERE id_servico = ?");
    return $stmt->execute([$nome, $duracao, $valor, $cor, $id]);
}

// -----------------------------
// EXCLUIR SERVIÇO
// -----------------------------
function excluirServico($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM servico WHERE id_servico = ?");
    return $stmt->execute([$id]);
}

