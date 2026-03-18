<?php
require_once("conexao.php");

function buscarClientePorCelular($celular) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE celular = ?");
    $stmt->execute([$celular]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function cadastrarCliente($nome, $celular, $senha, $dataNascimento) {
    global $pdo;

    // gera hash seguro
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO cliente (nome, celular, senha, data_nascimento) 
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$nome, $celular, $senhaHash, $dataNascimento]);
    
    return $pdo->lastInsertId();
}
function buscarClientePorNome($nome) {
    global $pdo;

    $nome = trim($nome);

    $stmt = $pdo->prepare("
        SELECT * 
        FROM cliente 
        WHERE LOWER(nome) LIKE LOWER(?)
    ");

    $stmt->execute(["%$nome%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function salvarTokenCliente($idCliente, $token, $expira) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE cliente 
        SET token_recuperacao = ?, token_expira = ?
        WHERE id_cliente = ?
    ");
    return $stmt->execute([$token, $expira, $idCliente]);
}

function validarTokenCliente($idCliente, $token) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM cliente
        WHERE id_cliente = ?
        AND token_recuperacao = ?
        AND token_expira >= NOW()
    ");
    $stmt->execute([$idCliente, $token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar') {

    require_once("conexao.php");

    $nome    = $_POST['nome'] ?? '';
    $celular = $_POST['celular'] ?? '';
    $senha   = $_POST['senha'] ?? '';

    if (!$nome || !$celular || !$senha) {
        echo "Preencha todos os campos!";
        exit;
    }

    // hash da senha
    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO cliente (nome, celular, senha)
        VALUES (?, ?, ?)
    ");

    if ($stmt->execute([$nome, $celular, $hash])) {
        echo "Cliente criado com sucesso!";
    } else {
        echo "Erro ao criar cliente!";
    }

    exit; // MUITO IMPORTANTE
}
function listarClientes() {
    global $pdo;

    $stmt = $pdo->query("
        SELECT id_cliente, nome, celular
        FROM cliente
        ORDER BY nome ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>
