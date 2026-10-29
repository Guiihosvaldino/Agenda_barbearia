<?php
// backend/process_overdue.php
// This script checks for appointments that have passed their scheduled end time
// and automatically registers the corresponding amount in the financial records
// for both admin and the professional.

// Assumes a PDO connection $pdo is already available (include this file after
// requiring conexao.php).

if (!isset($pdo)) {
    // Fallback: try to include connection if not present
    require_once __DIR__ . '/conexao.php';
}

// Fetch appointments that are not cancelled or already paid.
$sql = "SELECT a.id_agenda, a.hora, a.duracao, a.id_profissional, a.id_servico, a.status
        FROM agenda a
        WHERE a.status NOT IN ('cancelado', 'pago')";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$now = new DateTime();

foreach ($agendamentos as $ag) {
    // Calculate the end time of the appointment
    $horaInicio = DateTime::createFromFormat('H:i:s', $ag['hora']);
    if (!$horaInicio) {
        // If stored as H:i (without seconds)
        $horaInicio = DateTime::createFromFormat('H:i', $ag['hora']);
    }
    $duracao = (int)($ag['duracao'] ?? 30); // minutes
    $horaFim = clone $horaInicio;
    $horaFim->modify("+$duracao minutes");

    // If the appointment already ended and the current time is past the end
    if ($now > $horaFim) {
        // Retrieve the service price (assumes a 'preco' column in servicos table)
        $stmtPreco = $pdo->prepare("SELECT preco, nome FROM servicos WHERE id_servico = :id");
        $stmtPreco->execute([':id' => $ag['id_servico']]);
        $servico = $stmtPreco->fetch(PDO::FETCH_ASSOC);
        $valor = $servico ? $servico['preco'] : 0;

        // Register in the admin financial table
        $sqlFinAdmin = "INSERT INTO financeiro_admin (descricao, valor, data, tipo, id_agenda)
                        VALUES (:desc, :valor, NOW(), 'receita', :agenda_id)";
        $stmtFinAdmin = $pdo->prepare($sqlFinAdmin);
        $stmtFinAdmin->execute([
            ':desc' => "Serviço: {$servico['nome']} (Agenda #{$ag['id_agenda']})",
            ':valor' => $valor,
            ':agenda_id' => $ag['id_agenda']
        ]);

        // Register in the professional financial table
        $sqlFinProf = "INSERT INTO financeiro_profissional (id_profissional, descricao, valor, data, tipo, id_agenda)
                        VALUES (:prof, :desc, :valor, NOW(), 'receita', :agenda_id)";
        $stmtFinProf = $pdo->prepare($sqlFinProf);
        $stmtFinProf->execute([
            ':prof' => $ag['id_profissional'],
            ':desc' => "Serviço: {$servico['nome']} (Agenda #{$ag['id_agenda']})",
            ':valor' => $valor,
            ':agenda_id' => $ag['id_agenda']
        ]);

        // Mark the appointment as paid to avoid duplicate entries
        $sqlUpdate = "UPDATE agenda SET status = 'pago' WHERE id_agenda = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([':id' => $ag['id_agenda']]);
    }
}
?>
