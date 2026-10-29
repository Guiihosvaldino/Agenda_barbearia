<?php
require_once("conexao.php");

function listarClientesParaPacote() {
    global $pdo;
    $stmt = $pdo->query("SELECT id_cliente, nome, celular FROM cliente ORDER BY nome ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarPacotes($status = 'ABERTO', $mes = null, $ano = null) {
    global $pdo;
    $sql = "SELECT p.*, c.nome AS cliente_nome, c.celular AS cliente_celular
            FROM pacote_cliente p
            INNER JOIN cliente c ON c.id_cliente = p.id_cliente
            WHERE p.status = ?";
    $params = [$status];

    if ($mes && $ano) {
        $sql .= " AND MONTH(p.data_pagamento) = ? AND YEAR(p.data_pagamento) = ?";
        $params[] = $mes;
        $params[] = $ano;
    } elseif ($ano) {
        $sql .= " AND YEAR(p.data_pagamento) = ?";
        $params[] = $ano;
    }

    $sql .= " ORDER BY p.data_pagamento DESC, p.id_pacote DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarPacotePorId($id_pacote) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.nome AS cliente_nome, c.celular AS cliente_celular
                           FROM pacote_cliente p
                           INNER JOIN cliente c ON c.id_cliente = p.id_cliente
                           WHERE p.id_pacote = ?");
    $stmt->execute([$id_pacote]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function criarPacote($id_cliente, $descricao, $valor, $qtd_total, $qtd_usada, $data_pagamento, $status = 'ABERTO', $observacao = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO pacote_cliente 
        (id_cliente, descricao, valor, qtd_total, qtd_usada, data_pagamento, status, observacao)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $id_cliente,
        $descricao,
        $valor,
        $qtd_total,
        $qtd_usada,
        $data_pagamento,
        $status,
        $observacao
    ]);
}

function atualizarPacote($id_pacote, $id_cliente, $descricao, $valor, $qtd_total, $qtd_usada, $data_pagamento, $status, $observacao = '') {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE pacote_cliente SET 
        id_cliente = ?, 
        descricao = ?, 
        valor = ?, 
        qtd_total = ?, 
        qtd_usada = ?, 
        data_pagamento = ?, 
        status = ?, 
        observacao = ?
        WHERE id_pacote = ?");
    return $stmt->execute([
        $id_cliente,
        $descricao,
        $valor,
        $qtd_total,
        $qtd_usada,
        $data_pagamento,
        $status,
        $observacao,
        $id_pacote
    ]);
}

function registrarUsoPacote($id_pacote) {
    global $pdo;
    $pacote = buscarPacotePorId($id_pacote);
    if (!$pacote) return false;

    $novaQtd = $pacote['qtd_usada'] + 1;
    $status = $pacote['status'];
    if ($novaQtd >= $pacote['qtd_total']) {
        $status = 'FECHADO';
    }

    $stmt = $pdo->prepare("UPDATE pacote_cliente SET qtd_usada = ?, status = ? WHERE id_pacote = ?");
    return $stmt->execute([$novaQtd, $status, $id_pacote]);
}

function garantirTabelaUsosPacote() {
    global $pdo;
    $pdo->exec("CREATE TABLE IF NOT EXISTS pacote_uso_agenda (
        id_uso INT AUTO_INCREMENT PRIMARY KEY,
        id_agenda INT NOT NULL,
        id_cliente INT NOT NULL,
        id_pacote INT NOT NULL,
        data DATE NOT NULL,
        hora TIME NOT NULL,
        UNIQUE KEY uk_pacote_uso_agenda (id_agenda),
        UNIQUE KEY uk_pacote_uso_cliente_horario (id_cliente, data, hora),
        INDEX idx_pacote_uso_pacote (id_pacote)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
}

function registrarUsoPacoteNoAgendamento($id_pacote, $id_agenda, $id_cliente, $data, $hora) {
    global $pdo;

    $stmtUso = $pdo->prepare("SELECT id_pacote, id_cliente FROM pacote_uso_agenda WHERE id_agenda = ? FOR UPDATE");
    $stmtUso->execute([$id_agenda]);
    $usoExistente = $stmtUso->fetch(PDO::FETCH_ASSOC);

    if ($usoExistente) {
        if ((int)$usoExistente['id_pacote'] !== (int)$id_pacote || (int)$usoExistente['id_cliente'] !== (int)$id_cliente) {
            throw new RuntimeException("Este agendamento já teve um atendimento abatido de outro pacote.");
        }

        $stmt = $pdo->prepare("UPDATE pacote_uso_agenda SET data = ?, hora = ? WHERE id_agenda = ?");
        try {
            $stmt->execute([$data, $hora, $id_agenda]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new RuntimeException("Este cliente já teve um atendimento abatido neste dia e horário.");
            }
            throw $e;
        }

        return true;
    }

    $stmtPacote = $pdo->prepare("SELECT qtd_usada, qtd_total, status FROM pacote_cliente WHERE id_pacote = ? AND id_cliente = ? FOR UPDATE");
    $stmtPacote->execute([$id_pacote, $id_cliente]);
    $pacote = $stmtPacote->fetch(PDO::FETCH_ASSOC);
    if (!$pacote || $pacote['status'] !== 'ABERTO' || (int)$pacote['qtd_usada'] >= (int)$pacote['qtd_total']) {
        return false;
    }

    $stmtConflito = $pdo->prepare("SELECT id_agenda FROM pacote_uso_agenda WHERE id_cliente = ? AND data = ? AND hora = ? LIMIT 1");
    $stmtConflito->execute([$id_cliente, $data, $hora]);
    if ($stmtConflito->fetchColumn()) {
        throw new RuntimeException("Este cliente já teve um atendimento abatido neste dia e horário.");
    }

    $stmt = $pdo->prepare("INSERT INTO pacote_uso_agenda (id_agenda, id_cliente, id_pacote, data, hora) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$id_agenda, $id_cliente, $id_pacote, $data, $hora]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            throw new RuntimeException("Este cliente já teve um atendimento abatido neste dia e horário.");
        }
        throw $e;
    }

    if (!registrarUsoPacote($id_pacote)) {
        throw new RuntimeException("Não foi possível registrar o uso do pacote.");
    }

    return true;
}

function sincronizarUsoPacoteComAgendamento($id_agenda, $id_cliente, $data, $hora) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id_pacote FROM pacote_uso_agenda WHERE id_agenda = ?");
    $stmt->execute([$id_agenda]);
    $id_pacote = $stmt->fetchColumn();
    if (!$id_pacote) {
        return false;
    }

    return registrarUsoPacoteNoAgendamento($id_pacote, $id_agenda, $id_cliente, $data, $hora);
}

function excluirPacote($id_pacote) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM pacote_cliente WHERE id_pacote = ?");
    return $stmt->execute([$id_pacote]);
}

function calcularTotalPacotesMes($ano, $mes) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT SUM(valor) AS total
                           FROM pacote_cliente
                           WHERE YEAR(data_pagamento) = ? AND MONTH(data_pagamento) = ?");
    $stmt->execute([$ano, $mes]);
    return floatval($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
}
