<?php
require_once("../../backend/conexao.php");

// Importante: Verifique se sua conexão no conexao.php está na variável $conn ou $mysqli
// Se estiver usando PDO, o código muda levemente. Aqui fiz para MySQLi:

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebendo os dados do Modal
    $id_agenda = $_POST['id_agendamento']; // Este vem do campo oculto 'edit_id'
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $id_servico = $_POST['id_servico'];

    // Query ajustada para sua tabela 'agenda' e coluna 'id_agenda'
    $sql = "UPDATE agenda SET 
            data = ?, 
            hora = ?, 
            id_servico = ? 
            WHERE id_agenda = ?";

    $stmt = $conn->prepare($sql);
    
    // "ssii" significa: string (data), string (hora), integer (serviço), integer (id_agenda)
    $stmt->bind_param("ssii", $data, $hora, $id_servico, $id_agenda);

    if ($stmt->execute()) {
        // Redireciona de volta para a agenda na mesma data que você estava vendo
        header("Location: agenda.php?data=$data&status=editado");
        exit();
    } else {
        echo "Erro ao atualizar banco: " . $conn->error;
    }

    $stmt->close();
}
?>