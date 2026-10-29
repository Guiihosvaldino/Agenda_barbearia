<?php
function carregarArquivoAmbiente($caminho) {
    if (!is_readable($caminho)) {
        return;
    }

    foreach (file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || $linha[0] === '#' || strpos($linha, '=') === false) {
            continue;
        }

        list($nome, $valor) = explode('=', $linha, 2);
        $nome = trim($nome);
        $valor = trim($valor);
        if ($nome === '' || getenv($nome) !== false) {
            continue;
        }

        if (strlen($valor) >= 2 && (
            ($valor[0] === '"' && substr($valor, -1) === '"')
            || ($valor[0] === "'" && substr($valor, -1) === "'")
        )) {
            $valor = substr($valor, 1, -1);
        }

        putenv($nome . '=' . $valor);
    }
}

carregarArquivoAmbiente(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

function obterVariavelAmbiente(array $nomes, $padrao = null) {
    foreach ($nomes as $nome) {
        $valor = getenv($nome);
        if ($valor !== false && $valor !== '') {
            return $valor;
        }
    }

    return $padrao;
}

$host = obterVariavelAmbiente(['MYSQLHOST', 'DB_HOST', 'MYSQL_HOST'], 'localhost');
$port = obterVariavelAmbiente(['MYSQLPORT', 'DB_PORT', 'MYSQL_PORT'], '3306');
$user = obterVariavelAmbiente(['MYSQLUSER', 'DB_USER', 'MYSQL_USER'], 'root');
$pass = obterVariavelAmbiente(['MYSQLPASSWORD', 'DB_PASSWORD', 'MYSQL_PASSWORD'], '');
$db = obterVariavelAmbiente(['MYSQLDATABASE', 'DB_NAME', 'MYSQL_DATABASE'], 'barbearia_agenda');

try {
    $pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4;proto=tcp",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
    http_response_code(500);
    exit("Erro ao conectar ao banco de dados. Verifique a configuração do serviço.");
}
