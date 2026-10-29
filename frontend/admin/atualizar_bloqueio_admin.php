<?php
require_once("../../backend/conexao.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_profissional = $_POST['id_profissional'];
    $data = $_POST['data'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fim = $_POST['hora_fim'];
    $descricao = $_POST['descricao'];

    try {
        $sql = "UPDATE bloqueio_agenda 
                SET hora_fim = :fim, descricao = :desc 
                WHERE id_profissional = :prof AND data = :data AND hora_inicio = :ini";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fim' => $hora_fim,
            ':desc' => $descricao,
            ':prof' => $id_profissional,
            ':data' => $data,
            ':ini' => $hora_inicio
        ]);
        header("Location: agenda.php?data=$data&sucesso=1");
        exit;
    } catch (Exception $e) {
        die("Erro ao atualizar bloqueio: " . $e->getMessage());
    }
} else {
    header("Location: agenda.php");
    exit;
}
?>
