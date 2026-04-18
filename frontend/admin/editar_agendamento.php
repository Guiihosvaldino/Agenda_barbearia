<?php
require_once("../../backend/conexao.php");
global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_agenda = isset($_POST['id_agenda']) ? intval($_POST['id_agenda']) : 0;
    $data = $_POST['data'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $id_servico = isset($_POST['id_servico']) ? intval($_POST['id_servico']) : 0;

    // --- VALIDAÇÃO DE CAMPOS VAZIOS ---
    if (empty($data) || empty($hora) || $id_servico <= 0) {
        echo "<script>
                alert('Erro: Todos os campos (Data, Hora e Serviço) devem ser preenchidos!');
                window.history.back();
              </script>";
        exit;
    }

    if ($id_agenda > 0) {
        try {
            $sql = "UPDATE agenda SET 
                    data = :data, 
                    hora = :hora, 
                    id_servico = :id_s 
                    WHERE id_agenda = :id_a";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':data' => $data,
                ':hora' => $hora,
                ':id_s'  => $id_servico,
                ':id_a'  => $id_agenda
            ]);

            header("Location: agenda.php?data=$data&status=editado");
            exit();
        } catch (PDOException $e) {
            die("Erro crítico no banco de dados. Verifique se o serviço selecionado existe.");
        }
    }
}