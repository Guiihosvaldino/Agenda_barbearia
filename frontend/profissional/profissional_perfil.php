<?php
session_start();
require_once("../../backend/profissional.php");

if (!isset($_SESSION['profissional_id'])) {
    header("Location: profissional_login.php?erro=login");
    exit;
}

$id = $_SESSION['profissional_id'];
$prof = buscarProfissionalPorId($id);

$msgSucesso = "";
$msgErro = "";

// === ATUALIZAR PERFIL (nome, email, especialidade)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['acao'] == "perfil") {

    $nome = $_POST['nome'];
    $especialidade = $_POST['especialidade'];
    $email = $_POST['email'];

    if (atualizarPerfilProfissional($id, $nome, $especialidade, $email)) {
        $msgSucesso = "Perfil atualizado com sucesso!";
        $_SESSION['profissional_nome'] = $nome;
        $prof = buscarProfissionalPorId($id);
    } else {
        $msgErro = "Erro ao atualizar perfil!";
    }
}


// === ATUALIZAR SENHA
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['acao'] == "senha") {

    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar = $_POST['confirmar'];

    if (!password_verify($senha_atual, $prof['senha'])) {
        $msgErro = "❌ Senha atual incorreta!";
    } elseif ($nova_senha !== $confirmar) {
        $msgErro = "❌ A nova senha e a confirmação não coincidem!";
    } elseif (strlen($nova_senha) < 4) {
        $msgErro = "❌ A nova senha deve ter pelo menos 4 caracteres!";
    } else {
        if (atualizarSenhaProfissional($id, $nova_senha)) {
            $msgSucesso = "🔑 Senha alterada com sucesso!";
        } else {
            $msgErro = "❌ Erro ao alterar senha!";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>

<style>
body {
    margin: 0;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    font-family: 'Segoe UI', Arial;
    color: white;
}
.menu {
    background: #0d2a52;
    padding: 14px 25px;
    display: flex;
    gap: 25px;
    position: sticky;
    top: 0;
}
.menu a {
    color: #fff;
    font-weight: bold;
    text-decoration: none;
    padding: 6px 10px;
    border-radius: 6px;
}
.menu a:hover {
    background: #1e3c72;
}
.container {
    background: white;
    max-width: 600px;
    margin: 40px auto;
    padding: 30px;
    color: #222;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}
h1 {
    color: #1e3c72;
    text-align: center;
}
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
input {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #aaa;
}
button {
    background: #1e3c72;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 1.1em;
    cursor: pointer;
    font-weight: bold;
}
button:hover {
    background: #16315c;
}
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
.section-title {
    margin-top: 20px;
    color: #1e3c72;
    font-size: 1.4em;
    border-bottom: 2px solid #1e3c72;
    padding-bottom: 5px;
}
</style>

</head>
<body>

<div class="menu">
    <a href="profissional_home.php">🏠 Home</a>
    <a href="profissional_agenda.php">📅 Minha Agenda</a>
    <a href="profissional_perfil.php">👤 Meu Perfil</a>
    <a href="profissional_estatistica.php">📊 Estatísticas</a>
    <a href="profissional_agendar.php">➕ Novo Agendamento</a>
    <a href="profissional_login.php">🚪 Sair</a>
</div>


<div class="container">

    <h1>👤 Meu Perfil</h1>

    <?php if ($msgSucesso): ?>
        <div class="msg-sucesso"><?= $msgSucesso ?></div>
    <?php endif; ?>

    <?php if ($msgErro): ?>
        <div class="msg-erro"><?= $msgErro ?></div>
    <?php endif; ?>


    <!-- =======================
         FORMULÁRIO DE PERFIL
         ======================= -->
    <h2 class="section-title">📄 Dados Pessoais</h2>

    <form method="POST">
        <input type="hidden" name="acao" value="perfil">

        <label>Nome:</label>
        <input type="text" name="nome" value="<?= $prof['nome'] ?>" required>

        <label>Especialidade:</label>
        <input type="text" name="especialidade" value="<?= $prof['especialidade'] ?>" required>

        <label>E-mail:</label>
        <input type="email" name="email" value="<?= $prof['email'] ?>" required>

        <button type="submit">Salvar Alterações</button>
    </form>


    <!-- =======================
         ALTERAÇÃO DE SENHA
         ======================= -->
    <h2 class="section-title">🔑 Alterar Senha</h2>

    <form method="POST">
        <input type="hidden" name="acao" value="senha">

        <label>Senha atual:</label>
        <input type="password" name="senha_atual" required>

        <label>Nova senha:</label>
        <input type="password" name="nova_senha" required>

        <label>Confirmar nova senha:</label>
        <input type="password" name="confirmar" required>

        <button type="submit">Atualizar Senha</button>
    </form>

</div>

</body>
</html>
