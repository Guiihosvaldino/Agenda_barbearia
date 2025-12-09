<?php

require_once("../../backend/admin.php");
require_once("../../backend/cliente.php");
require_once("../../backend/profissional.php");
require_once("../../backend/agenda.php");


// 🔍 Busca cliente por nome
$clientesEncontrados = [];
if (isset($_GET['buscar']) && $_GET['buscar'] != "") {
    $nomeBusca = "%" . $_GET['buscar'] . "%";
    $clientesEncontrados = buscarClientePorNome($nomeBusca);
}

// Para agendar
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $idCliente      = $_POST['id_cliente'];
    $idProfissional = $_POST['profissional'];
    $idServico      = $_POST['servico'];
    $data           = $_POST['data'];
    $hora           = $_POST['hora'];

    if (salvarAgendamento($idCliente, $idProfissional, $idServico, $data, $hora)) {
        $msg = "✅ Agendamento realizado com sucesso!";
    } else {
        $msg = "⚠️ Horário já ocupado!";
    }
}

$profissionais = listarProfissionais();
$servicos = listarServicos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Agendamento</title>
</head>
<body>

<!-- MENU SUPERIOR -->
<div class="menu">
    <a href="agenda.php">📅 Agenda</a>
    <a href="financeiro.php">💰 Financeiro</a>
    <a href="admin_agendar.php">➕ Novo Agendamento</a>
    <a href="admin_profissionais.php">👤 Profissionais</a>
</div>

<div class="container">

<h2>➕ Agendar Horário </h2>

<?php if ($msg): ?>
    <p class="msg"><?= $msg ?></p>
<?php endif; ?>

<!-- FORM DE BUSCA DO CLIENTE -->
<form method="GET" class="form-busca">
    <label>Buscar cliente por nome:</label>
    <input type="text" name="buscar" placeholder="Digite o nome..." value="<?= $_GET['buscar'] ?? '' ?>">
    <button type="submit">Pesquisar</button>
</form>

<?php if ($clientesEncontrados): ?>

    <form method="POST" class="form-agendar">

        <label>Selecione o Cliente:</label>
        <select name="id_cliente" required>
            <?php foreach ($clientesEncontrados as $c): ?>
                <option value="<?= $c['id_cliente'] ?>">
                    <?= $c['nome'] ?> - <?= $c['celular'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Profissional:</label>
        <select name="profissional" required>
            <?php foreach ($profissionais as $p): ?>
                <option value="<?= $p['id_profissional'] ?>"><?= $p['nome'] ?> - <?= $p['especialidade'] ?></option>
            <?php endforeach; ?>
        </select>

        <label>Serviço:</label>
        <select name="servico" required>
            <?php foreach ($servicos as $s): ?>
                <option value="<?= $s['id_servico'] ?>">
                    <?= $s['nome'] ?> - R$<?= $s['valor'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Data:</label>
        <input type="date" name="data" required>

        <label>Hora:</label>
        <input type="time" name="hora" required>

        <button type="submit">Confirmar Agendamento</button>

    </form>

<?php elseif (isset($_GET['buscar'])): ?>

    <p class="alert">Nenhum cliente encontrado.</p>

<?php endif; ?>

</div>

<style>
body {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    font-family: Arial;
    color: #fff;
}

.menu {
    background: #0d2a52;
    padding: 12px 20px;
    display: flex;
    gap: 20px;
}

.menu a {
    color: #fff;
    text-decoration: none;
    font-weight: bold;
}

.container {
    background: #fff;
    color: #000;
    max-width: 600px;
    margin: 30px auto;
    padding: 25px;
    border-radius: 12px;
}

.form-busca input {
    padding: 8px;
    width: 70%;
}

.form-busca button {
    padding: 8px;
    background: #1e3c72;
    color: #fff;
}

.form-agendar select,
.form-agendar input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
}

button {
    width: 100%;
    background: #2d7a2d;
    color: #fff;
    padding: 10px;
    border-radius: 6px;
}

.alert { color: red; font-weight: bold; }
.msg { color: green; font-weight: bold; text-align:center; }

</style>

</body>
</html>
