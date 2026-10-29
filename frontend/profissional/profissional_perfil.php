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
    $almoco_inicio = !empty($_POST['almoco_inicio']) ? $_POST['almoco_inicio'] : null;
    $almoco_fim = !empty($_POST['almoco_fim']) ? $_POST['almoco_fim'] : null;

    if (atualizarPerfilProfissional($id, $nome, $especialidade, $email, $almoco_inicio, $almoco_fim)) {
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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Meu Perfil</title>

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
h1 {
    color: #660a18;
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
    background: #660a18;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 1.1em;
    cursor: pointer;
    font-weight: bold;
}
button:hover {
    background: #480610;
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
    color: #660a18;
    font-size: 1.4em;
    border-bottom: 2px solid #660a18;
    padding-bottom: 5px;
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
    <h1>👤 Meu Perfil</h1>

    <?php if ($msgSucesso): ?>
        <div class="msg-sucesso"><?= $msgSucesso ?></div>
    <?php endif; ?>

    <?php if ($msgErro): ?>
        <div class="msg-erro"><?= $msgErro ?></div>
    <?php endif; ?>


    <h2 class="section-title">📄 Dados Pessoais</h2>

    <form method="POST">
        <input type="hidden" name="acao" value="perfil">

        <label>Nome:</label>
        <input type="text" name="nome" value="<?= $prof['nome'] ?>" required>

        <label>Especialidade:</label>
        <input type="text" name="especialidade" value="<?= $prof['especialidade'] ?>" required>

        <label>E-mail:</label>
        <input type="email" name="email" value="<?= $prof['email'] ?>" required>

        <label>Início do Almoço:</label>
        <input type="time" name="almoco_inicio" value="<?= !empty($prof['almoco_inicio']) ? date('H:i', strtotime($prof['almoco_inicio'])) : '' ?>">

        <label>Fim do Almoço:</label>
        <input type="time" name="almoco_fim" value="<?= !empty($prof['almoco_fim']) ? date('H:i', strtotime($prof['almoco_fim'])) : '' ?>">

        <button type="submit">Salvar Alterações</button>
    </form>


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
