<?php
require_once("../../backend/conexao.php");
session_start();


if (!isset($_SESSION['profissional_id'])) { 
    exit("Acesso negado"); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_profissional = $_POST['id_profissional'];
    $data = $_POST['data'];
    $hora_inicio = $_POST['hora_inicio'];
    $tipo = $_POST['tipo_final']; 

    try {
        $pdo->beginTransaction();

        if ($tipo === 'bloqueio') {
            // --- LÓGICA DE SALVAR BLOQUEIO ---
            $hora_fim = $_POST['hora_fim'];
            $descricao = !empty($_POST['descricao_bloqueio']) ? $_POST['descricao_bloqueio'] : 'Bloqueio de Horário';

            if (strtotime($hora_fim) <= strtotime($hora_inicio)) {
                throw new Exception("Hora fim inválida");
            }

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
                throw new Exception("Campos obrigatórios faltando");
            }

            // Separa Nome e Celular (ex: João - 11999...)
            $partes = explode(' - ', $input_cliente);
            $nome_cliente = trim($partes[0]);
            $celular_cliente = isset($partes[1]) ? trim($partes[1]) : '';

            // 1. Busca ou Cadastra Cliente
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

            // 2. Salva na Agenda (Status PENDENTE por padrão)
            $sql_agenda = "INSERT INTO agenda (id_cliente, id_profissional, id_servico, data, hora, status) 
                           VALUES (:cli, :prof, :serv, :data, :hora, 'PENDENTE')";
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
        // CORRIGIDO: De 'agenda_profissional.php' para 'profissional_agenda.php'
        header("Location: profissional_agenda.php?data=$data&sucesso=1");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // CORRIGIDO: Aqui também
        header("Location: profissional_agenda.php?data=$data&erro=salvar&msg=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // CORRIGIDO: E aqui na segurança de saída direta
    header("Location: profissional_agenda.php");
    exit();
}