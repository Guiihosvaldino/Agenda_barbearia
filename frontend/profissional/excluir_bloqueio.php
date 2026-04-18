<?php
require_once("../../backend/conexao.php");
session_start();

// Use o ID da sessão para garantir que um barbeiro não apague a agenda de outro
if (isset($_SESSION['profissional_id']) && isset($_GET['data']) && isset($_GET['hora'])) {
    $id_prof = $_SESSION['profissional_id']; 
    $data = $_GET['data'];
    $hora_clicada = $_GET['hora'] . ":00";

    try {
        $sql = "DELETE FROM bloqueio_agenda 
                WHERE id_profissional = :prof 
                AND data = :data 
                AND :hora BETWEEN hora_inicio AND hora_fim";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':prof' => $id_prof,
            ':data' => $data,
            ':hora' => $hora_clicada
        ]);

        header("Location: profissional_agenda.php?data=$data&excluido=1");
        exit;
    } catch (PDOException $e) {
        die("Erro ao excluir bloqueio: " . $e->getMessage());
    }
} else {
    header("Location: profissional_agenda.php?erro=sessao");
}