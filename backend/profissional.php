<?php
require_once("conexao.php");

/* ============================================================
   LISTAGENS GERAIS
   ============================================================ */
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
    $stmt = $pdo->prepare("SELECT id_profissional, nome, especialidade, email FROM profissional ORDER BY nome ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ============================================================
   CRUD PROFISSIONAL
   ============================================================ */

function adicionarProfissional($nome, $especialidade, $email, $senha) {
    global $pdo;

    // Hash da senha
    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO profissional (nome, especialidade, email, senha)
        VALUES (?, ?, ?, ?)
    ");

    return $stmt->execute([$nome, $especialidade, $email, $hash]);
}

function buscarProfissionalPorId($id) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM profissional WHERE id_profissional = ? LIMIT 1");
    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buscarProfissionalPorEmail($email) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM profissional WHERE email = ?");
    $stmt->execute([$email]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function editarProfissional($id, $nome, $especialidade) {
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE profissional 
        SET nome = ?, especialidade = ?
        WHERE id_profissional = ?
    ");

    return $stmt->execute([$nome, $especialidade, $id]);
}



function excluirProfissional($id) {
    global $pdo;

    // Exclui os agendamentos vinculados
    $stmt1 = $pdo->prepare("DELETE FROM agenda WHERE id_profissional = ?");
    $stmt1->execute([$id]);

    // Agora exclui o profissional
    $stmt2 = $pdo->prepare("DELETE FROM profissional WHERE id_profissional = ?");
    return $stmt2->execute([$id]);
}




function listarAgendaProfissional($idProfissional, $data = null) { 
    global $pdo;

    // Se ninguém passar a data, ele assume a data de hoje
    if (!$data) {
        $data = date('Y-m-d');
    }

    $stmt = $pdo->prepare("
        SELECT 
            a.id_agenda,
            a.data,
            a.hora,
            a.status,
            c.nome AS cliente,
            s.nome AS servico,
            s.valor,
            s.duracao
        FROM agenda a
        INNER JOIN cliente c ON c.id_cliente = a.id_cliente
        INNER JOIN servico s ON s.id_servico = a.id_servico
        WHERE a.id_profissional = ? AND a.data = ?
        ORDER BY a.hora ASC
    ");

    $stmt->execute([$idProfissional, $data]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function buscarAgendamentoPorId($idAgenda) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.*, c.nome as cliente, s.nome as servico 
        FROM agenda a
        INNER JOIN cliente c ON c.id_cliente = a.id_cliente
        INNER JOIN servico s ON s.id_servico = a.id_servico
        WHERE a.id_agenda = ?
    ");
    $stmt->execute([$idAgenda]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



function totalAtendimentosMes($idProf, $ano, $mes) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM agenda 
        WHERE id_profissional = ?
        AND status = 'CONFIRMADO'
        AND YEAR(data) = ?
        AND MONTH(data) = ?
    ");

    $stmt->execute([$idProf, $ano, $mes]);

    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

function tempoTotalTrabalhado($idProf, $ano, $mes) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT SUM(s.duracao) AS total_minutos
        FROM agenda a
        INNER JOIN servico s ON a.id_servico = s.id_servico
        WHERE a.id_profissional = ?
        AND a.status = 'CONFIRMADO'
        AND YEAR(a.data) = ?
        AND MONTH(a.data) = ?
    ");

    $stmt->execute([$idProf, $ano, $mes]);

    return $stmt->fetch(PDO::FETCH_ASSOC)['total_minutos'];
}

function totalFinanceiroMes($idProf, $ano, $mes) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT SUM(s.valor) AS total
        FROM agenda a
        INNER JOIN servico s ON a.id_servico = s.id_servico
        WHERE a.id_profissional = ?
        AND a.status = 'CONFIRMADO'
        AND YEAR(a.data) = ?
        AND MONTH(a.data) = ?
    ");

    $stmt->execute([$idProf, $ano, $mes]);

    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

function resumoPorServico($idProf, $ano, $mes) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            s.nome AS servico,
            COUNT(*) AS qtd,
            SUM(s.valor) AS total
        FROM agenda a
        INNER JOIN servico s ON a.id_servico = s.id_servico
        WHERE a.id_profissional = ?
        AND a.status = 'CONFIRMADO'
        AND YEAR(a.data) = ?
        AND MONTH(a.data) = ?
        GROUP BY s.id_servico
        ORDER BY total DESC
    ");

    $stmt->execute([$idProf, $ano, $mes]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function clientesAtendidosMes($idProf, $ano, $mes) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            c.nome AS cliente,
            c.celular,
            a.data,
            a.hora,
            s.nome AS servico,
            s.valor
        FROM agenda a
        INNER JOIN cliente c ON a.id_cliente = c.id_cliente
        INNER JOIN servico s ON a.id_servico = s.id_servico
        WHERE a.id_profissional = ?
        AND a.status = 'CONFIRMADO'
        AND YEAR(a.data) = ?
        AND MONTH(a.data) = ?
        ORDER BY a.data DESC, a.hora DESC
    ");

    $stmt->execute([$idProf, $ano, $mes]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function salvarTokenResetSenha($id, $token) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE profissional SET reset_token = ? WHERE id_profissional = ?");
    return $stmt->execute([$token, $id]);
}
// Buscar profissional pelo token
function buscarProfissionalPorToken($token) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM profissional WHERE reset_token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Limpar token após redefinir a senha
function limparTokenResetSenha($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE profissional SET reset_token = NULL WHERE id_profissional = ?");
    return $stmt->execute([$id]);
}

// Atualizar senha com hash
function atualizarSenhaProfissional($id, $senha) {
    global $pdo;
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE profissional SET senha = ? WHERE id_profissional = ?");
    return $stmt->execute([$hash, $id]);
}

function atualizarPerfilProfissional($id, $nome, $especialidade, $email, $almoco_inicio = null, $almoco_fim = null) {
    global $pdo;

    // AUTO-MIGRAÇÃO: Adiciona colunas de almoço se não existirem no profissional
    try {
        $pdo->query("SELECT almoco_inicio FROM profissional LIMIT 1");
    } catch (Exception $e) {
        try {
            $pdo->exec("ALTER TABLE profissional ADD COLUMN almoco_inicio TIME DEFAULT NULL, ADD COLUMN almoco_fim TIME DEFAULT NULL");
        } catch (Exception $ex) {}
    }

    $stmt = $pdo->prepare("
        UPDATE profissional
        SET nome = ?, especialidade = ?, email = ?, almoco_inicio = ?, almoco_fim = ?
        WHERE id_profissional = ?
    ");

    return $stmt->execute([$nome, $especialidade, $email, $almoco_inicio, $almoco_fim, $id]);
}


?>
