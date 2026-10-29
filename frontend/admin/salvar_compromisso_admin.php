<?php
require_once("../../backend/conexao.php");
require_once("../../backend/cliente.php"); 
require_once("../../backend/pacote.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pegamos o profissional selecionado no modal (no admin, pode ser qualquer um)
    $id_profissional = $_POST['id_profissional'];
    $data = $_POST['data'];
    $hora_inicio = $_POST['hora_inicio'];
    $tipo = $_POST['tipo_final']; // 'agendamento' ou 'bloqueio'

    try {
        if ($tipo !== 'bloqueio' && !empty($_POST['abater_corte'])) {
            garantirTabelaUsosPacote();
        }
        $pdo->beginTransaction();

        if ($tipo === 'bloqueio') {
            // --- LÓGICA DE SALVAR BLOQUEIO ---
            $hora_fim = $_POST['hora_fim'];
            $descricao = !empty($_POST['descricao_bloqueio']) ? $_POST['descricao_bloqueio'] : 'Bloqueio Administrativo';

            if (strtotime($hora_fim) <= strtotime($hora_inicio)) {
                throw new Exception("A hora de término deve ser após o início.");
            }

            // Inserção na tabela de bloqueios
            $sql = "INSERT INTO bloqueio_agenda (id_profissional, data, hora_inicio, hora_fim, descricao) 
                    VALUES (:prof, :data, :ini, :fim, :desc)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':prof' => $id_profissional,
                ':data' => $data,
                ':ini'  => $hora_inicio,
                ':fim'  => $hora_fim,
                ':desc' => $descricao
            ]);

        } else {
            // --- LÓGICA DE SALVAR AGENDAMENTO ---
            $id_servico = $_POST['id_servico'];
            $input_cliente = $_POST['nome_cliente_completo'];

            if (empty($input_cliente) || empty($id_servico)) {
                throw new Exception("Selecione o cliente e o serviço.");
            }

            // Separa Nome e Celular vindos do datalist (Ex: João Silva - 34999...)
            $partes = explode(' - ', $input_cliente);
            $nome_cliente = trim($partes[0]);
            $celular_cliente = isset($partes[1]) ? trim($partes[1]) : '';

            // 1. Busca se o cliente já existe, senão cadastra
            $sql_cliente = "SELECT id_cliente FROM cliente WHERE nome = :nome LIMIT 1";
            $stmt_c = $pdo->prepare($sql_cliente);
            $stmt_c->execute([':nome' => $nome_cliente]);
            $cliente_db = $stmt_c->fetch(PDO::FETCH_ASSOC);

            if ($cliente_db) {
                $id_cliente = $cliente_db['id_cliente'];
            } else {
                $sql_ins_cli = "INSERT INTO cliente (nome, celular) VALUES (:nome, :celular)";
                $stmt_ins = $pdo->prepare($sql_ins_cli);
                $stmt_ins->execute([':nome' => $nome_cliente, ':celular' => $celular_cliente]);
                $id_cliente = $pdo->lastInsertId();
            }

            // 2. Salva na Agenda (Administrador já salva como CONFIRMADO por padrão)
            $sql_agenda = "INSERT INTO agenda (id_cliente, id_profissional, id_servico, data, hora, status) 
                           VALUES (:cli, :prof, :serv, :data, :hora, 'CONFIRMADO')";
            $stmt_agenda = $pdo->prepare($sql_agenda);
            $stmt_agenda->execute([
                ':cli'  => $id_cliente,
                ':prof' => $id_profissional,
                ':serv' => $id_servico,
                ':data' => $data,
                ':hora' => $hora_inicio
            ]);

            // Se vinculou com pacote e marcou para abater corte
            if (!empty($_POST['abater_corte']) && !empty($_POST['id_pacote_vinculado'])) {
                $id_agenda = (int)$pdo->lastInsertId();
                if (!registrarUsoPacoteNoAgendamento(
                    (int)$_POST['id_pacote_vinculado'],
                    $id_agenda,
                    (int)$id_cliente,
                    $data,
                    $hora_inicio
                )) {
                    throw new Exception("O pacote selecionado não está aberto ou não possui atendimentos disponíveis.");
                }
            } elseif (!empty($_POST['abater_corte'])) {
                throw new Exception("Selecione um pacote válido para abater o atendimento.");
            }
        }

        $pdo->commit();
        // Redireciona de volta para a agenda administrativa
        header("Location: agenda.php?data=$data&sucesso=1");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: agenda.php?data=$data&erro=1&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: agenda.php");
    exit;
}