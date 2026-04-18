<?php
session_start();

if (!isset($_SESSION['profissional_id'])) {
    header("Location: ../admin/profissional_login.php?erro=login");
    exit;
}

require_once("../../backend/profissional.php");
require_once("../../backend/cliente.php"); 
require_once("../../backend/agenda.php");
require_once("../../backend/conexao.php");

$id_profissional = $_SESSION['profissional_id'];
$data_selecionada = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

// Lógica de horários de funcionamento
$dia_semana_num = date('N', strtotime($data_selecionada)); 
$map_dias = [
    1 => 'SEGUNDA', 2 => 'TERCA', 3 => 'QUARTA', 
    4 => 'QUINTA', 5 => 'SEXTA', 6 => 'SABADO', 7 => 'DOMINGO'
];
$dia_nome = $map_dias[$dia_semana_num];

$stmt_func = $pdo->prepare("SELECT abre, fecha, ativo FROM funcionamento WHERE dia_semana = ?");
$stmt_func->execute([$dia_nome]);
$config = $stmt_func->fetch(PDO::FETCH_ASSOC);

$hora_inicio = ($config) ? (int)substr($config['abre'], 0, 2) : 8;
$hora_fim = ($config) ? (int)substr($config['fecha'], 0, 2) : 18;
$esta_aberto = ($config && $config['ativo'] == 1);

// Buscas no Banco
$agenda = listarAgendaProfissional($id_profissional, $data_selecionada);
$lista_clientes = listarClientes(); 
$lista_servicos = listarServicos(); 

$sql_bloqueios = "SELECT hora_inicio, hora_fim, descricao FROM bloqueio_agenda 
                  WHERE id_profissional = :prof AND data = :data";
$stmt_b = $pdo->prepare($sql_bloqueios);
$stmt_b->execute([':prof' => $id_profissional, ':data' => $data_selecionada]);
$bloqueios_dia = $stmt_b->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Agenda - <?= date('d/m', strtotime($data_selecionada)) ?></title>
    <style>
        body { background: linear-gradient(135deg, #1e3c72, #2a5298); font-family: sans-serif; margin: 0; color: #fff; }
        .menu { background: #0d2a52; padding: 14px 25px; display: flex; gap: 25px; font-size: 1.1em; position: sticky; top: 0; z-index: 50; }
        .menu a { color: #fff; text-decoration: none; padding: 6px 12px; font-weight: bold; border-radius: 6px; }
        .menu a:hover { background: #1e3c72; }
        
        .container { background: white; max-width: 900px; margin: 30px auto; padding: 25px; color: #333; border-radius: 12px; }
        .filtro-data { display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #dee2e6; }
        .agenda-grid { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .linha-agenda { display: flex; border-bottom: 1px solid #eee; min-height: 65px; }
        .hora-col { width: 80px; background: #f1f4f8; font-weight: bold; display: flex; align-items: center; justify-content: center; border-right: 1px solid #ddd; }
        .evento-col { flex-grow: 1; padding: 5px 10px; display: flex; align-items: center; }
        
        .card-agendado { background: #1a73e8; color: white; padding: 10px; border-radius: 6px; border-left: 6px solid #0d47a1; transition: 0.3s; width: 100%; cursor: pointer; }
        .card-bloqueado { background: #dc3545; color: white; padding: 10px; border-radius: 6px; border-left: 6px solid #a71d2a; width: 100%; font-weight: bold; cursor: pointer; }
        .card-bloqueado:hover { background: #bd2130; }
        
        .btn-vazio { width: 100%; text-decoration: none; color: #bbb; padding: 15px; display: block; cursor: pointer; }
        .btn-vazio:hover { background: #f0f7ff; color: #1e3c72; }

        /* MODAL */
        .modal-overlay { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); align-items: center; justify-content: center; }
        .modal-content { background: white; width: 450px; padding: 30px; border-radius: 12px; color: #333; }
        .modal-content input, .modal-content select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .btn-group { display: flex; gap: 10px; margin-top: 25px; }
        .btn-acao { border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; flex: 1; text-transform: uppercase; }
        .btn-salvar { background: #28a745; color: white; }
        .btn-excluir { background: #dc3545; color: white; }
        .btn-cancelar { background: #6c757d; color: white; }
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
        <form method="GET">
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
                $tempo_grid_segundos = strtotime($h_grid);

                // Busca Agendamento
                $agendamento = null;
                foreach ($agenda as $a) {
                    if (date('H:i', strtotime($a['hora'])) == $h_grid) {
                        $agendamento = $a;
                        break;
                    }
                }

                // Busca Bloqueio
                $bloqueio_ativo = null;
                foreach ($bloqueios_dia as $b) {
                    $b_ini = strtotime($b['hora_inicio']);
                    $b_fim = strtotime($b['hora_fim']);
                    if ($tempo_grid_segundos >= $b_ini && $tempo_grid_segundos < $b_fim) {
                        $bloqueio_ativo = $b;
                        break;
                    }
                }
        ?>
            <div class="linha-agenda">
                <div class="hora-col"><?= $h_grid ?></div>
                <div class="evento-col">
                    <?php if ($bloqueio_ativo): ?>
                        <div class="card-bloqueado" onclick="editarBloqueio('<?= $data_selecionada ?>', '<?= $h_grid ?>', '<?= substr($bloqueio_ativo['hora_fim'],0,5) ?>', '<?= htmlspecialchars($bloqueio_ativo['descricao']) ?>')">
                            🚫 <?= htmlspecialchars($bloqueio_ativo['descricao']) ?>
                        </div>
                    <?php elseif ($agendamento): ?>
                        <div class="card-agendado" onclick="alert('Editar ID: <?= $agendamento['id_agenda'] ?>')">
                            <strong><?= htmlspecialchars($agendamento['cliente']) ?></strong> | <?= htmlspecialchars($agendamento['servico']) ?>
                        </div>
                    <?php else: ?>
                        <div class="btn-vazio" onclick="abrirModal('<?= $data_selecionada ?>', '<?= $h_grid ?>')">
                            + Disponível
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php } } ?>
        </div>
    <?php endif; ?>
</div>

<div id="modalNovoAgendamento" class="modal-overlay">
    <div class="modal-content">
        <h2 id="modal_titulo" style="margin-top:0; color:#1e3c72;">Novo Compromisso</h2>
        
        <div id="seletor_tipo" style="display: flex; gap: 20px; margin-bottom: 20px; justify-content: center; font-weight: bold;">
            <label style="cursor:pointer;"><input type="radio" name="tipo_compromisso" value="agendamento" checked onchange="alternarCamposModal(this.value)" style="width:auto;"> Agendamento</label>
            <label style="cursor:pointer;"><input type="radio" name="tipo_compromisso" value="bloqueio" onchange="alternarCamposModal(this.value)" style="width:auto;"> Bloqueio</label>
        </div>

        <form method="POST" action="salvar_compromisso.php">
            <input type="hidden" name="id_profissional" value="<?= $id_profissional ?>">
            <input type="hidden" name="tipo_final" id="modal_tipo_final" value="agendamento">

            <label>Data:</label>
            <input type="date" name="data" id="modal_data" required readonly style="background:#f0f0f0;">

            <div style="display:flex; gap:10px;">
                <div style="flex:1;">
                    <label id="label_hora_inicio">Hora Início:</label>
                    <input type="time" name="hora_inicio" id="modal_hora_inicio" required readonly style="background:#f0f0f0;">
                </div>
                <div style="flex:1; display:none;" id="container_hora_fim">
                    <label>Hora Fim:</label>
                    <input type="time" name="hora_fim" id="modal_hora_fim">
                </div>
            </div>

            <div id="campos_agendamento">
                <label>Cliente:</label>
                <input list="clientes_list" name="nome_cliente_completo" id="input_cliente" placeholder="Digite o nome...">
                <datalist id="clientes_list">
                    <?php foreach($lista_clientes as $c): ?>
                        <option value="<?= $c['nome'] ?> - <?= $c['celular'] ?>">
                    <?php endforeach; ?>
                </datalist>

                <label>Serviço:</label>
                <select name="id_servico" id="select_servico">
                    <option value="">Selecione...</option>
                    <?php foreach($lista_servicos as $s): ?>
                        <option value="<?= $s['id_servico'] ?>"><?= $s['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="campos_bloqueio" style="display:none;">
                <label>Descrição:</label>
                <input type="text" name="descricao_bloqueio" id="input_desc_bloqueio" placeholder="Almoço, Folga, etc.">
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-acao btn-salvar">Salvar</button>
                <button type="button" id="btn_excluir_bloqueio" class="btn-acao btn-excluir" style="display:none;" onclick="confirmarExcluirBloqueio()">Excluir</button>
                <button type="button" class="btn-acao btn-cancelar" onclick="fecharModal()">Fechar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModal(data, hora) {
        document.getElementById('modal_titulo').innerText = "Novo Compromisso";
        document.getElementById('modal_data').value = data;
        document.getElementById('modal_hora_inicio').value = hora;
        document.getElementById('btn_excluir_bloqueio').style.display = 'none';
        document.getElementById('seletor_tipo').style.display = 'flex';
        
        alternarCamposModal('agendamento');
        document.querySelector('input[name="tipo_compromisso"][value="agendamento"]').checked = true;
        document.getElementById('modalNovoAgendamento').style.display = 'flex';
    }

    function editarBloqueio(data, horaIni, horaFim, desc) {
        document.getElementById('modal_titulo').innerText = "Editar Bloqueio";
        document.getElementById('modal_data').value = data;
        document.getElementById('modal_hora_inicio').value = horaIni;
        document.getElementById('modal_hora_fim').value = horaFim;
        document.getElementById('input_desc_bloqueio').value = desc;
        
        document.getElementById('btn_excluir_bloqueio').style.display = 'block';
        document.getElementById('seletor_tipo').style.display = 'none'; // Esconde seletor na edição
        
        alternarCamposModal('bloqueio');
        document.getElementById('modalNovoAgendamento').style.display = 'flex';
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

    function fecharModal() { document.getElementById('modalNovoAgendamento').style.display = 'none'; }

    function confirmarExcluirBloqueio() {
        if (confirm("Remover este bloqueio?")) {
            const d = document.getElementById('modal_data').value;
            const h = document.getElementById('modal_hora_inicio').value;
            window.location.href = `excluir_bloqueio.php?prof=<?= $id_profissional ?>&data=${d}&hora=${h}`;
        }
    }
</script>
</body>
</html>