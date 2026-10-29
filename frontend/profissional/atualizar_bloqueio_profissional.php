<?php
require_once("../../backend/conexao.php");
session_start();

// Proteção de segurança: Garante que o profissional está logado
if (!isset($_SESSION['profissional_id'])) { 
    exit("Acesso negado"); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados vindos do formulário do modal
    $id_profissional = $_POST['id_profissional'];
    $data = $_POST['data'];
    $hora_inicio = $_POST['hora_inicio']; // Chave para identificar o registro original
    $hora_fim = $_POST['hora_fim'];
    $descricao = !empty($_POST['descricao']) ? $_POST['descricao'] : 'Bloqueio de Horário';

    try {
        // Validação simples: a hora fim não pode ser menor ou igual à hora de início
        if (strtotime($hora_fim) <= strtotime($hora_inicio)) {
            throw new Exception("A hora de término não pode ser anterior ou igual à hora de início.");
        }

        // Atualiza o bloqueio baseado no profissional, data e hora de início correspondentes
        $sql = "UPDATE bloqueio_agenda 
                SET hora_fim = :hora_fim, descricao = :descricao 
                WHERE id_profissional = :id_prof AND data = :data AND hora_inicio = :hora_inicio";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':hora_fim'    => $hora_fim,
            ':descricao'   => $descricao,
            ':id_prof'     => $id_profissional,
            ':data'        => $data,
            ':hora_inicio' => $hora_inicio
        ]);

        // Redireciona de volta para a sua tela correta (profissional_agenda.php)
        header("Location: profissional_agenda.php?data=$data&sucesso=1");
        exit();

    } catch (Exception $e) {
        // Em caso de erro (como hora inválida), volta informando o problema
        header("Location: profissional_agenda.php?data=$data&erro=bloqueio&msg=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Se tentarem acessar o arquivo direto pela URL, chuta de volta para a agenda
    header("Location: profesional_agenda.php");
    exit();
}