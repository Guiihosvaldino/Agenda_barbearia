<?php
require_once("../../backend/conexao.php");

$id = $_GET['id'];

$sql = $pdo->prepare("DELETE FROM agendamentos WHERE id_agendamento = ?");
$sql->execute([$id]);

header("Location: agenda.php");
