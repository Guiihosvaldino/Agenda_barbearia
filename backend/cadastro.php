<?php
require_once("conexao.php");

$celular = $_POST['celular'] ?? '';

if (empty($celular)) {
    header("Location: ../frontend/novo_cliente.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE celular = ?");
$stmt->execute([$celular]);
$cliente = $stmt->fetch();

if ($cliente) {
    // Se existir, volta para a mesma tela passando um parâmetro de erro
    header("Location: ../frontend/novo_cliente.php?erro=existente&celular=" . $celular);
    exit;
}

// Se NÃO existir, segue para a próxima fase
header("Location: ../frontend/cadastro.php?celular=" . $celular);
exit;