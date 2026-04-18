<?php
require_once("../../backend/conexao.php");
session_start();

// 1. Verificação de Segurança: O usuário está logado como admin?
// Ajuste 'admin_id' conforme o nome da variável de sessão que você usa no login do admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// 2. Verificação de Parâmetros: Temos tudo que precisamos para deletar?
if (isset($_GET['prof']) && isset($_GET['data']) && isset($_GET['hora'])) {
    
    $id_prof = $_GET['prof'];
    $data = $_GET['data'];
    // Garante que a hora esteja no formato HH:MM:00 para o banco de dados
    $hora_clicada = date('H:i:00', strtotime($_GET['hora']));

    try {
        /* Lógica do DELETE:
           Como um bloqueio pode durar várias horas (ex: 08:00 às 10:00),
           precisamos deletar o registro onde a hora clicada na grade 
           esteja DENTRO do intervalo de início e fim.
        */
        $sql = "DELETE FROM bloqueio_agenda 
                WHERE id_profissional = :prof 
                AND data = :data 
                AND :hora BETWEEN hora_inicio AND (hora_fim - INTERVAL 1 SECOND)";
        
        /* Nota: Subtraímos 1 segundo do hora_fim no BETWEEN para evitar que, 
           se um bloqueio termina às 10:00 e outro começa às 10:00, 
           o sistema não apague o bloqueio errado ao clicar exatamente nas 10:00.
        */

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':prof' => $id_prof,
            ':data' => $data,
            ':hora' => $hora_clicada
        ]);

        // Redireciona de volta para a agenda master com sucesso
        header("Location: agenda.php?data=$data&msg=bloqueio_removido");
        exit;

    } catch (PDOException $e) {
        // Em caso de erro de banco de dados
        die("Erro crítico ao excluir bloqueio: " . $e->getMessage());
    }

} else {
    // Se os dados necessários não foram enviados
    header("Location: agenda.php?erro=dados_insuficientes");
    exit;
}