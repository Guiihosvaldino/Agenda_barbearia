<?php
require_once("../../backend/conexao.php");
require_once("../../backend/pacote.php");
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
            garantirTabelaUsosPacote();
            $pdo->beginTransaction();

            $stmtCliente = $pdo->prepare("SELECT id_cliente FROM agenda WHERE id_agenda = ?");
            $stmtCliente->execute([$id_agenda]);
            $id_cliente = $stmtCliente->fetchColumn();
            if (!$id_cliente) {
                throw new Exception("Agendamento não encontrado.");
            }

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

            $usoSincronizado = sincronizarUsoPacoteComAgendamento(
                $id_agenda,
                (int)$id_cliente,
                $data,
                $hora
            );

            // Se marcou para abater corte do pacote
            if (!$usoSincronizado && !empty($_POST['abater_corte'])) {
                if (empty($_POST['id_pacote_vinculado'])) {
                    throw new Exception("Selecione um pacote válido para abater o atendimento.");
                }
                if (!registrarUsoPacoteNoAgendamento(
                    (int)$_POST['id_pacote_vinculado'],
                    $id_agenda,
                    (int)$id_cliente,
                    $data,
                    $hora
                )) {
                    throw new Exception("O pacote selecionado não está aberto ou não possui atendimentos disponíveis.");
                }
            }

            $pdo->commit();
            header("Location: agenda.php?data=$data&status=editado");
            exit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header("Location: agenda.php?data=" . urlencode($data) . "&erro=1&msg=" . urlencode($e->getMessage()));
            exit();
        }
    }
}