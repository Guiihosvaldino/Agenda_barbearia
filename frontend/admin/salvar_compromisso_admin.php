<?php
require_once("../../backend/conexao.php");
require_once("../../backend/cliente.php"); 
session_start();

// 1. Verificação de Segurança para o Administrador
if (!isset($_SESSION['admin_id'])) { 
    exit("Acesso negado. Apenas administradores podem realizar esta ação."); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pegamos o profissional selecionado no modal (no admin, pode ser qualquer um)
    $id_profissional = $_POST['id_profissional'];
    $data = $_POST['data'];
    $hora_inicio = $_POST['hora_inicio'];
    $tipo = $_POST['tipo_final']; // 'agendamento' ou 'bloqueio'

    try {
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
        }

        $pdo->commit();
        // Redireciona de volta para a agenda administrativa
        header("Location: agenda.php?data=$data&sucesso=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: agenda.php?data=$data&erro=1&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: agenda.php");
    exit;
}