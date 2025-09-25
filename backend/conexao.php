<?php
$host = "localhost";
$user = "root";  // seu usuário do MySQL
$pass = "jesuscristo";      // senha do MySQL
$db   = "barbearia_agenda"; // nome do banco

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexão estabelecida!";
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

