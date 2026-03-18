<?php
session_start();
require_once("../../backend/conexao.php");
require_once("../../backend/profissional.php");

if (!isset($_SESSION['profissional_id'])) { header("Location: login.php"); exit; }

$id_agenda = $_GET['id'] ?? null;
$agendamento = buscarAgendamentoPorId($id_agenda);

// Buscamos todos os serviços para o barbeiro escolher no select
$servicos = listarServicos(); 

if (!$agendamento) { echo "Agendamento não encontrado."; exit; }

// Lógica de Salvamento
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novo_status = $_POST['status'];
    $nova_hora = $_POST['hora'];
    $novo_servico = $_POST['id_servico']; // Pegando o novo serviço do formulário
    
    // Atualizamos também o id_servico na tabela agenda
    $stmt = $pdo->prepare("UPDATE agenda SET status = ?, hora = ?, id_servico = ? WHERE id_agenda = ?");
    if ($stmt->execute([$novo_status, $nova_hora, $novo_servico, $id_agenda])) {
        header("Location: profissional_agenda.php?data=" . $agendamento['data']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Agendamento</title>
    <style>
        body { background: #1e3c72; font-family: sans-serif; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; color: #333; padding: 30px; border-radius: 12px; width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        h2 { color: #1e3c72; margin-top: 0; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        select, input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .btn-salvar { background: #28a745; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; margin-top: 20px; cursor: pointer; font-size: 1em; }
        .btn-cancelar { display: block; text-align: center; margin-top: 10px; color: #666; text-decoration: none; font-size: 0.9em; }
        .info-cliente { background: #f1f4f8; padding: 10px; border-radius: 6px; margin-bottom: 10px; border-left: 4px solid #1e3c72; }
    </style>
</head>
<body>

<div class="card">
    <h2>✏️ Editar Agendamento</h2>
    
    <div class="info-cliente">
        <strong>Cliente:</strong> <?= htmlspecialchars($agendamento['cliente']) ?>
    </div>

    <form method="POST">
        <label>Serviço:</label>
        <select name="id_servico" required>
            <?php foreach ($servicos as $s): ?>
                <option value="<?= $s['id_servico'] ?>" <?= ($s['id_servico'] == $agendamento['id_servico']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nome']) ?> - R$ <?= number_format($s['valor'], 2, ',', '.') ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Horário:</label>
        <input type="time" name="hora" value="<?= $agendamento['hora'] ?>" required>

        <label>Status:</label>
        <select name="status">
            <option value="PENDENTE" <?= $agendamento['status'] == 'PENDENTE' ? 'selected' : '' ?>>Pendente</option>
            <option value="CONFIRMADO" <?= $agendamento['status'] == 'CONFIRMADO' ? 'selected' : '' ?>>Confirmado</option>
            <option value="CANCELADO" <?= $agendamento['status'] == 'CANCELADO' ? 'selected' : '' ?>>Cancelado</option>
        </select>

        <button type="submit" class="btn-salvar">Salvar Alterações</button>
        <a href="profissional_agenda.php?data=<?= $agendamento['data'] ?>" class="btn-cancelar">Voltar sem salvar</a>
    </form>
</div>

</body>
</html>