<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../../backend/admin.php");
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}
require_once("../../backend/servico.php"); // Crie funções para servico: listar, adicionar, editar, excluir

// Mensagens
$msgSucesso = "";
$msgErro = "";

// -----------------------------
// SALVAR NOVO SERVIÇO
// -----------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] == "novo") {

    $nome    = $_POST['nome'];
    $duracao = $_POST['duracao'];
    $valor   = $_POST['valor'];
    $cor     = $_POST['cor'] ?? '#660a18';

    if ($nome && $duracao && $valor) {

        if (adicionarServico($nome, $duracao, $valor, $cor)) {
            $msgSucesso = "Serviço cadastrado com sucesso!";
        } else {
            $msgErro = "Erro ao cadastrar serviço!";
        }

    } else {
        $msgErro = "Preencha todos os campos obrigatórios.";
    }
}


// -----------------------------
// EDITAR SERVIÇO
// -----------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] == "editar") {

    $id      = $_POST['id'];
    $nome    = $_POST['nome'];
    $duracao = $_POST['duracao'];
    $valor   = $_POST['valor'];
    $cor     = $_POST['cor'] ?? '#660a18';

    if (editarServico($id, $nome, $duracao, $valor, $cor)) {
        $msgSucesso = "Serviço atualizado com sucesso!";
    } else {
        $msgErro = "Erro ao atualizar serviço!";
    }
}


// -----------------------------
// EXCLUIR SERVIÇO
// -----------------------------
if (isset($_GET['excluir'])) {

    $id = $_GET['excluir'];

    if (excluirServico($id)) {
        $msgSucesso = "Serviço removido com sucesso!";
    } else {
        $msgErro = "Erro ao excluir serviço!";
    }
}


// Lista de serviços
$servicos = listarServicosAdmin();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Serviços - Admin 💈</title>

<style>
/* ——— MESMA IDENTIDADE VISUAL ——— */

body {
    margin: 0;
    background-image: url('../imagem/FUNDO.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    font-family: 'Segoe UI', Arial, sans-serif;
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
    background: #ffffff;
    max-width: 850px;
    margin: 40px auto;
    padding: 30px 35px;
    border-radius: 16px;
    color: #222;
    box-shadow: 0 8px 22px rgba(0,0,0,0.25);
}

h2 {
    text-align: center;
    color: #660a18;
    margin-bottom: 30px;
    font-size: 2em;
}

form {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-bottom: 35px;
}

input {
    padding: 12px;
    font-size: 1em;
    border-radius: 8px;
    border: 1px solid #999;
}

button {
    padding: 12px;
    background: linear-gradient(135deg, #660a18, #8a1224);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 1.1em;
    cursor: pointer;
    font-weight: bold;
    transition: 0.2s;
}

button:hover {
    transform: scale(1.03);
}

/* Mensagens */
.msg-sucesso {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-left: 5px solid #28a745;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: bold;
}

.msg-erro {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-left: 5px solid #dc3545;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: bold;
}

/* Tabela */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th {
    background: #660a18;
    color: #fff;
    padding: 12px;
}

td {
    padding: 12px;
}

tr:nth-child(even) { background: #f6f6f6; }
tr:nth-child(odd) { background: #ffffff; }

.btn-edit {
    background: #ffc107;
    color: #222;
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
}

.btn-delete {
    background: #dc3545;
    color: #fff;
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
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
    <link rel="stylesheet" href="../css/menu-lateral.css">
</head>

<body>

<?php require __DIR__ . '/menu_lateral.php'; ?>

<div class="container">
    <img src="../../frontend/imagem/logo1.png" class="logo">
    <h2>Cadastro de Serviços</h2>

    <?php if ($msgSucesso): ?>
        <div class="msg-sucesso"><?= $msgSucesso ?></div>
    <?php endif; ?>

    <?php if ($msgErro): ?>
        <div class="msg-erro"><?= $msgErro ?></div>
    <?php endif; ?>


    <!-- FORM NOVO SERVIÇO -->
    <form method="POST">
        <input type="hidden" name="acao" value="novo">

        <input type="text" name="nome" placeholder="Nome do Serviço" required>
        <input type="number" name="duracao" placeholder="Duração (minutos)" required>
        <input type="text" name="valor" placeholder="Valor (R$)" required>
        
        <div style="display: flex; align-items: center; gap: 15px; background: #f8f9fa; padding: 10px 15px; border-radius: 8px; border: 1px solid #ccc;">
            <label style="font-weight: bold; color: #660a18; font-size: 0.95em;">Cor de Destaque na Agenda:</label>
            <input type="color" name="cor" value="#660a18" style="width: 60px; height: 35px; padding: 0; border: none; border-radius: 4px; cursor: pointer;">
        </div>

        <button type="submit">Cadastrar Serviço</button>
    </form>


    <h3>📋 Serviços Cadastrados</h3>

    <table>
        <tr>
            <th>Nome</th>
            <th>Duração (min)</th>
            <th>Valor (R$)</th>
            <th>Ações</th>
        </tr>

        <?php foreach ($servicos as $s): ?>
        <tr>
            <td>
                <span style="display: inline-block; width: 14px; height: 14px; border-radius: 50%; background: <?= !empty($s['cor']) ? $s['cor'] : '#660a18' ?>; margin-right: 8px; vertical-align: middle; box-shadow: 0 1px 3px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.6);"></span>
                <strong><?= htmlspecialchars($s['nome']) ?></strong>
            </td>
            <td><?= htmlspecialchars($s['duracao']) ?></td>
            <td><?= number_format($s['valor'],2,',','.') ?></td>

            <td>
                <a class="btn-edit" href="admin_servicos.php?editar=<?= $s['id_servico'] ?>">Editar</a>
                <a class="btn-delete" href="admin_servicos.php?excluir=<?= $s['id_servico'] ?>"
                   onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
                   Excluir
                </a>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

<?php 
// SE CLICOU EM EDITAR — MOSTRA O FORMULÁRIO
if (isset($_GET['editar'])):

    $idEditar = $_GET['editar'];
    $serv = buscarServicoPorId($idEditar);

    if ($serv):
?>

<br><br>

<h3>✏ Editar Serviço</h3>

<form method="POST">
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="id" value="<?= $serv['id_servico'] ?>">

    <input type="text" name="nome" 
           value="<?= $serv['nome'] ?>" 
           placeholder="Nome do Serviço" required>

    <input type="number" name="duracao" 
           value="<?= $serv['duracao'] ?>" 
           placeholder="Duração (minutos)" required>

    <input type="text" name="valor" 
           value="<?= $serv['valor'] ?>" 
           placeholder="Valor (R$)" required>

    <div style="display: flex; align-items: center; gap: 15px; background: #f8f9fa; padding: 10px 15px; border-radius: 8px; border: 1px solid #ccc;">
        <label style="font-weight: bold; color: #660a18; font-size: 0.95em;">Cor de Destaque na Agenda:</label>
        <input type="color" name="cor" value="<?= htmlspecialchars($serv['cor'] ?? '#660a18') ?>" style="width: 60px; height: 35px; padding: 0; border: none; border-radius: 4px; cursor: pointer;">
    </div>

    <button type="submit">Salvar Alterações</button>
</form>

<?php 
    endif;
endif;
?>

</div>

</body>
</html>
