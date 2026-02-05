<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../../backend/admin.php");
require_once("../../backend/profissional.php");

// Mensagens
$msgSucesso = "";
$msgErro = "";

// -----------------------------
// SALVAR NOVO PROFISSIONAL
// -----------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] == "novo") {

    $nome         = $_POST['nome'];
    $especialidade = $_POST['especialidade'];
    $email        = $_POST['email'];
    $senha        = $_POST['senha'];

    if ($nome && $especialidade && $email && $senha) {

        if (adicionarProfissional($nome, $especialidade, $email, $senha)) {
            $msgSucesso = "Profissional cadastrado com sucesso!";
        } else {
            $msgErro = "Erro ao cadastrar profissional! Este e-mail já existe.";
        }

    } else {
        $msgErro = "Preencha todos os campos obrigatórios.";
    }
}


// -----------------------------
// EDITAR PROFISSIONAL
// -----------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] == "editar") {

    $id      = $_POST['id'];
    $nome    = $_POST['nome'];
    $especialidade = $_POST['especialidade'];

    if (editarProfissional($id, $nome, $especialidade)) {
        $msgSucesso = "Profissional atualizado com sucesso!";
    } else {
        $msgErro = "Erro ao atualizar profissional!";
    }
}


// -----------------------------
// EXCLUIR PROFISSIONAL
// -----------------------------
if (isset($_GET['excluir'])) {

    $id = $_GET['excluir'];

    if (excluirProfissional($id)) {
        $msgSucesso = "Profissional removido com sucesso!";
    } else {
        $msgErro = "Erro ao excluir profissional!";
    }
}


// Lista de profissionais
$profissionais = listarProfissionaisAdmin();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Profissionais - Admin</title>

<style>
/* ——— MESMA IDENTIDADE VISUAL ——— */

body {
    margin: 0;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #fff;
}
.menu {
    background: #0d2a52;
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
    background: #1e3c72;
}

.container {
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
    color: #1e3c72;
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
    background: linear-gradient(135deg, #1e3c72, #2a5298);
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
    background: #1e3c72;
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

</style>
</head>

<body>

<div class="menu">
    <a href="agenda.php">📅 Agenda</a>
    <a href="financeiro.php">💰 Financeiro</a>
    <a href="admin_agendar.php">➕ Novo Agendamento</a>
    <a href="admin_profissionais.php">👤 Profissionais</a>
    <a href="admin_servicos.php">💈 Serviços</a>
    <a href="admin_funcionamento.php">⏰ Funcionamento</a>
    <a href="login_admin.php">🚪 Sair</a>
</div>

<div class="container">

    <h2>👤 Cadastro de Profissionais</h2>

    <?php if ($msgSucesso): ?>
        <div class="msg-sucesso"><?= $msgSucesso ?></div>
    <?php endif; ?>

    <?php if ($msgErro): ?>
        <div class="msg-erro"><?= $msgErro ?></div>
    <?php endif; ?>


    <!-- FORM NOVO PROFISSIONAL -->
    <form method="POST">
        <input type="hidden" name="acao" value="novo">

        <input type="text" name="nome" placeholder="Nome do Profissional" required>
        <input type="text" name="especialidade" placeholder="Especialidade" required>
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required>

        <button type="submit">Cadastrar Profissional</button>
    </form>
    




    <h3>📋 Profissionais Cadastrados</h3>

    <table>
        <tr>
            <th>Nome</th>
            <th>Especialidade</th>
            <th>Ações</th>
        </tr>

        <?php foreach ($profissionais as $p): ?>
        <tr>
            <td><?= $p['nome'] ?></td>
            <td><?= $p['especialidade'] ?></td>

            <td>
                <a class="btn-edit" href="admin_profissionais.php?editar=<?= $p['id_profissional'] ?>">Editar</a>
                <a class="btn-delete" href="admin_profissionais.php?excluir=<?= $p['id_profissional'] ?>"
                   onclick="return confirm('Tem certeza que deseja excluir este profissional?')">
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
    $prof = buscarProfissionalPorId($idEditar);

    if ($prof):
?>

<br><br>

<h3>✏ Editar Profissional</h3>

<form method="POST">
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="id" value="<?= $prof['id_profissional'] ?>">

    <input type="text" name="nome" 
           value="<?= $prof['nome'] ?>" 
           placeholder="Nome" required>

    <input type="text" name="especialidade" 
           value="<?= $prof['especialidade'] ?>" 
           placeholder="Especialidade" required>

    <button type="submit">Salvar Alterações</button>
</form>

<?php 
    endif;
endif;
?>

</div>

</body>
</html>
