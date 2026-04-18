<?php
require_once("../../backend/conexao.php");
global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_p = $_POST['id_profissional'];
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $id_s = $_POST['id_servico'];
    $nome_input = $_POST['nome_cliente_completo'];

    // Separa o nome do celular (Guilherme - 3499...)
    $partes = explode(" - ", $nome_input);
    $nome_cliente = trim($partes[0]);

    try {
        // Busca o ID do cliente
        $stmt_c = $pdo->prepare("SELECT id_cliente FROM cliente WHERE nome = :nome LIMIT 1");
        $stmt_c->execute([':nome' => $nome_cliente]);
        $cliente = $stmt_c->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            $id_c = $cliente['id_cliente'];

            $sql = "INSERT INTO agenda (id_cliente, id_profissional, id_servico, data, hora, status) 
                    VALUES (:id_c, :id_p, :id_s, :data, :hora, 'PENDENTE')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_c' => $id_c,
                ':id_p' => $id_p,
                ':id_s' => $id_s,
                ':data' => $data,
                ':hora' => $hora
            ]);

            header("Location: agenda.php?data=$data&status=sucesso");
            exit();
        } else {
            echo "Cliente não encontrado!";
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}