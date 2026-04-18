<?php
require_once("../../backend/conexao.php");
global $pdo;

if (isset($_GET['id'])) {
    $id_agenda = intval($_GET['id']);
    // Captura a data para voltar na mesma tela
    $data_retorno = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

    try {
        $sql = "DELETE FROM agenda WHERE id_agenda = :id";
        $stmt = $pdo->prepare($sql);
        $sucesso = $stmt->execute([':id' => $id_agenda]);

        if ($sucesso) {
            // Volta para a agenda na data que estava sendo visualizada
            header("Location: agenda.php?data=$data_retorno&status=excluido");
            exit();
        } 
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>