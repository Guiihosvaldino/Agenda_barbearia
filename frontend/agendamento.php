<?php
require_once("../backend/profissional.php");
require_once("../backend/agenda.php");

// Verifica se cliente está logado
session_start();

if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../index.php?erro=login");
    exit;
}

$idCliente = $_SESSION['cliente_id'];

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
    <title>Agendar Horário - Barbearia</title>

    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 40px;
        }

        .container {
            background: #ffffff;
            width: 420px;
            padding: 28px 32px;
            border-radius: 16px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.25);
        }

        h2 {
            text-align: center;
            margin-top: 0;
            color: #1e3c72;
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: 700;
        }

        label {
            font-weight: bold;
            color: #222;
            margin-bottom: 5px;
            display: block;
        }

        select, input[type="date"], input[type="time"] {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #888;
            font-size: 1em;
            margin-bottom: 18px;
            outline: none;
            transition: 0.2s;
        }

        select:focus, input:focus {
            border-color: #1e3c72;
            background: #eef3ff;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            transform: scale(1.03);
            background: linear-gradient(135deg, #2a5298, #1e3c72);
        }

        .erro {
            background: #ffdddd;
            border-left: 5px solid red;
            padding: 10px;
            margin-bottom: 18px;
            border-radius: 6px;
            color: #a30000;
            font-weight: bold;
            text-align: center;
        }

        .voltar {
            display: block;
            text-align: center;
            padding: 10px;
            background: #1e3c72;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            margin-top: 18px;
            transition: 0.2s;
        }

        .voltar:hover {
            background: #16315c;
            transform: scale(1.02);
        }
    </style>

</head>
<body>

<div class="container">

    <h2>Agendar Horário</h2>

    <?php if ($msg): ?>
        <div class="erro"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">

        <label>Profissional:</label>
        <select name="profissional" required>
            <option value="">Selecione</option>
            <?php foreach ($profissionais as $p): ?>
                <option value="<?= $p['id_profissional'] ?>">
                    <?= $p['nome'] ?> - <?= $p['especialidade'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Serviço:</label>
        <select name="servico" required>
            <option value="">Selecione</option>
            <?php foreach ($servicos as $s): ?>
                <option value="<?= $s['id_servico'] ?>">
                    <?= $s['nome'] ?> (<?= $s['duracao'] ?>min - R$<?= $s['valor'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Data:</label>
        <input type="date" name="data" required>

        <label>Hora:</label>
        <input type="time" name="hora" required>

        <button type="submit">Agendar</button>
    </form>

    <a href="index.php" class="voltar">⬅ Voltar</a>

</div>

</body>
</html>
