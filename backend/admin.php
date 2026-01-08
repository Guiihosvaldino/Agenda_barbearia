<?php
require_once("conexao.php");

function listarAgendamentosDoDia($data) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.id_agenda, c.nome AS cliente, p.nome AS profissional, 
                                  s.nome AS servico, s.valor, a.hora, a.status
                           FROM agenda a
                           INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                           INNER JOIN profissional p ON a.id_profissional = p.id_profissional
                           INNER JOIN servico s ON a.id_servico = s.id_servico
                           WHERE a.data = ?
                           ORDER BY a.hora ASC");
    $stmt->execute([$data]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function atualizarStatusAgendamento($id_agenda, $status) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE agenda SET status = ? WHERE id_agenda = ?");
    return $stmt->execute([$status, $id_agenda]);
}

function calcularFinanceiro($ano, $mes) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.nome AS profissional, SUM(s.valor) AS total
                           FROM agenda a
                           INNER JOIN profissional p ON a.id_profissional = p.id_profissional
                           INNER JOIN servico s ON a.id_servico = s.id_servico
                           WHERE YEAR(a.data) = ? AND MONTH(a.data) = ? AND a.status = 'CONFIRMADO'
                           GROUP BY p.id_profissional");
    $stmt->execute([$ano, $mes]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calcularFinanceiroTotal($ano, $mes) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT SUM(s.valor) AS total_geral
                           FROM agenda a
                           INNER JOIN servico s ON a.id_servico = s.id_servico
                           WHERE YEAR(a.data) = ? AND MONTH(a.data) = ? AND a.status = 'CONFIRMADO'");
    $stmt->execute([$ano, $mes]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total_geral'] ?? 0;
}


require_once("conexao.php");

function buscarAdminPorEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function criarAdmin($nome, $email, $senha) {
    global $pdo;
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admin (nome, email, senha) VALUES (?, ?, ?)");
    return $stmt->execute([$nome, $email, $hash]);
}


?>
