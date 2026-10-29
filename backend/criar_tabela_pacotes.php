<?php
require_once("conexao.php");

try {
    $sql = "CREATE TABLE IF NOT EXISTS pacote_cliente (
        id_pacote INT AUTO_INCREMENT PRIMARY KEY,
        id_cliente INT NOT NULL,
        descricao VARCHAR(150) NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        qtd_total INT NOT NULL DEFAULT 4,
        qtd_usada INT NOT NULL DEFAULT 0,
        data_pagamento DATE NOT NULL,
        status ENUM('ABERTO', 'FECHADO') NOT NULL DEFAULT 'ABERTO',
        observacao TEXT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_cliente (id_cliente),
        INDEX idx_data_pag (data_pagamento),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $pdo->exec($sql);
    echo "Tabela pacote_cliente criada ou já existente com sucesso!\n";

    $sqlUsos = "CREATE TABLE IF NOT EXISTS pacote_uso_agenda (
        id_uso INT AUTO_INCREMENT PRIMARY KEY,
        id_agenda INT NOT NULL,
        id_cliente INT NOT NULL,
        id_pacote INT NOT NULL,
        data DATE NOT NULL,
        hora TIME NOT NULL,
        UNIQUE KEY uk_pacote_uso_agenda (id_agenda),
        UNIQUE KEY uk_pacote_uso_cliente_horario (id_cliente, data, hora),
        INDEX idx_pacote_uso_pacote (id_pacote)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $pdo->exec($sqlUsos);
    echo "Tabela pacote_uso_agenda criada ou já existente com sucesso!\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage() . "\n";
}
