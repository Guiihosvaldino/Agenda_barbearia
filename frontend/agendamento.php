<?php
require_once("../backend/profissional.php");
require_once("../backend/agenda.php");

$idCliente = $_GET['id'] ?? null;
if (!$idCliente) {
    header("Location: index.php");
    exit;
}

$profissionais = listarProfissionais();
$servicos = listarServicos();

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idProfissional = $_POST['profissional'];
    $idServico = $_POST['servico'];
    $data = $_POST['data'];
    $hora = $_POST['hora'];

    if (salvarAgendamento($idCliente, $idProfissional, $idServico, $data, $hora)) {
        header("Location: confirmacao.php");
        exit;
    } else {
        $msg = "⚠️ Este horário já está ocupado. Escolha outro.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Agendamento - Barbearia</title>
</head>
<body>
    <h2>Agendar Horário</h2>

    <?php if ($msg): ?>
        <p style="color:red;"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Profissional:</label><br>
        <select name="profissional" required>
            <option value="">Selecione</option>
            <?php foreach ($profissionais as $p): ?>
                <option value="<?= $p['id_profissional'] ?>"><?= $p['nome'] ?> - <?= $p['especialidade'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Serviço:</label><br>
        <select name="servico" required>
            <option value="">Selecione</option>
            <?php foreach ($servicos as $s): ?>
                <option value="<?= $s['id_servico'] ?>"><?= $s['nome'] ?> (<?= $s['duracao'] ?>min - R$<?= $s['valor'] ?>)</option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Data:</label><br>
        <input type="date" name="data" required><br><br>

        <label>Hora:</label><br>
        <input type="time" name="hora" required><br><br>

        <button type="submit">Agendar</button>
    </form>
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #fff 100%);
        font-family: 'Segoe UI', Arial, sans-serif;
        color: #fff;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        }
        h2 {
            color: #333;
            margin-top: 30px;
            text-align: center;
        }
        form {
            background: #fff;
            max-width: 400px;
            margin: 30px auto;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        label {
            font-weight: bold;
            color: #444;
        }
        select, input[type="date"], input[type="time"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button[type="submit"] {
            background: #2d7a2d;
            color: #fff;
            border: none;
            padding: 10px 0;
            width: 100%;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: #256225;
        }
        p[style*="color:red"] {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</body>
</html>
