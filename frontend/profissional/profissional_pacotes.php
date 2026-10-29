<?php
require_once("../../backend/conexao.php");
require_once("../../backend/pacote.php");
session_start();

if (!isset($_SESSION['profissional_id'])) {
    header("Location: profissional_login.php?erro=login");
    exit();
}

$aba = $_GET['aba'] ?? 'ABERTO';
if (!in_array($aba, ['ABERTO', 'FECHADO'], true)) {
    $aba = 'ABERTO';
}

$pacotes = listarPacotes($aba);
$totalAbertos = count(listarPacotes('ABERTO'));
$totalFechados = count(listarPacotes('FECHADO'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Pacotes de Clientes</title>
    <link rel="stylesheet" href="../css/menu-lateral.css">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding: 1px 18px 35px;
            box-sizing: border-box;
            background: linear-gradient(rgba(0, 0, 0, 0.48), rgba(0, 0, 0, 0.48)), url('../imagem/FUNDO.jpg') center / cover fixed;
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
        }

        .pacotes-container {
            position: relative;
            z-index: 1;
            width: min(1100px, 100%);
            margin: 34px auto;
            padding: 26px;
            box-sizing: border-box;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
        }

        h1 {
            margin: 0 0 8px;
            color: #660a18;
            font-size: 1.6rem;
        }

        .descricao-pagina {
            margin: 0 0 22px;
            color: #555;
        }

        .abas-pacotes {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .abas-pacotes a {
            padding: 12px 15px;
            border-bottom: 3px solid transparent;
            color: #555;
            font-weight: 700;
            text-decoration: none;
        }

        .abas-pacotes a.ativa {
            border-color: #660a18;
            color: #660a18;
        }

        .contador {
            display: inline-block;
            min-width: 22px;
            margin-left: 5px;
            padding: 2px 6px;
            border-radius: 12px;
            background: #eee;
            text-align: center;
        }

        .tabela-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th,
        td {
            padding: 13px 10px;
            border-bottom: 1px solid #e8e8e8;
            vertical-align: middle;
        }

        th {
            color: #660a18;
            background: #faf6f7;
            white-space: nowrap;
        }

        .cliente-nome {
            display: block;
            font-weight: 700;
        }

        .cliente-celular,
        .observacao {
            display: block;
            margin-top: 4px;
            color: #666;
            font-size: 0.88em;
        }

        .progresso {
            min-width: 130px;
        }

        .barra-fundo {
            height: 8px;
            margin-top: 7px;
            overflow: hidden;
            border-radius: 10px;
            background: #eee;
        }

        .barra-preenchida {
            height: 100%;
            border-radius: inherit;
            background: #2e7d32;
        }

        .vazio {
            padding: 38px 15px;
            color: #666;
            text-align: center;
        }

        @media (max-width: 600px) {
            .pacotes-container {
                margin: 76px auto 20px;
                padding: 18px 14px;
            }

            h1 {
                font-size: 1.35rem;
            }

            th,
            td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/menu_lateral.php'; ?>

<main class="pacotes-container">
    <h1>📦 Pacotes dos clientes</h1>
    <p class="descricao-pagina">Consulte os atendimentos disponíveis e o histórico de pacotes. Para abater um atendimento, abra o agendamento do cliente e selecione o pacote.</p>

    <nav class="abas-pacotes" aria-label="Status dos pacotes">
        <a href="profissional_pacotes.php?aba=ABERTO" class="<?= $aba === 'ABERTO' ? 'ativa' : '' ?>">
            Abertos <span class="contador"><?= $totalAbertos ?></span>
        </a>
        <a href="profissional_pacotes.php?aba=FECHADO" class="<?= $aba === 'FECHADO' ? 'ativa' : '' ?>">
            Fechados <span class="contador"><?= $totalFechados ?></span>
        </a>
    </nav>

    <div class="tabela-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Pacote</th>
                    <th>Cliente</th>
                    <th>Pagamento</th>
                    <th>Atendimentos</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$pacotes): ?>
                <tr>
                    <td class="vazio" colspan="5">Nenhum pacote <?= $aba === 'ABERTO' ? 'aberto' : 'fechado' ?> encontrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pacotes as $pacote):
                    $total = (int)$pacote['qtd_total'];
                    $usados = (int)$pacote['qtd_usada'];
                    $percentual = $total > 0 ? min(100, (int)round(($usados / $total) * 100)) : 0;
                ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($pacote['descricao'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php if (!empty($pacote['observacao'])): ?>
                                <span class="observacao"><?= htmlspecialchars($pacote['observacao'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="cliente-nome"><?= htmlspecialchars($pacote['cliente_nome'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="cliente-celular"><?= htmlspecialchars($pacote['cliente_celular'], ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($pacote['data_pagamento'])) ?></td>
                        <td>
                            <div class="progresso">
                                <?= $usados ?> de <?= $total ?>
                                <div class="barra-fundo" aria-label="<?= $percentual ?>% utilizado">
                                    <div class="barra-preenchida" style="width: <?= $percentual ?>%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>R$ <?= number_format((float)$pacote['valor'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
