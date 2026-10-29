<?php
require_once("../../backend/admin.php");
require_once("../../backend/pacote.php");
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

$msgSucesso = "";
$msgErro = "";

// Ações via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar_pacote') {
        $id_pacote      = !empty($_POST['id_pacote']) ? intval($_POST['id_pacote']) : null;
        $id_cliente     = intval($_POST['id_cliente'] ?? 0);
        $descricao      = trim($_POST['descricao'] ?? '');
        $valor          = floatval(str_replace(',', '.', $_POST['valor'] ?? '0'));
        $qtd_total      = intval($_POST['qtd_total'] ?? 4);
        $qtd_usada      = intval($_POST['qtd_usada'] ?? 0);
        $data_pagamento = $_POST['data_pagamento'] ?? date('Y-m-d');
        $status         = $_POST['status'] ?? 'ABERTO';
        $observacao     = trim($_POST['observacao'] ?? '');

        if ($id_cliente <= 0 || empty($descricao) || $valor <= 0 || $qtd_total <= 0) {
            $msgErro = "Por favor, preencha todos os campos obrigatórios corretamente.";
        } else {
            if ($qtd_usada >= $qtd_total) {
                $status = 'FECHADO';
            }

            if ($id_pacote) {
                $res = atualizarPacote($id_pacote, $id_cliente, $descricao, $valor, $qtd_total, $qtd_usada, $data_pagamento, $status, $observacao);
                if ($res) {
                    $msgSucesso = "Pacote atualizado com sucesso! O valor foi atualizado no financeiro para o dia " . date('d/m/Y', strtotime($data_pagamento)) . ".";
                } else {
                    $msgErro = "Erro ao atualizar o pacote.";
                }
            } else {
                $res = criarPacote($id_cliente, $descricao, $valor, $qtd_total, $qtd_usada, $data_pagamento, $status, $observacao);
                if ($res) {
                    $msgSucesso = "Pacote criado com sucesso! O valor de R$ " . number_format($valor, 2, ',', '.') . " foi lançado no financeiro do dia " . date('d/m/Y', strtotime($data_pagamento)) . ".";
                } else {
                    $msgErro = "Erro ao criar o pacote.";
                }
            }
        }
    } elseif ($acao === 'registrar_uso') {
        $id_pacote = intval($_POST['id_pacote'] ?? 0);
        if ($id_pacote > 0) {
            if (registrarUsoPacote($id_pacote)) {
                $msgSucesso = "Atendimento registrado com sucesso no pacote!";
            } else {
                $msgErro = "Erro ao registrar atendimento.";
            }
        }
    } elseif ($acao === 'excluir_pacote') {
        $id_pacote = intval($_POST['id_pacote'] ?? 0);
        if ($id_pacote > 0) {
            if (excluirPacote($id_pacote)) {
                $msgSucesso = "Pacote excluído com sucesso do sistema e do financeiro.";
            } else {
                $msgErro = "Erro ao excluir pacote.";
            }
        }
    }
}

// Aba ativa: ABERTAS ou FECHADAS
$aba = $_GET['aba'] ?? 'ABERTO';
if (!in_array($aba, ['ABERTO', 'FECHADO'])) {
    $aba = 'ABERTO';
}

// Filtro de mês/ano para fechados
$mesFiltro = !empty($_GET['mes']) ? intval($_GET['mes']) : null;
$anoFiltro = !empty($_GET['ano']) ? intval($_GET['ano']) : null;

// Buscar listas
$pacotes = listarPacotes($aba, $mesFiltro, $anoFiltro);
$clientes = listarClientesParaPacote();

// Contagens para os badges das abas
$totalAbertosCount = count(listarPacotes('ABERTO'));
$totalFechadosCount = count(listarPacotes('FECHADO'));

// Somatório dos pacotes exibidos
$totalValorExibido = 0;
foreach ($pacotes as $p) {
    $totalValorExibido += floatval($p['valor']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Gerenciamento de Pacotes - Admin</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: url('../imagem/FUNDO.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333;
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
            gap: 20px;
            font-size: 1.05em;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .menu a {
            color: #fff;
            text-decoration: none;
            padding: 7px 14px;
            font-weight: bold;
            border-radius: 6px;
            transition: 0.2s;
        }

        .menu a:hover, .menu a.ativo {
            background: #660a18;
        }

        .main-container {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px 40px 20px;
        }

        .card-painel {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            padding: 25px 30px;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .top-bar-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .top-bar-title h1 {
            margin: 0;
            font-size: 1.6em;
            color: #660a18;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .btn-novo-pacote {
            background: linear-gradient(135deg, #660a18, #8a1224);
            color: #fff;
            border: none;
            padding: 12px 24px;
            font-size: 0.95em;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(102, 10, 24, 0.35);
            transition: 0.2s;
        }

        .btn-novo-pacote:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 10, 24, 0.45);
            background: linear-gradient(135deg, #8a1224, #660a18);
        }

        /* Abas Abertas / Fechadas */
        .tabs-header {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 20px;
            gap: 20px;
        }

        .tab-btn {
            padding: 12px 20px;
            font-size: 1.05em;
            font-weight: 700;
            color: #666;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: #660a18;
        }

        .tab-btn.active {
            color: #660a18;
            border-bottom-color: #660a18;
        }

        .tab-badge {
            background: #eee;
            color: #555;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .tab-btn.active .tab-badge {
            background: #660a18;
            color: #fff;
        }

        /* Filtro e Busca */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 420px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.95em;
            outline: none;
            box-sizing: border-box;
            transition: 0.2s;
        }

        .search-box input:focus {
            border-color: #8a1224;
            box-shadow: 0 0 6px rgba(138, 18, 36, 0.25);
        }

        .search-box svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            fill: #888;
            width: 18px;
            height: 18px;
        }

        .total-box {
            font-size: 1.05em;
            font-weight: 700;
            color: #333;
        }

        .total-box span {
            color: #1e7e34;
            font-size: 1.2em;
        }

        .filtro-meses {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fdf2f4;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #f3d4db;
        }

        .filtro-meses select, .filtro-meses input {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.9em;
        }

        .filtro-meses button {
            background: #660a18;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        /* Mensagens */
        .msg {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .msg.sucesso {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }

        .msg.erro {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }

        /* Tabela */
        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #e5e5e5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        thead tr {
            background: #fbfbfb;
            border-bottom: 2px solid #e0e0e0;
        }

        th {
            padding: 14px 16px;
            font-size: 0.9em;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #eee;
            transition: 0.15s;
        }

        tbody tr:hover {
            background: #fcf6f7;
        }

        td {
            padding: 14px 16px;
            font-size: 0.95em;
            color: #333;
            vertical-align: middle;
        }

        .desc-principal {
            font-weight: 700;
            color: #660a18;
            font-size: 1.05em;
        }

        .cliente-info {
            display: flex;
            flex-direction: column;
        }

        .cliente-nome {
            font-weight: 700;
            color: #222;
        }

        .cliente-cel {
            font-size: 0.85em;
            color: #777;
        }

        .badge-progresso {
            display: inline-flex;
            flex-direction: column;
            gap: 4px;
            min-width: 130px;
        }

        .badge-texto {
            font-weight: 700;
            font-size: 0.88em;
            color: #444;
        }

        .progress-bar-bg {
            background: #e9ecef;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            width: 100%;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #660a18, #8a1224);
            border-radius: 4px;
            transition: width 0.3s;
        }

        .progress-bar-fill.completo {
            background: #28a745;
        }

        .badge-pago {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 700;
            display: inline-block;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8em;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-status.aberto {
            background: #e3f2fd;
            color: #1565c0;
        }

        .badge-status.fechado {
            background: #efebe9;
            color: #4e342e;
        }

        /* Ações */
        .acoes-col {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-acao {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px 9px;
            border-radius: 6px;
            font-size: 1.1em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .btn-usar {
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 0.85em;
            font-weight: bold;
            padding: 6px 10px;
            border: 1px solid #c8e6c9;
        }

        .btn-usar:hover {
            background: #2e7d32;
            color: #fff;
        }

        .btn-editar {
            color: #660a18;
            background: #fdf2f4;
        }

        .btn-editar:hover {
            background: #660a18;
            color: #fff;
        }

        .btn-excluir {
            color: #d32f2f;
            background: #ffebee;
        }

        .btn-excluir:hover {
            background: #d32f2f;
            color: #fff;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .modal.ativo {
            display: flex;
        }

        .modal-content {
            background: #fff;
            width: 100%;
            max-width: 520px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: modalFade 0.25s ease-out;
        }

        @keyframes modalFade {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            background: #660a18;
            color: #fff;
            padding: 16px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25em;
        }

        .modal-header .btn-fechar {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.5em;
            cursor: pointer;
            line-height: 1;
        }

        .modal-body {
            padding: 22px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            font-size: 0.9em;
            color: #444;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.95em;
            box-sizing: border-box;
            outline: none;
            transition: 0.2s;
        }

        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            border-color: #8a1224;
            box-shadow: 0 0 5px rgba(138, 18, 36, 0.3);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .info-ajuda {
            font-size: 0.82em;
            color: #666;
            margin-top: 4px;
        }

        .modal-footer {
            padding: 14px 22px;
            background: #f8f8f8;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-cancelar {
            background: #ddd;
            color: #333;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-salvar {
            background: #660a18;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-salvar:hover {
            background: #480610;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #777;
        }

        .empty-state p {
            margin: 10px 0 0 0;
            font-size: 1.1em;
        }

        @media (max-width: 768px) {
            .menu {
                flex-wrap: wrap;
                justify-content: center;
                gap: 8px;
            }
            .top-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
    <link rel="stylesheet" href="../css/menu-lateral.css">
</head>
<body>

<?php require __DIR__ . '/menu_lateral.php'; ?>

<div class="main-container">

    <div class="card-painel">

        <!-- Topo da página -->
        <div class="top-bar">
            <div class="top-bar-title">
                <h1>📦 COMANDAS / PACOTES DE CORTES</h1>
            </div>
            <button class="btn-novo-pacote" onclick="abrirModalNovo()">
                ➕ ADICIONAR PACOTE
            </button>
        </div>

        <?php if ($msgSucesso): ?>
            <div class="msg sucesso">✅ <?= $msgSucesso ?></div>
        <?php endif; ?>

        <?php if ($msgErro): ?>
            <div class="msg erro">❌ <?= $msgErro ?></div>
        <?php endif; ?>

        <!-- Abas -->
        <div class="tabs-header">
            <a href="admin_pacotes.php?aba=ABERTO" class="tab-btn <?= $aba === 'ABERTO' ? 'active' : '' ?>">
                ABERTAS <span class="tab-badge"><?= $totalAbertosCount ?></span>
            </a>
            <a href="admin_pacotes.php?aba=FECHADO" class="tab-btn <?= $aba === 'FECHADO' ? 'active' : '' ?>">
                FECHADAS <span class="tab-badge"><?= $totalFechadosCount ?></span>
            </a>
        </div>

        <!-- Barra de busca e filtros -->
        <div class="filter-bar">
            <div class="search-box">
                <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="text" id="campoBusca" placeholder="Pesquise clientes por nome..." onkeyup="filtrarClientes()">
            </div>

            <?php if ($aba === 'FECHADO'): ?>
            <form method="GET" class="filtro-meses">
                <input type="hidden" name="aba" value="FECHADO">
                <label style="font-weight:bold; font-size: 0.9em; color:#660a18;">Filtrar Mês:</label>
                <select name="mes">
                    <option value="">Todos</option>
                    <?php 
                    $mesesNome = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
                    for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($mesFiltro == $m) ? 'selected' : '' ?>><?= $mesesNome[$m] ?></option>
                    <?php endfor; ?>
                </select>
                <input type="number" name="ano" value="<?= $anoFiltro ?? date('Y') ?>" style="width: 75px;">
                <button type="submit">Buscar</button>
                <?php if ($mesFiltro || $anoFiltro): ?>
                    <a href="admin_pacotes.php?aba=FECHADO" style="font-size: 0.85em; color: #660a18; text-decoration: underline;">Limpar</a>
                <?php endif; ?>
            </form>
            <?php endif; ?>

            <div class="total-box">
                Total Pago: <span>R$ <?= number_format($totalValorExibido, 2, ',', '.') ?></span>
            </div>
        </div>

        <!-- Tabela -->
        <div class="table-responsive">
            <table id="tabelaPacotes">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Cliente</th>
                        <th>Data Pagamento 📅</th>
                        <th>N. Atendimentos</th>
                        <th>Total</th>
                        <th>Total Pago</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pacotes)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                📭
                                <p>Nenhum pacote encontrado <?= $aba === 'ABERTO' ? 'em aberto' : 'fechado' ?>.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pacotes as $p): 
                        $porc = ($p['qtd_total'] > 0) ? min(100, round(($p['qtd_usada'] / $p['qtd_total']) * 100)) : 0;
                        $completo = ($p['qtd_usada'] >= $p['qtd_total']);
                    ?>
                    <tr class="linha-pacote" data-cliente="<?= strtolower(htmlspecialchars($p['cliente_nome'])) ?>">
                        <td>
                            <div class="desc-principal"><?= htmlspecialchars($p['descricao']) ?></div>
                            <?php if (!empty($p['observacao'])): ?>
                                <small style="color: #777;"><?= htmlspecialchars($p['observacao']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="cliente-info">
                                <span class="cliente-nome"><?= htmlspecialchars($p['cliente_nome']) ?></span>
                                <span class="cliente-cel"><?= htmlspecialchars($p['cliente_celular']) ?></span>
                            </div>
                        </td>
                        <td>
                            <strong><?= date('d/m/Y', strtotime($p['data_pagamento'])) ?></strong>
                        </td>
                        <td>
                            <div class="badge-progresso">
                                <span class="badge-texto"><?= $p['qtd_usada'] ?> de <?= $p['qtd_total'] ?> atendimentos</span>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill <?= $completo ? 'completo' : '' ?>" style="width: <?= $porc ?>%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>R$ <?= number_format($p['valor'], 2, ',', '.') ?></strong>
                        </td>
                        <td>
                            <span class="badge-pago">R$ <?= number_format($p['valor'], 2, ',', '.') ?></span>
                        </td>
                        <td style="text-align: center;">
                            <div class="acoes-col" style="justify-content: center;">
                                <?php if ($p['status'] === 'ABERTO'): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Deseja registrar +1 atendimento utilizado para <?= htmlspecialchars(addslashes($p['cliente_nome'])) ?>?');">
                                        <input type="hidden" name="acao" value="registrar_uso">
                                        <input type="hidden" name="id_pacote" value="<?= $p['id_pacote'] ?>">
                                        <button type="submit" class="btn-acao btn-usar" title="Registrar 1 Corte Feito">
                                            ✂️ +1 Corte
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <button class="btn-acao btn-editar" title="Editar Pacote / Alterar Data" onclick='abrirModalEditar(<?= json_encode($p) ?>)'>
                                    ✏️
                                </button>

                                <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este pacote? Ele também sairá do financeiro.');">
                                    <input type="hidden" name="acao" value="excluir_pacote">
                                    <input type="hidden" name="id_pacote" value="<?= $p['id_pacote'] ?>">
                                    <button type="submit" class="btn-acao btn-excluir" title="Excluir Pacote">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- Modal Criar / Editar Pacote -->
<div id="modalPacote" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">📦 Novo Pacote de Cortes</h3>
            <button type="button" class="btn-fechar" onclick="fecharModal()">&times;</button>
        </div>

        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="acao" value="salvar_pacote">
                <input type="hidden" name="id_pacote" id="input_id_pacote" value="">

                <div class="form-group">
                    <label>Cliente: *</label>
                    <select name="id_cliente" id="input_id_cliente" required>
                        <option value="">Selecione o Cliente</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>">
                                <?= htmlspecialchars($c['nome']) ?> (<?= htmlspecialchars($c['celular']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Descrição do Pacote: *</label>
                    <input type="text" name="descricao" id="input_descricao" placeholder="Ex: Pacote 4 Cortes, Mensal Cabelo + Barba..." required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Valor Total (R$): *</label>
                        <input type="text" name="valor" id="input_valor" placeholder="Ex: 160.00" required>
                        <div class="info-ajuda">💰 Esse valor vai direto para o Financeiro no mês e dia selecionados.</div>
                    </div>

                    <div class="form-group">
                        <label>Data do Pagamento: *</label>
                        <input type="date" name="data_pagamento" id="input_data_pagamento" value="<?= date('Y-m-d') ?>" required>
                        <div class="info-ajuda">📅 Pode alterar a data caso tenha sido pago em dia anterior.</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Total de Cortes/Atendimentos: *</label>
                        <input type="number" name="qtd_total" id="input_qtd_total" min="1" value="4" required>
                    </div>

                    <div class="form-group">
                        <label>Cortes já Realizados:</label>
                        <input type="number" name="qtd_usada" id="input_qtd_usada" min="0" value="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Status do Pacote:</label>
                    <select name="status" id="input_status">
                        <option value="ABERTO">ABERTO (Ativo / Cortes Disponíveis)</option>
                        <option value="FECHADO">FECHADO (Concluído / Todos os Cortes Realizados)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Observação (Opcional):</label>
                    <textarea name="observacao" id="input_observacao" rows="2" placeholder="Ex: Pago no Pix, validade de 30 dias..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="btn-salvar">Salvar Pacote</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalNovo() {
    document.getElementById("modalTitulo").innerText = "📦 Novo Pacote de Cortes";
    document.getElementById("input_id_pacote").value = "";
    document.getElementById("input_id_cliente").value = "";
    document.getElementById("input_descricao").value = "Pacote 4 Cortes";
    document.getElementById("input_valor").value = "160.00";
    document.getElementById("input_data_pagamento").value = "<?= date('Y-m-d') ?>";
    document.getElementById("input_qtd_total").value = "4";
    document.getElementById("input_qtd_usada").value = "0";
    document.getElementById("input_status").value = "ABERTO";
    document.getElementById("input_observacao").value = "";
    
    document.getElementById("modalPacote").classList.add("ativo");
}

function abrirModalEditar(p) {
    document.getElementById("modalTitulo").innerText = "✏️ Editar Pacote de Cortes";
    document.getElementById("input_id_pacote").value = p.id_pacote;
    document.getElementById("input_id_cliente").value = p.id_cliente;
    document.getElementById("input_descricao").value = p.descricao;
    document.getElementById("input_valor").value = p.valor;
    document.getElementById("input_data_pagamento").value = p.data_pagamento;
    document.getElementById("input_qtd_total").value = p.qtd_total;
    document.getElementById("input_qtd_usada").value = p.qtd_usada;
    document.getElementById("input_status").value = p.status;
    document.getElementById("input_observacao").value = p.observacao || "";
    
    document.getElementById("modalPacote").classList.add("ativo");
}

function fecharModal() {
    document.getElementById("modalPacote").classList.remove("ativo");
}

window.onclick = function(event) {
    const modal = document.getElementById("modalPacote");
    if (event.target === modal) {
        fecharModal();
    }
}

// Filtro rápido por nome do cliente
function filtrarClientes() {
    const termo = document.getElementById("campoBusca").value.toLowerCase().trim();
    const linhas = document.querySelectorAll(".linha-pacote");

    linhas.forEach(linha => {
        const nomeCliente = linha.getAttribute("data-cliente") || "";
        const textoLinha = linha.innerText.toLowerCase();

        if (nomeCliente.includes(termo) || textoLinha.includes(termo)) {
            linha.style.display = "";
        } else {
            linha.style.display = "none";
        }
    });
}
</script>

</body>
</html>
