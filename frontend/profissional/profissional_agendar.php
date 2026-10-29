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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Novo Agendamento</title>
    <link rel="stylesheet" href="../css/menu-lateral.css">
</head>
<body>

<!-- MENU SUPERIOR -->
<?php require __DIR__ . '/menu_lateral.php'; ?>

<div class="container">
        <img src="../../frontend/imagem/logo1.png" class="logo">
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
        <input type="date" name="data" id="data" required>
        
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
        background-image: url('../imagem/FUNDO.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 0;
        color: #fff;
        }
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 0;
    }

     .menu {
    background: #380309;
    padding: 14px 25px;
    display: flex;
    gap: 25px;
    font-size: 1.1em;
    position: sticky;
    top: 0;
    z-index: 50;
}

.menu a {
    color: #fff;
    text-decoration: none;
    padding: 6px 12px;
    font-weight: bold;
    border-radius: 6px;
}

.menu a:hover {
    background: #660a18;
}


.container {
    position: relative;
    z-index: 1;
    background: white;
    max-width: 600px;
    margin: 40px auto;
    padding: 30px;
    color: #222;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

h2 {
    text-align: center;
    color: #660a18;
    margin-bottom: 25px;
}

/* MENSAGENS */
.msg {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    font-weight: bold;
    margin-bottom: 15px;
}

.alert {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    font-weight: bold;
}

/* FORM BUSCA */
.form-busca {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
}
.form-busca input {
    flex: 1;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.form-busca button {
    padding: 10px 16px;
    background: #660a18;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
}
.form-busca button:hover {
    background: #8a1224;
}

/* FORM AGENDAR */
.form-agendar label {
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
}

.form-agendar input,
.form-agendar select {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
}

/* BOTÃO PRINCIPAL */
.form-agendar button {
    width: 100%;
    background: #660a18;
    color: white;
    padding: 14px;
    border-radius: 8px;
    font-size: 1.1em;
    font-weight: bold;
    border: none;
    cursor: pointer;
}
.form-agendar button:hover {
    background: #8a1224;
}
       .logo {
        width: 350px;   /* quase o tamanho do formulário */
        margin-bottom: -70px;
        margin-left: auto;
        margin-right: auto;
        display: block;
        }

/* ========== RESPONSIVIDADE (CELULARES) ========== */
@media (max-width: 768px) {
    .container {
        width: 90% !important;
        max-width: none !important;
        margin: 20px auto !important;
        padding: 20px !important;
    }
    .menu {
        flex-direction: column !important;
        align-items: center !important;
        gap: 10px !important;
        padding: 15px !important;
    }
    .menu a {
        width: 100% !important;
        text-align: center !important;
        box-sizing: border-box !important;
    }
    .logo {
        width: 100% !important;
        max-width: 250px !important;
        margin-bottom: -40px !important;
    }
    table {
        display: block !important;
        overflow-x: auto !important;
        white-space: nowrap !important;
    }
}
</style>


</style>

<script>
document.getElementById("data").addEventListener("change", function() {

    fetch("verificar_funcionamento.php?data=" + this.value)
        .then(response => response.json())
        .then(dados => {

            if (!dados.ativo) {
                alert("❌ Barbearia fechada nesse dia!");
                this.value = "";
            }

        });

});
</script>

</body>
</html>

</body>
</html>
