<?php
require_once("../../backend/admin.php");
require_once("../../backend/cliente.php");
require_once("../../backend/profissional.php");
require_once("../../backend/agenda.php");
require_once("../../backend/conexao.php"); 
require_once("../../backend/pacote.php");
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

garantirTabelaUsosPacote();

$agora_data = date('Y-m-d');
$agora_hora = date('H:i:s');

try {
    $sql_auto_confirm = "UPDATE agenda SET status = 'CONFIRMADO' 
                         WHERE status = 'PENDENTE' 
                         AND (data < :data_hoje OR (data = :data_hoje AND hora < :hora_agora))";
    $stmt_auto = $pdo->prepare($sql_auto_confirm);
    $stmt_auto->execute([':data_hoje' => $agora_data, ':hora_agora' => $agora_hora]);
} catch (PDOException $e) {}

$data_selecionada = (isset($_GET['data']) && !empty($_GET['data'])) ? $_GET['data'] : date('Y-m-d');
$mes_get = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');
$ano_get = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

$data_base_semana = new DateTime($data_selecionada);
if (isset($_GET['offset_semana'])) {
    $data_base_semana->modify((int)$_GET['offset_semana'] . " days");
    $data_selecionada = $data_base_semana->format('Y-m-d');
}

// Garante que a semana mostrada comece na Segunda-feira (1)
$dia_semana_num = (int)$data_base_semana->format('N');
if ($dia_semana_num > 1) {
    $data_base_semana->modify("-" . ($dia_semana_num - 1) . " days");
}

$data_obj_mes = DateTime::createFromFormat('Y-n-j', "$ano_get-$mes_get-1");
$mes_anterior = (clone $data_obj_mes)->modify('-1 month');
$mes_proximo = (clone $data_obj_mes)->modify('+1 month');

$profissionais = listarProfissionais();
$agendamentos_dia = listarAgendamentosDoDia($data_selecionada);
$lista_clientes = listarClientes(); 
$lista_servicos = listarServicos(); 

// Busca pacotes abertos para alertas em tempo real nos modais e na agenda
$stmtPacotesAbertos = $pdo->query("
    SELECT p.id_pacote, p.id_cliente, p.descricao, p.qtd_usada, p.qtd_total, p.valor, p.data_pagamento,
           c.nome AS cliente_nome, c.celular AS cliente_celular
    FROM pacote_cliente p
    INNER JOIN cliente c ON c.id_cliente = p.id_cliente
    WHERE p.status = 'ABERTO'
    ORDER BY p.id_pacote DESC
");
$todosPacotesAbertos = $stmtPacotesAbertos ? $stmtPacotesAbertos->fetchAll(PDO::FETCH_ASSOC) : [];

$agendamentos_por_profissional = [];
foreach ($profissionais as $p) {
    $agendamentos_por_profissional[$p['nome']] = [];
}
foreach ($agendamentos_dia as $ag) {
    $agendamentos_por_profissional[$ag['profissional']][] = $ag;
}

$dias_traducao = [
    'Sun' => 'DOMINGO', 'Mon' => 'SEGUNDA', 'Tue' => 'TERCA', 
    'Wed' => 'QUARTA', 'Thu' => 'QUINTA', 'Fri' => 'SEXTA', 'Sat' => 'SABADO'
];

$dias_curto_pt = [
    'Sun' => 'Dom', 'Mon' => 'Seg', 'Tue' => 'Ter', 
    'Wed' => 'Qua', 'Thu' => 'Qui', 'Fri' => 'Sex', 'Sat' => 'Sáb'
];

$dia_semana_nome = $dias_traducao[date('D', strtotime($data_selecionada))];

$sql_func = "SELECT abre, fecha, ativo FROM funcionamento WHERE dia_semana = :dia";
$stmt_func = $pdo->prepare($sql_func);
$stmt_func->execute([':dia' => $dia_semana_nome]);
$dados_func = $stmt_func->fetch(PDO::FETCH_ASSOC);

$esta_aberto = ($dados_func && (int)$dados_func['ativo'] === 1);
$agendamentos_dia = listarAgendamentosDoDia($data_selecionada);

// NOVA BUSCA: Bloqueios para a grade administrativa
$sql_b = "SELECT id_profissional, hora_inicio, hora_fim, descricao FROM bloqueio_agenda WHERE data = :data";
$stmt_b = $pdo->prepare($sql_b);
$stmt_b->execute([':data' => $data_selecionada]);
$bloqueios_dia = $stmt_b->fetchAll(PDO::FETCH_ASSOC);

$bloqueios_por_profissional = [];
foreach ($profissionais as $p) {
    $bloqueios_por_profissional[$p['id_profissional']] = [];
}
foreach ($bloqueios_dia as $b) {
    $bloqueios_por_profissional[$b['id_profissional']][] = $b;
}

$hora_inicio_expediente = ($dados_func && !empty($dados_func['abre'])) ? $dados_func['abre'] : '08:00:00';
$hora_fim_expediente = ($dados_func && !empty($dados_func['fecha'])) ? $dados_func['fecha'] : '19:00:00';

$inicio_dt = new DateTime($hora_inicio_expediente);
$fim_dt = new DateTime($hora_fim_expediente);

$minutos_inicio = (int)$inicio_dt->format('H') * 60 + (int)$inicio_dt->format('i');
$minutos_fim = (int)$fim_dt->format('H') * 60 + (int)$fim_dt->format('i');

// Verifica agendamentos e bloqueios fora do horário
foreach ($agendamentos_dia as $ag) {
    $ag_ini = new DateTime($ag['hora']);
    $duracao = (int)($ag['duracao'] ?? 30);
    $ag_ini_min = (int)$ag_ini->format('H') * 60 + (int)$ag_ini->format('i');
    $ag_fim_min = $ag_ini_min + $duracao;
    if ($ag_ini_min < $minutos_inicio) $minutos_inicio = $ag_ini_min;
    if ($ag_fim_min > $minutos_fim) $minutos_fim = $ag_fim_min;
}
foreach ($bloqueios_dia as $b) {
    $b_ini = new DateTime($b['hora_inicio']);
    $b_fim = new DateTime($b['hora_fim']);
    $b_ini_min = (int)$b_ini->format('H') * 60 + (int)$b_ini->format('i');
    $b_fim_min = (int)$b_fim->format('H') * 60 + (int)$b_fim->format('i');
    if ($b_ini_min < $minutos_inicio) $minutos_inicio = $b_ini_min;
    if ($b_fim_min > $minutos_fim) $minutos_fim = $b_fim_min;
}
foreach ($profissionais as $p) {
    if (!empty($p['almoco_inicio']) && !empty($p['almoco_fim'])) {
        $alm_ini = new DateTime($p['almoco_inicio']);
        $alm_fim = new DateTime($p['almoco_fim']);
        $alm_ini_min = (int)$alm_ini->format('H') * 60 + (int)$alm_ini->format('i');
        $alm_fim_min = (int)$alm_fim->format('H') * 60 + (int)$alm_fim->format('i');
        if ($alm_ini_min < $minutos_inicio) $minutos_inicio = $alm_ini_min;
        if ($alm_fim_min > $minutos_fim) $minutos_fim = $alm_fim_min;
    }
}

// Arredondar para blocos de 30 minutos e adicionar margem de 30 minutos nas pontas
$minutos_inicio = floor($minutos_inicio / 30) * 30 - 30;
$minutos_fim = ceil($minutos_fim / 30) * 30 + 30;
if ($minutos_inicio < 0) $minutos_inicio = 0;
if ($minutos_fim > 1440) $minutos_fim = 1440;

$inicio_dt = new DateTime(sprintf('%02d:%02d:00', floor($minutos_inicio / 60), $minutos_inicio % 60));
$fim_dt = new DateTime(sprintf('%02d:%02d:00', floor($minutos_fim / 60), $minutos_fim % 60));

$total_minutos = $minutos_fim - $minutos_inicio;
if ($total_minutos <= 0) $total_minutos = 600;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Agenda Master 💈</title>
    <style> 
        :root {
            --scale-factor: 1.8px; /* Altura em pixels por minuto */
        }
        body { background-image: url('../imagem/FUNDO.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; font-family: 'Segoe UI', sans-serif; color: #fff; margin: 0; }
        body::before { content: ''; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 0; }
        .container { position: relative; z-index: 1; background: white; color: #333; margin: 20px auto; max-width: 1200px; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .header-agenda { display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd; }
        .btn-nav { background: #380309; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .seletor-semana { display: flex; gap: 5px; }
        .dia-item { padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 5px; text-align: center; min-width: 80px; text-decoration: none; color: #333; }
        .dia-item.active { background: #660a18; color: white; transform: scale(1.05); }
        
        /* NOVO DESIGN DO SCHEDULER */
        .tabela-wrapper { overflow-x: auto; border: 1px solid #ddd; border-radius: 8px; background: #fff; }
        
        .timeline-grid {
            display: grid;
            grid-template-columns: 80px repeat(<?= count($profissionais) ?>, 1fr);
            position: relative;
            background: #fff;
            min-width: 750px;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .timeline-header-row {
            display: grid;
            grid-template-columns: 80px repeat(<?= count($profissionais) ?>, 1fr);
            background: #660a18;
            min-width: 750px;
            border-bottom: 2px solid #380309;
        }
        .timeline-header-col {
            color: white;
            padding: 12px;
            font-weight: bold;
            text-align: center;
            border-right: 1px solid #380309;
        }
        .timeline-header-col:last-child {
            border-right: none;
        }
        .timeline-time-col {
            background: #f8f9fa;
            border-right: 2px solid #ddd;
            height: calc(<?= $total_minutos ?> * var(--scale-factor));
        }
        .timeline-time-marker {
            position: absolute;
            left: 0;
            width: 80px;
            text-align: center;
            font-size: 0.8em;
            font-weight: bold;
            color: #666;
            transform: translateY(-50%);
            z-index: 2;
        }
        .timeline-time-line {
            position: absolute;
            left: 80px;
            right: 0;
            border-top: 1px dashed #eee;
            pointer-events: none;
            z-index: 1;
        }
        .timeline-time-line.hour {
            border-top: 1px solid #ddd;
        }
        .timeline-prof-col {
            position: relative;
            height: calc(<?= $total_minutos ?> * var(--scale-factor));
            border-right: 1px solid #eee;
            background-color: #fafafa;
            cursor: pointer;
            transition: background 0.15s;
        }
        .timeline-prof-col:last-child {
            border-right: none;
        }
        .timeline-prof-col:hover {
            background-color: #f7f9fc;
        }
        .card-agendamento-abs {
            position: absolute;
            left: 4px;
            right: 4px;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 0.8em;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            transition: transform 0.15s, box-shadow 0.15s;
            cursor: pointer;
            z-index: 5;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card-agendamento-abs:hover {
            transform: scale(1.015);
            box-shadow: 0 4px 8px rgba(0,0,0,0.25);
            z-index: 6;
        }

        /* Badge no canto do card (ex: 3/4) */
        .badge-pacote-corner {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.95);
            color: #111;
            border: 1px solid rgba(0, 0, 0, 0.25);
            font-size: 0.82em;
            font-weight: 800;
            padding: 3px 7px;
            border-radius: 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.25);
            z-index: 10;
            line-height: 1.2;
            letter-spacing: 0.5px;
            pointer-events: none;
            white-space: nowrap;
        }

        /* Alerta de comanda aberta nos modais (Estilo MinhaAgenda) */
        .card-alerta-comanda {
            margin-top: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            overflow: hidden;
            animation: fadeInAlert 0.25s ease-in-out;
        }
        @keyframes fadeInAlert {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .comanda-alert-header {
            background: #ff5722;
            color: #fff;
            font-weight: 700;
            font-size: 0.88em;
            padding: 8px 12px;
        }
        .comanda-alert-body {
            background: #ffffff;
            border: 1.5px solid #ff5722;
            border-top: none;
            padding: 12px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background 0.15s;
        }
        .comanda-alert-body:hover {
            background: #fff8f5;
        }
        .comanda-nome {
            font-weight: 700;
            color: #2e7d32;
            font-size: 1.05em;
        }
        .comanda-progresso {
            font-weight: 700;
            color: #2e7d32;
            font-size: 0.95em;
        }
        .comanda-alert-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .check-abater-label {
            color: #d84315;
            font-weight: 700;
            font-size: 0.88em;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            margin: 0;
        }
        .btn-ou-nova-comanda {
            border: 1.5px solid #ff5722;
            color: #ff5722;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 800;
            text-decoration: none;
            font-size: 0.8em;
            text-transform: uppercase;
            background: #fff;
            transition: 0.2s;
        }
        .btn-ou-nova-comanda:hover {
            background: #ff5722;
            color: #fff;
        }
        
        /* Cores vibrantes e premium baseadas no status */
        .card-agendamento-abs.pendente {
            background: linear-gradient(135deg, #f57c00, #ff9800);
            border-left: 5px solid #e65100;
            color: white;
        }
        .card-agendamento-abs.confirmado {
            background: linear-gradient(135deg, #c62828, #e53935);
            border-left: 5px solid #8e0000;
            color: white;
        }
        .card-agendamento-abs.cancelado {
            background: linear-gradient(135deg, #78909c, #b0bec5);
            border-left: 5px solid #37474f;
            color: white;
            opacity: 0.6;
        }
        .card-bloqueio-abs {
            position: absolute;
            left: 4px;
            right: 4px;
            background: repeating-linear-gradient(
              45deg,
              #eceff1,
              #eceff1 10px,
              #cfd8dc 10px,
              #cfd8dc 20px
            );
            border: 1px solid #b0bec5;
            border-radius: 6px;
            opacity: 0.85;
            z-index: 4;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #37474f;
            font-size: 0.78em;
            font-weight: bold;
            padding: 5px;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
        }
        .timeline-hover-indicator {
            display: none;
            position: absolute;
            left: 80px;
            right: 0;
            background: rgba(30, 60, 114, 0.07);
            border-top: 1.5px dashed #660a18;
            border-bottom: 1.5px dashed #660a18;
            pointer-events: none;
            z-index: 3;
            align-items: center;
            justify-content: center;
            color: #660a18;
            font-weight: bold;
            font-size: 0.85em;
            letter-spacing: 0.5px;
            box-shadow: 0 0 10px rgba(30, 60, 114, 0.05);
        }
        
        .modal-overlay { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); align-items: center; justify-content: center; }
        .modal-content { background: white; width: 450px; padding: 30px; border-radius: 12px; color: #333; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .modal-content label { font-weight: bold; font-size: 0.9em; display: block; margin-top: 10px; }
        .modal-content input, .modal-content select { width: 100%; padding: 10px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .btn-acao { border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; flex: 1; }
        .menu { background: #380309; padding: 14px 25px; display: flex; gap: 25px; font-size: 1.1em; position: sticky; top: 0; z-index: 50; }
        .menu a { color: #fff; text-decoration: none; padding: 6px 12px; font-weight: bold; border-radius: 6px; }
        .menu a:hover { background: #660a18; }
    
/* ========== RESPONSIVIDADE (CELULARES) ========== */
@media (max-width: 768px) {
    .container {
        width: 95% !important;
        max-width: none !important;
        margin: 10px auto !important;
        padding: 10px !important;
    }
    .menu {
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 8px !important;
        padding: 10px 15px !important;
    }
    .menu a {
        font-size: 0.85em !important;
        padding: 6px 10px !important;
    }
    .logo {
        width: 100% !important;
        max-width: 250px !important;
        margin-bottom: -40px !important;
    }

    /* Header da agenda: empilhar verticalmente */
    .header-agenda {
        flex-direction: column !important;
        gap: 12px !important;
        padding: 10px !important;
        align-items: center !important;
    }

    /* Seletor de semana: scroll horizontal */
    .seletor-semana {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        flex-wrap: nowrap !important;
        max-width: 100% !important;
        padding-bottom: 4px !important;
    }
    .dia-item {
        min-width: 55px !important;
        padding: 6px 4px !important;
        font-size: 0.8em !important;
        flex-shrink: 0 !important;
    }

    /* Botões Anterior / Próxima */
    .btn-nav {
        padding: 6px 10px !important;
        font-size: 0.75em !important;
        white-space: nowrap !important;
    }

    /* Tabela da timeline com scroll */
    .tabela-wrapper {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        margin: 0 auto !important;
    }

    /* Modal responsivo */
    .modal-content {
        width: 90% !important;
        max-width: 400px !important;
        padding: 20px !important;
        margin: 0 auto !important;
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
       <div class="header-agenda">
            <div style="display: flex; align-items: center; gap: 10px; font-weight: bold; color: #660a18;">
                <a href="?data=<?= $mes_anterior->format('Y-m-01') ?>&mes=<?= $mes_anterior->format('n') ?>&ano=<?= $mes_anterior->format('Y') ?>" class="btn-nav">◀</a>
                <span><?= $data_obj_mes->format('m / Y') ?></span>
                <a href="?data=<?= $mes_proximo->format('Y-m-01') ?>&mes=<?= $mes_proximo->format('n') ?>&ano=<?= $mes_proximo->format('Y') ?>" class="btn-nav">▶</a>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="?data=<?= $data_selecionada ?>&offset_semana=-7" class="btn-nav">Anterior</a>
                <div class="seletor-semana">
                    <?php 
                    $temp_date = clone $data_base_semana;
                    for($i=0; $i<7; $i++): 
                        $curr = $temp_date->format('Y-m-d');
                        $dia_eng = date('D', strtotime($curr));
                        $dia_pt = $dias_curto_pt[$dia_eng]; 
                        $act = ($data_selecionada == $curr) ? 'active' : '';
                    ?>
                        <a href="?data=<?= $curr ?>" class="dia-item <?= $act ?>">
                            <small><?= $dia_pt ?></small>
                            <strong><?= date('d/m', strtotime($curr)) ?></strong>
                        </a>
                    <?php $temp_date->modify('+1 day'); endfor; ?>
                </div>
                <a href="?data=<?= $data_selecionada ?>&offset_semana=7" class="btn-nav">Próxima</a>
            </div>
        </div>

        <div class="tabela-wrapper">
            <?php if (!$esta_aberto): ?>
                <div style="text-align:center; padding:50px; color:#666;">
                    <h3>🚫 Barbearia Fechada</h3>
                    <p>Não há expediente cadastrado para <strong><?= $dia_semana_nome ?></strong>.</p>
                </div>
            <?php else: ?>
                <!-- HEADER DAS COLUNAS -->
                <div class="timeline-header-row">
                    <div class="timeline-header-col" style="background:#380309;">Hora</div>
                    <?php foreach ($profissionais as $p): ?>
                        <div class="timeline-header-col"><?= htmlspecialchars($p['nome']) ?></div>
                    <?php endforeach; ?>
                </div>

                <!-- CORPO DA LINHA DO TEMPO -->
                <div class="timeline-grid" id="timelineGrid">
                    <!-- Indicador de Hover (Visual de guia por horário) -->
                    <div id="timeline-hover-indicator" class="timeline-hover-indicator"></div>

                    <!-- Coluna de Horas -->
                    <div class="timeline-time-col">
                        <?php
                        $marker = clone $inicio_dt;
                        while ($marker <= $fim_dt) {
                            $m_val = (int)$marker->format('H') * 60 + (int)$marker->format('i');
                            $top_pos = ($m_val - $minutos_inicio);
                            $time_str = $marker->format('H:i');
                            $is_hour = ($marker->format('i') == '00');
                            ?>
                            <div class="timeline-time-marker" style="top: calc(<?= $top_pos ?> * var(--scale-factor));">
                                <?= $time_str ?>
                            </div>
                            <div class="timeline-time-line <?= $is_hour ? 'hour' : '' ?>" style="top: calc(<?= $top_pos ?> * var(--scale-factor));"></div>
                            <?php
                            $marker->modify('+30 minutes');
                        }
                        ?>
                    </div>

                    <!-- Colunas dos Profissionais -->
                    <?php foreach ($profissionais as $p): 
                        $nomeP = $p['nome'];
                        $idP = $p['id_profissional'];
                    ?>
                        <div class="timeline-prof-col" data-prof-id="<?= $idP ?>" onclick="lidarCliqueColuna(event, this)">
                            
                            <!-- Renderizar Bloqueios -->
                            <?php foreach (($bloqueios_por_profissional[$idP] ?? []) as $b): 
                                $b_ini = new DateTime($b['hora_inicio']);
                                $b_fim = new DateTime($b['hora_fim']);
                                $b_ini_min = (int)$b_ini->format('H') * 60 + (int)$b_ini->format('i');
                                $b_fim_min = (int)$b_fim->format('H') * 60 + (int)$b_fim->format('i');
                                
                                $top = $b_ini_min - $minutos_inicio;
                                $height = $b_fim_min - $b_ini_min;
                                if ($height > 0):
                            ?>
                                <div class="card-bloqueio-abs" 
                                     style="top: calc(<?= $top ?> * var(--scale-factor)); height: calc(<?= $height ?> * var(--scale-factor)); cursor: pointer;" 
                                     onclick="event.stopPropagation(); abrirModalEdicaoBloqueio('<?= $idP ?>', '<?= $data_selecionada ?>', '<?= $b_ini->format('H:i') ?>', '<?= $b_fim->format('H:i') ?>', '<?= addslashes($b['descricao']) ?>')"
                                     title="Clique para editar/excluir: <?= htmlspecialchars($b['descricao']) ?>">
                                    🔒 Bloqueado: <?= htmlspecialchars($b['descricao']) ?>
                                </div>
                            <?php endif; endforeach; ?>

                            <!-- Renderizar Almoço / Pausa -->
                            <?php if (!empty($p['almoco_inicio']) && !empty($p['almoco_fim'])): 
                                $alm_ini = new DateTime($p['almoco_inicio']);
                                $alm_fim = new DateTime($p['almoco_fim']);
                                $alm_ini_min = (int)$alm_ini->format('H') * 60 + (int)$alm_ini->format('i');
                                $alm_fim_min = (int)$alm_fim->format('H') * 60 + (int)$alm_fim->format('i');
                                
                                $top_alm = $alm_ini_min - $minutos_inicio;
                                $height_alm = $alm_fim_min - $alm_ini_min;
                                if ($height_alm > 0):
                            ?>
                                <div class="card-bloqueio-abs" style="top: calc(<?= $top_alm ?> * var(--scale-factor)); height: calc(<?= $height_alm ?> * var(--scale-factor)); background: repeating-linear-gradient(45deg, #f5f5f5, #f5f5f5 10px, #e3f2fd 10px, #e3f2fd 20px); border: 1px solid #90caf9; color: #1565c0; z-index: 4; display: flex; align-items: center; justify-content: center; font-weight: bold;" title="Intervalo de Almoço (<?= $alm_ini->format('H:i') ?> - <?= $alm_fim->format('H:i') ?>)">
                                    🍽️ Almoço (<?= $alm_ini->format('H:i') ?> - <?= $alm_fim->format('H:i') ?>)
                                </div>
                            <?php endif; endif; ?>

                            <!-- Renderizar Agendamentos -->
                            <?php foreach (($agendamentos_por_profissional[$nomeP] ?? []) as $ag): 
                                $ag_ini = new DateTime($ag['hora']);
                                $duracao = (int)($ag['duracao'] ?? 30);
                                $ag_ini_min = (int)$ag_ini->format('H') * 60 + (int)$ag_ini->format('i');
                                
                                $top = $ag_ini_min - $minutos_inicio;
                                $height = $duracao;
                                
                                $ag_fim = clone $ag_ini;
                                $ag_fim->modify("+$duracao minutes");

                                // Determinar cor dinâmica com base no serviço cadastrado pelo admin
                                $cor_servico = !empty($ag['cor']) ? $ag['cor'] : '#660a18';
                                $status_classe = strtolower($ag['status']);
                                
                                if ($status_classe === 'cancelado') {
                                    $card_style = "background: linear-gradient(135deg, #78909c, #b0bec5); border-left: 5px solid #37474f; opacity: 0.65;";
                                } else {
                                    $card_style = "background: " . $cor_servico . "; border-left: 5px solid rgba(0, 0, 0, 0.35); color: white;";
                                }
                            ?>
                                <div class="card-agendamento-abs <?= $status_classe ?>" 
                                     style="top: calc(<?= $top ?> * var(--scale-factor)); height: calc(<?= $height ?> * var(--scale-factor) - 2px); <?= $card_style ?>"
                                     onclick="event.stopPropagation(); abrirModalEdicao('<?= $ag['id_agenda'] ?>', '<?= $data_selecionada ?>', '<?= $ag_ini->format('H:i') ?>', '<?= addslashes($ag['cliente']) ?>', '<?= $ag['id_servico'] ?>', '<?= $ag['id_cliente'] ?? '' ?>', '<?= (int)($ag['pacote_id_uso'] ?? 0) ?>')">
                                    
                                    <?php if (!empty($ag['pacote_qtd_total'])): ?>
                                        <div class="badge-pacote-corner" title="Comanda/Pacote Aberto: <?= htmlspecialchars($ag['pacote_descricao']) ?> (<?= $ag['pacote_qtd_usada'] ?> de <?= $ag['pacote_qtd_total'] ?>)">
                                            <?= $ag['pacote_qtd_usada'] ?>/<?= $ag['pacote_qtd_total'] ?>
                                        </div>
                                    <?php endif; ?>

                                    <div style="font-weight: bold; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; <?= !empty($ag['pacote_qtd_total']) ? 'padding-right: 48px;' : '' ?>">
                                        <?= htmlspecialchars($ag['cliente']) ?>
                                    </div>
                                    <div style="font-size: 0.85em; opacity: 0.95; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; <?= !empty($ag['pacote_qtd_total']) ? 'padding-right: 48px;' : '' ?>">
                                        <?= htmlspecialchars($ag['servico']) ?>
                                    </div>
                                    <div style="font-size: 0.75em; opacity: 0.85; margin-top: auto; font-weight: 500;">
                                        <?= $ag_ini->format('H:i') ?> - <?= $ag_fim->format('H:i') ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="modalAgendamento" class="modal-overlay">
        <div class="modal-content">
            <h2 id="modal_titulo" style="margin-top:0; color:#660a18; text-align: center;">Novo Compromisso</h2>
            
            <div id="seletor_tipo" style="display: flex; gap: 20px; margin-bottom: 20px; justify-content: center; font-weight: bold;">
                <label style="cursor:pointer;"><input type="radio" name="tipo_compromisso" value="agendamento" checked onchange="alternarCamposModal(this.value)" style="width:auto; margin-right: 5px;"> Agendamento</label>
                <label style="cursor:pointer;"><input type="radio" name="tipo_compromisso" value="bloqueio" onchange="alternarCamposModal(this.value)" style="width:auto; margin-right: 5px;"> Bloqueio</label>
            </div>

            <form method="POST" action="salvar_compromisso_admin.php">
                <input type="hidden" name="id_profissional" id="modal_prof_id">
                <input type="hidden" name="tipo_final" id="modal_tipo_final" value="agendamento">

                <div style="display:flex; gap:10px;">
                    <div style="flex:1;"><label>Data:</label><input type="date" name="data" id="modal_data" readonly style="background:#f0f0f0;"></div>
                    <div style="flex:1;">
                        <label>Hora Início:</label>
                        <input type="time" name="hora_inicio" id="modal_hora" required>
                    </div>
                </div>

                <div id="container_hora_fim" style="display:none;">
                    <label>Hora Fim:</label>
                    <input type="time" name="hora_fim" id="modal_hora_fim">
                </div>

                <div id="campos_agendamento">
                    <label>Cliente:</label>
                    <input list="clientes_list" name="nome_cliente_completo" id="input_cliente" placeholder="Digite para buscar..." autocomplete="off">
                    <datalist id="clientes_list">
                        <?php foreach($lista_clientes as $c): ?>
                            <option value="<?= $c['nome'] ?> - <?= $c['celular'] ?>">
                        <?php endforeach; ?>
                    </datalist>

                    <!-- Alerta de comanda aberta no Novo Agendamento -->
                    <div id="box_notificacao_pacote_novo" class="card-alerta-comanda" style="display: none;">
                        <div class="comanda-alert-header">
                            🔔 Há comanda aberta para o cliente. Toque para vincular.
                        </div>
                        <div class="comanda-alert-body" onclick="toggleAbaterNovo()">
                            <span class="comanda-nome" id="novo_comanda_nome">pacote</span>
                            <span class="comanda-progresso" id="novo_comanda_progresso">0/0 total &rsaquo;</span>
                        </div>
                        <div class="comanda-alert-footer">
                            <label class="check-abater-label">
                                <input type="checkbox" name="abater_corte" id="novo_check_abater" value="1" style="width:18px; height:18px; margin:0;">
                                Abater 1 atendimento do pacote neste agendamento
                            </label>
                            <a href="admin_pacotes.php" target="_blank" class="btn-ou-nova-comanda">OU NOVA COMANDA</a>
                        </div>
                        <input type="hidden" name="id_pacote_vinculado" id="novo_id_pacote_vinculado" value="">
                    </div>
                    
                    <label>Serviço:</label>
                    <select name="id_servico" id="select_servico">
                        <?php foreach($lista_servicos as $s): ?>
                            <option value="<?= $s['id_servico'] ?>"><?= $s['nome'] ?> - R$ <?= $s['valor'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="campos_bloqueio" style="display:none;">
                    <label>Descrição:</label>
                    <input type="text" name="descricao_bloqueio" id="input_desc_bloqueio" placeholder="Almoço, Folga, Manutenção, etc.">
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn-acao" style="background:#28a745; color:white;">SALVAR</button>
                    <button type="button" onclick="fecharModal()" class="btn-acao" style="background:#666; color:white;">FECHAR</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEdicao" class="modal-overlay">
        <div class="modal-content">
            <h2 style="margin-top:0; color:#660a18;">Editar Agendamento</h2>
            <form method="POST" action="editar_agendamento.php" onsubmit="return validarEdicao()">
                <input type="hidden" name="id_agenda" id="edit_id">
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;"><label>Data:</label><input type="date" name="data" id="edit_data" required></div>
                    <div style="flex:1;"><label>Hora:</label><input type="time" name="hora" id="edit_hora" required></div>
                </div>
                <label>Cliente:</label>
                <input type="text" name="cliente" id="edit_cliente" readonly style="background:#f0f0f0;">

                <!-- Alerta de comanda aberta no Editar Agendamento -->
                <div id="box_notificacao_pacote_edit" class="card-alerta-comanda" style="display: none;">
                    <div class="comanda-alert-header">
                        🔔 Há comanda aberta para o cliente. Toque para vincular.
                    </div>
                    <div class="comanda-alert-body" onclick="toggleAbaterEdit()">
                        <span class="comanda-nome" id="edit_comanda_nome">pacote</span>
                        <span class="comanda-progresso" id="edit_comanda_progresso">0/0 total &rsaquo;</span>
                    </div>
                    <div class="comanda-alert-footer">
                        <label class="check-abater-label">
                            <input type="checkbox" name="abater_corte" id="edit_check_abater" value="1" style="width:18px; height:18px; margin:0;">
                            <span id="edit_check_abater_text">Abater 1 atendimento do pacote neste agendamento</span>
                        </label>
                        <a href="admin_pacotes.php" target="_blank" class="btn-ou-nova-comanda">OU NOVA COMANDA</a>
                    </div>
                    <input type="hidden" name="id_pacote_vinculado" id="edit_id_pacote_vinculado" value="">
                </div>

                <label>Serviço:</label>
                <select name="id_servico" id="edit_servico" required>
                    <option value="">-- Selecione um serviço --</option>
                    <?php foreach($lista_servicos as $s): ?>
                        <option value="<?= $s['id_servico'] ?>"><?= $s['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn-acao" style="background:#ffc107; color:#333;">ATUALIZAR</button>
                    <button type="button" onclick="excluirNoModal()" class="btn-acao" style="background:#dc3545; color:white;">EXCLUIR</button>
                    <button type="button" onclick="fecharModalEdicao()" class="btn-acao" style="background:#666; color:white;">FECHAR</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDICAO/EXCLUSAO DE BLOQUEIO DE HORARIO -->
    <div id="modalEdicaoBloqueio" class="modal-overlay">
        <div class="modal-content">
            <h2 style="margin-top:0; color:#660a18; text-align: center;">Editar Bloqueio</h2>
            <form method="POST" action="atualizar_bloqueio_admin.php">
                <input type="hidden" name="id_profissional" id="edit_bloqueio_prof_id">
                <input type="hidden" name="data" id="edit_bloqueio_data">
                <input type="hidden" name="hora_inicio" id="edit_bloqueio_hora_ini">
                
                <label>Data:</label>
                <input type="text" id="edit_bloqueio_data_ver" readonly style="background:#f0f0f0;">
                
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>Hora Início:</label>
                        <input type="time" id="edit_bloqueio_hora_ini_ver" readonly style="background:#f0f0f0;">
                    </div>
                    <div style="flex:1;">
                        <label>Hora Fim:</label>
                        <input type="time" name="hora_fim" id="edit_bloqueio_hora_fim" required>
                    </div>
                </div>
                
                <label>Descrição:</label>
                <input type="text" name="descricao" id="edit_bloqueio_desc" required>
                
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn-acao" style="background:#ffc107; color:#333;">ATUALIZAR</button>
                    <button type="button" onclick="excluirBloqueioNoModal()" class="btn-acao" style="background:#dc3545; color:white;">EXCLUIR</button>
                    <button type="button" onclick="fecharModalEdicaoBloqueio()" class="btn-acao" style="background:#666; color:white;">FECHAR</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Captura cliques vazios na coluna do profissional para criar agendamento no horário exato
    function lidarCliqueColuna(event, el) {
        if (event.target !== el) return;
        
        const rect = el.getBoundingClientRect();
        const offsetY = event.clientY - rect.top;
        
        // Obtém o fator de escala definido no CSS
        const rootStyle = getComputedStyle(document.documentElement);
        const scaleStr = rootStyle.getPropertyValue('--scale-factor') || '1.8px';
        const scale = parseFloat(scaleStr);
        
        const clickedMinute = Math.floor(offsetY / scale);
        const startMinutes = <?= $minutos_inicio ?>;
        let totalMinutes = startMinutes + clickedMinute;
        
        // Snap para os 5 minutos mais próximos
        totalMinutes = Math.round(totalMinutes / 5) * 5;
        
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        
        const formattedTime = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        const profId = el.getAttribute('data-prof-id');
        const data = '<?= $data_selecionada ?>';
        
        abrirModal(data, formattedTime, profId);
    }

    // Gerenciador do indicador visual de Hover (guia de meia em meia hora para o admin)
    const timelineGrid = document.getElementById('timelineGrid');
    const hoverIndicator = document.getElementById('timeline-hover-indicator');
    
    if (timelineGrid && hoverIndicator) {
        const rootStyle = getComputedStyle(document.documentElement);
        const scaleStr = rootStyle.getPropertyValue('--scale-factor') || '1.8px';
        const scale = parseFloat(scaleStr);
        const startMinutes = <?= $minutos_inicio ?>;
        
        timelineGrid.addEventListener('mousemove', function(event) {
            const profCol = event.target.closest('.timeline-prof-col');
            if (!profCol) {
                hoverIndicator.style.display = 'none';
                return;
            }
            
            const rect = profCol.getBoundingClientRect();
            const offsetY = event.clientY - rect.top;
            
            const clickedMinute = Math.floor(offsetY / scale);
            // Snap para blocos de 30 minutos
            const snappedBlock = Math.floor(clickedMinute / 30);
            const snappedMinutes = startMinutes + snappedBlock * 30;
            
            const topPos = (snappedMinutes - startMinutes) * scale;
            const heightPos = 30 * scale; // 30 minutos de altura
            
            const hours = Math.floor(snappedMinutes / 60);
            const minutes = snappedMinutes % 60;
            const formattedTime = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
            
            hoverIndicator.style.top = topPos + 'px';
            hoverIndicator.style.height = heightPos + 'px';
            hoverIndicator.style.display = 'flex';
            hoverIndicator.innerText = formattedTime;
        });
        
        timelineGrid.addEventListener('mouseleave', function() {
            hoverIndicator.style.display = 'none';
        });
    }

    function alternarCamposModal(tipo) {
        document.getElementById('modal_tipo_final').value = tipo;
        const isBloqueio = (tipo === 'bloqueio');
        
        document.getElementById('container_hora_fim').style.display = isBloqueio ? 'block' : 'none';
        document.getElementById('campos_agendamento').style.display = isBloqueio ? 'none' : 'block';
        document.getElementById('campos_bloqueio').style.display = isBloqueio ? 'block' : 'none';
        
        document.getElementById('modal_hora_fim').required = isBloqueio;
        document.getElementById('input_cliente').required = !isBloqueio;
        document.getElementById('select_servico').required = !isBloqueio;
    }

    // Dados de pacotes abertos
    const pacotesAbertos = <?= json_encode($todosPacotesAbertos) ?>;

    function buscarPacoteDoCliente(identificador) {
        if (!identificador) return null;
        const texto = String(identificador).toLowerCase().trim();
        return pacotesAbertos.find(p => {
            if (p.id_cliente == identificador) return true;
            const nomeP = p.cliente_nome.toLowerCase().trim();
            return texto === nomeP || texto.startsWith(nomeP) || nomeP.startsWith(texto);
        }) || null;
    }

    function verificarPacoteNovoAgendamento(valor) {
        const box = document.getElementById('box_notificacao_pacote_novo');
        if (!valor || valor.trim() === '') {
            box.style.display = 'none';
            document.getElementById('novo_id_pacote_vinculado').value = '';
            document.getElementById('novo_check_abater').checked = false;
            return;
        }

        const partes = valor.split(' - ');
        const nomeCliente = partes[0].trim();
        const pacote = buscarPacoteDoCliente(nomeCliente);

        if (pacote) {
            document.getElementById('novo_comanda_nome').innerText = pacote.descricao;
            document.getElementById('novo_comanda_progresso').innerText = pacote.qtd_usada + '/' + pacote.qtd_total + ' total ›';
            document.getElementById('novo_id_pacote_vinculado').value = pacote.id_pacote;
            document.getElementById('novo_check_abater').checked = true;
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
            document.getElementById('novo_id_pacote_vinculado').value = '';
            document.getElementById('novo_check_abater').checked = false;
        }
    }

    function toggleAbaterNovo() {
        const chk = document.getElementById('novo_check_abater');
        chk.checked = !chk.checked;
    }

    function toggleAbaterEdit() {
        const chk = document.getElementById('edit_check_abater');
        if (chk.disabled) return;
        chk.checked = !chk.checked;
    }

    const inputCli = document.getElementById('input_cliente');
    if (inputCli) {
        inputCli.addEventListener('input', function() {
            verificarPacoteNovoAgendamento(this.value);
        });
        inputCli.addEventListener('change', function() {
            verificarPacoteNovoAgendamento(this.value);
        });
    }

    // Função para abrir o modal de novo agendamento
    function abrirModal(data, hora, profId) {
        document.getElementById('modal_data').value = data;
        document.getElementById('modal_hora').value = hora;
        document.getElementById('modal_prof_id').value = profId;
        
        // Resetar para "agendamento" ao abrir
        alternarCamposModal('agendamento');
        document.querySelector('input[name="tipo_compromisso"][value="agendamento"]').checked = true;
        
        document.getElementById('input_cliente').value = '';
        verificarPacoteNovoAgendamento('');

        document.getElementById('modalAgendamento').style.display = 'flex';
    }

    // Função para fechar o modal
    function fecharModal() { 
        document.getElementById('modalAgendamento').style.display = 'none'; 
    }

    // Função para abrir o modal de edição
    function abrirModalEdicao(id, data, hora, cliente, servicoId, idCliente, pacoteJaUsado) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_data').value = data;
        document.getElementById('edit_hora').value = hora;
        document.getElementById('edit_cliente').value = cliente;
        document.getElementById('edit_servico').value = servicoId;

        // Verifica se o cliente tem pacote aberto para exibir alerta
        const boxEdit = document.getElementById('box_notificacao_pacote_edit');
        const pacote = buscarPacoteDoCliente(idCliente || cliente);

        const checkAbaterEdit = document.getElementById('edit_check_abater');
        const textoAbaterEdit = document.getElementById('edit_check_abater_text');

        if (pacoteJaUsado && parseInt(pacoteJaUsado, 10) > 0) {
            const pacoteUsadoAberto = pacote && String(pacote.id_pacote) === String(pacoteJaUsado) ? pacote : null;
            document.getElementById('edit_comanda_nome').innerText = pacoteUsadoAberto ? pacoteUsadoAberto.descricao : 'Atendimento já abatido';
            document.getElementById('edit_comanda_progresso').innerText = pacoteUsadoAberto ? pacoteUsadoAberto.qtd_usada + '/' + pacoteUsadoAberto.qtd_total + ' total ›' : 'Este horário já foi utilizado';
            document.getElementById('edit_id_pacote_vinculado').value = pacoteJaUsado;
            checkAbaterEdit.checked = true;
            checkAbaterEdit.disabled = true;
            textoAbaterEdit.innerText = 'Este atendimento já foi abatido';
            boxEdit.style.display = 'block';
        } else if (pacote) {
            document.getElementById('edit_comanda_nome').innerText = pacote.descricao;
            document.getElementById('edit_comanda_progresso').innerText = pacote.qtd_usada + '/' + pacote.qtd_total + ' total ›';
            document.getElementById('edit_id_pacote_vinculado').value = pacote.id_pacote;
            checkAbaterEdit.checked = false; // Admin decide se quer abater
            checkAbaterEdit.disabled = false;
            textoAbaterEdit.innerText = 'Abater 1 atendimento do pacote neste agendamento';
            boxEdit.style.display = 'block';
        } else {
            boxEdit.style.display = 'none';
            document.getElementById('edit_id_pacote_vinculado').value = '';
            checkAbaterEdit.checked = false;
            checkAbaterEdit.disabled = false;
            textoAbaterEdit.innerText = 'Abater 1 atendimento do pacote neste agendamento';
        }

        document.getElementById('modalEdicao').style.display = 'flex';
    }

    function fecharModalEdicao() { 
        document.getElementById('modalEdicao').style.display = 'none'; 
    }

    function validarEdicao() {
        const data = document.getElementById('edit_data').value;
        const hora = document.getElementById('edit_hora').value;
        const servico = document.getElementById('edit_servico').value;
        if (!data || !hora || servico === "" || servico === "0") {
            alert("Atenção: A data, a hora e o serviço são obrigatórios!");
            return false;
        }
        return true;
    }

    function excluirNoModal() {
        const id = document.getElementById('edit_id').value;
        if(confirm("Tem certeza que deseja excluir este agendamento?")) {
            window.location.href = "excluir_agendamento.php?id=" + id;
        }
    }

    // Fecha os modais ao clicar fora deles
    window.onclick = function(event) {
        if (event.target.className === 'modal-overlay') {
            fecharModal();
            fecharModalEdicao();
        }
    }
    // Função para abrir o modal de edição de bloqueio
    function abrirModalEdicaoBloqueio(idProf, data, horaIni, horaFim, descricao) {
        document.getElementById('edit_bloqueio_prof_id').value = idProf;
        document.getElementById('edit_bloqueio_data').value = data;
        document.getElementById('edit_bloqueio_hora_ini').value = horaIni;
        document.getElementById('edit_bloqueio_hora_fim').value = horaFim;
        document.getElementById('edit_bloqueio_desc').value = descricao;
        // Populate read‑only display fields
        document.getElementById('edit_bloqueio_data_ver').value = data;
        document.getElementById('edit_bloqueio_hora_ini_ver').value = horaIni;
        // Mostra o modal
        document.getElementById('modalEdicaoBloqueio').style.display = 'flex';
    }

    // Função para fechar o modal de edição de bloqueio
    function fecharModalEdicaoBloqueio() {
        document.getElementById('modalEdicaoBloqueio').style.display = 'none';
    }

    // Função para excluir bloqueio a partir do modal
    function excluirBloqueioNoModal() {
        const idProf = document.getElementById('edit_bloqueio_prof_id').value;
        const data = document.getElementById('edit_bloqueio_data').value;
        const horaIni = document.getElementById('edit_bloqueio_hora_ini').value;
        if (confirm('Tem certeza que deseja excluir este bloqueio?')) {
            window.location.href = 'excluir_bloqueio.php?prof=' + encodeURIComponent(idProf) + '&data=' + encodeURIComponent(data) + '&hora=' + encodeURIComponent(horaIni);
        }
    }

    </script>
    <?php if (!empty($_GET['msg'])): ?>
        <script>alert(<?= json_encode($_GET['msg'], JSON_UNESCAPED_UNICODE) ?>);</script>
    <?php endif; ?>
</body>
</html>