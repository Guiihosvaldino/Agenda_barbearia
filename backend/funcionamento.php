<?php
require_once("conexao.php");

function listarFuncionamento() {
    global $pdo;
    return $pdo->query("SELECT * FROM funcionamento")->fetchAll(PDO::FETCH_ASSOC);
}

function salvarFuncionamento($dia, $abre, $fecha, $ativo) {
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE funcionamento
        SET abre=?, fecha=?, ativo=?
        WHERE dia_semana=?
    ");

    return $stmt->execute([$abre, $fecha, $ativo, $dia]);
}


$diaSemanaNumero = date('N', strtotime($data)); // 1=Seg ... 7=Dom

$map = [
    1=>'SEGUNDA',
    2=>'TERCA',
    3=>'QUARTA',
    4=>'QUINTA',
    5=>'SEXTA',
    6=>'SABADO',
    7=>'DOMINGO'
];

$diaSemana = $map[$diaSemanaNumero];

$stmt = $pdo->prepare("
    SELECT abre, fecha 
    FROM funcionamento 
    WHERE dia_semana=? AND ativo=1
");
$stmt->execute([$diaSemana]);
$func = $stmt->fetch(PDO::FETCH_ASSOC);


?>