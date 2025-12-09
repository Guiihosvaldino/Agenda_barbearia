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

function listarProfissionaisAdmin() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id_profissional, nome, especialidade FROM profissional ORDER BY nome ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function editarProfissional($id, $nome, $especialidade) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE profissional SET nome = ?, especialidade = ? WHERE id_profissional = ?");
    return $stmt->execute([$nome, $especialidade, $id]);
}

function excluirProfissional($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM profissional WHERE id_profissional = ?");
    return $stmt->execute([$id]);
}

/* === ADICIONAR PROFISSIONAL — FALTAVA === */
function adicionarProfissional($nome, $especialidade) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO profissional (nome, especialidade) VALUES (?, ?)");
    return $stmt->execute([$nome, $especialidade]);
}

/* === USADO PARA EDITAR === */
function buscarProfissionalPorId($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM profissional WHERE id_profissional = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
