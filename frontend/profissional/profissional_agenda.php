<?php
session_start();

if (!isset($_SESSION['profissional_id'])) {
    header("Location: ../admin/profissional_login.php?erro=login");
    exit;
}

require_once("../../backend/profissional.php");
require_once("../../backend/conexao.php");

$id_profissional = $_SESSION['profissional_id'];

// --- NOVA LÓGICA DE DATA ---
// Se houver data na URL, usa ela. Se não, usa HOJE.
$data_selecionada = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

$dia_semana_num = date('N', strtotime($data_selecionada)); 
$map_dias = [
    1 => 'SEGUNDA', 2 => 'TERCA', 3 => 'QUARTA', 
    4 => 'QUINTA', 5 => 'SEXTA', 6 => 'SABADO', 7 => 'DOMINGO'
];
$dia_nome = $map_dias[$dia_semana_num];

// Busca funcionamento para o dia selecionado
$stmt_func = $pdo->prepare("SELECT abre, fecha, ativo FROM funcionamento WHERE dia_semana = ?");
$stmt_func->execute([$dia_nome]);
$config = $stmt_func->fetch(PDO::FETCH_ASSOC);

$hora_inicio = ($config) ? (int)substr($config['abre'], 0, 2) : 8;
$hora_fim = ($config) ? (int)substr($config['fecha'], 0, 2) : 18;
$esta_aberto = ($config && $config['ativo'] == 1);

// BUSCAR AGENDA FILTRADA POR DATA (ajuste sua função para aceitar a data)
// Exemplo: listarAgendaProfissional($id, $data)
$agenda = listarAgendaProfissional($id_profissional, $data_selecionada);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Agenda - <?= date('d/m', strtotime($data_selecionada)) ?></title>
    <style>
        /* Reutilizando seu CSS anterior com ajustes */
        body { background: linear-gradient(135deg, #1e3c72, #2a5298); font-family: sans-serif; margin: 0; color: #fff; }
        .menu { background: #0d2a52; padding: 15px; display: flex; gap: 20px; }
        .menu a { color: white; text-decoration: none; font-weight: bold; }
        
        .container { background: white; max-width: 900px; margin: 30px auto; padding: 25px; color: #333; border-radius: 12px; }
        
        /* BARRA DE FILTRO */
        .filtro-data {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .filtro-data input[type="date"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1em;
        }

        .agenda-grid { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .linha-agenda { display: flex; border-bottom: 1px solid #eee; min-height: 65px; }
        .hora-col { width: 80px; background: #f1f4f8; font-weight: bold; display: flex; align-items: center; justify-content: center; border-right: 1px solid #ddd; }
        .evento-col { flex-grow: 1; padding: 5px 10px; display: flex; align-items: center; }

        /* CARD CLICÁVEL PARA EDITAR */
        .card-link { text-decoration: none; width: 100%; display: block; }
        .card-agendado {
            background: #1a73e8; color: white; padding: 10px; border-radius: 6px;
            border-left: 6px solid #0d47a1; transition: 0.3s;
        }
        .card-agendado:hover { transform: scale(1.01); filter: brightness(1.1); }

        .btn-vazio { width: 100%; text-decoration: none; color: #bbb; padding: 15px; display: block; }
        .btn-vazio:hover { background: #f0f7ff; color: #1e3c72; }
        
        .status-confirmado { background: #28a745; border-left-color: #1e7e34; }
        .status-pendente { background: #f39c12; border-left-color: #d35400; }
    </style>
</head>
<body>

<div class="menu">
    <a href="profissional_home.php">🏠 Home</a>
    <a href="profissional_agenda.php">📅 Minha Agenda</a>
    <a href="profissional_perfil.php">👤 Meu Perfil</a>
    <a href="profissional_estatistica.php">📊 Estatísticas</a>
    <a href="profissional_agendar.php">➕ Novo Agendamento</a>
    <a href="profissional_login.php">🚪 Sair</a>
</div>

<div class="container">
    <h1>📅 Agenda Profissional</h1>

    <div class="filtro-data">
        <span>Visualizando: <strong><?= date('d/m/Y', strtotime($data_selecionada)) ?></strong></span>
        <form method="GET" action="profissional_agenda.php">
            <label>Alterar dia: </label>
            <input type="date" name="data" value="<?= $data_selecionada ?>" onchange="this.form.submit()">
        </form>
    </div>

    <?php if (!$esta_aberto): ?>
        <div style="text-align:center; padding: 30px; color: #e74c3c;">
            <h3>🚫 Barbearia fechada nesta <?= strtolower($dia_nome) ?>.</h3>
        </div>
    <?php else: ?>
        
        <div class="agenda-grid">
            <?php
            $intervalo = 30;
            for ($hora = $hora_inicio; $hora < $hora_fim; $hora++) {
                for ($min = 0; $min < 60; $min += $intervalo) {
                    $h_grid = sprintf('%02d:%02d', $hora, $min);
                    
                    $agendamento = null;
                    foreach ($agenda as $a) {
                        if (date('H:i', strtotime($a['hora'])) == $h_grid) {
                            $agendamento = $a;
                            break;
                        }
                    }
            ?>
                <div class="linha-agenda">
                    <div class="hora-col"><?= $h_grid ?></div>
                    <div class="evento-col">
                        <?php if ($agendamento): ?>
                            <a href="profissional_editar_agendamento.php?id=<?= $agendamento['id_agenda'] ?>" class="card-link">
                                <div class="card-agendado status-<?= strtolower($agendamento['status']) ?>">
                                    <strong><?= $agendamento['cliente'] ?></strong> | <?= $agendamento['servico'] ?>
                                    <div style="font-size: 0.8em; margin-top: 3px;">✏️ Clique para alterar</div>
                                </div>
                            </a>
                        <?php else: ?>
                            <a href="profissional_agendar.php?data=<?= $data_selecionada ?>&hora=<?= $h_grid ?>:00" class="btn-vazio">
                                + Disponível
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php
                }
            }
            ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>