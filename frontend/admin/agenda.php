<?php
require_once("../../backend/admin.php");
require_once("../../backend/cliente.php");
require_once("../../backend/profissional.php");
require_once("../../backend/agenda.php");
require_once("../../backend/conexao.php"); 
session_start();

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

$data_obj_mes = DateTime::createFromFormat('Y-n-j', "$ano_get-$mes_get-1");
$mes_anterior = (clone $data_obj_mes)->modify('-1 month');
$mes_proximo = (clone $data_obj_mes)->modify('+1 month');

$profissionais = listarProfissionais();
$agendamentos_dia = listarAgendamentosDoDia($data_selecionada);
$lista_clientes = listarClientes(); 
$lista_servicos = listarServicos(); 

$grade_organizada = [];
foreach ($agendamentos_dia as $ag) {
    $h = date('H:i', strtotime($ag['hora']));
    $grade_organizada[$h][$ag['profissional']] = $ag;
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
// ... (seu código inicial de agendamentos) ...
$agendamentos_dia = listarAgendamentosDoDia($data_selecionada);

// NOVA BUSCA: Bloqueios para a grade administrativa
$sql_b = "SELECT id_profissional, hora_inicio, hora_fim, descricao FROM bloqueio_agenda WHERE data = :data";
$stmt_b = $pdo->prepare($sql_b);
$stmt_b->execute([':data' => $data_selecionada]);
$bloqueios_dia = $stmt_b->fetchAll(PDO::FETCH_ASSOC);

// ... (resto da lógica de tradução e funcionamento) ...
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Agenda Master 💈</title>
    <style> 
        body { background: #1e3c72; font-family: 'Segoe UI', sans-serif; color: #fff; margin: 0; }
        .container { background: white; color: #333; margin: 20px auto; max-width: 1200px; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .header-agenda { display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd; }
        .btn-nav { background: #0d2a52; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .seletor-semana { display: flex; gap: 5px; }
        .dia-item { padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 5px; text-align: center; min-width: 80px; text-decoration: none; color: #333; }
        .dia-item.active { background: #1e3c72; color: white; transform: scale(1.05); }
        .tabela-wrapper { overflow-x: auto; border: 1px solid #ddd; border-radius: 8px; }
        .tabela-grade { width: 100%; border-collapse: collapse; min-width: 700px; }
        .tabela-grade th { background: #1e3c72; color: white; padding: 12px; border: 1px solid #0d2a52; }
        .tabela-grade td { border: 1px solid #eee; height: 55px; vertical-align: middle; text-align: center; }
        .col-hora { width: 80px; font-weight: bold; background: #f8f9fa; color: #666; border-right: 2px solid #ddd !important; }
        .card-agendamento { padding: 5px; border-radius: 3px; font-size: 0.8em; text-align: left; margin: 2px; transition: 0.2s; border-left: 4px solid #1e3c72; }
        .card-agendamento.pendente { background: #fff3e0; border-left-color: #ff9800; color: #855600; }
        .card-agendamento.confirmado { background: #e8f5e9; border-left-color: #2e7d32; color: #1b5e20; }
        .card-agendamento.cancelado { background: #ffebee; border-left-color: #c62828; opacity: 0.6; }
        .btn-vazio { display: flex; justify-content: center; align-items: center; width: 100%; height: 100%; text-decoration: none; color: #ccc; font-size: 1.5em; }
        .modal-overlay { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); align-items: center; justify-content: center; }
        .modal-content { background: white; width: 450px; padding: 30px; border-radius: 12px; color: #333; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .modal-content label { font-weight: bold; font-size: 0.9em; display: block; margin-top: 10px; }
        .modal-content input, .modal-content select { width: 100%; padding: 10px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .btn-acao { border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; flex: 1; }
        .menu { background: #0d2a52; padding: 14px 25px; display: flex; gap: 25px; font-size: 1.1em; position: sticky; top: 0; z-index: 50; }
        .menu a { color: #fff; text-decoration: none; padding: 6px 12px; font-weight: bold; border-radius: 6px; }
        .menu a:hover { background: #1e3c72; }
    </style>
</head>
<body>
    <div class="menu">
        <a href="agenda.php">📅 Agenda</a>
        <a href="financeiro.php">💰 Financeiro</a>
        <a href="admin_profissionais.php">👤 Profissionais</a>
        <a href="admin_servicos.php">💈 Serviços</a>
        <a href="admin_funcionamento.php">⏰ Funcionamento</a>
        <a href="login_admin.php">🚪 Sair</a>
    </div>

    <div class="container">
       <div class="header-agenda">
            <div style="display: flex; align-items: center; gap: 10px; font-weight: bold; color: #1e3c72;">
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
                        $dia_pt = $dias_curto_pt[$dia_eng]; // Tradução aqui
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
                <table class="tabela-grade">
                    <thead>
                        <tr>
                            <th class="col-hora">Hora</th>
                            <?php foreach ($profissionais as $p): ?>
                                <th><?= $p['nome'] ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $inicio = new DateTime($dados_func['abre']);
                        $fim = new DateTime($dados_func['fecha']);
                        while ($inicio < $fim):
                            $h_f = $inicio->format('H:i');
                        ?>
                        <tr>
                            <td class="col-hora"><?= $h_f ?></td>
                            <?php foreach ($profissionais as $p): ?>
                                <td>
                                    <?php if (isset($grade_organizada[$h_f][$p['nome']])): 
                                        $ag = $grade_organizada[$h_f][$p['nome']]; ?>
                                        <div class="card-agendamento <?= strtolower($ag['status']) ?>" 
                                             onclick="abrirModalEdicao('<?= $ag['id_agenda'] ?>', '<?= $ag['data'] ?>', '<?= $ag['hora'] ?>', '<?= $ag['cliente'] ?>', '<?= $ag['id_servico'] ?>')"
                                             style="cursor:pointer;">
                                            <strong><?= $ag['cliente'] ?></strong><br>
                                            <small><?= $ag['servico'] ?></small>
                                        </div>
                                    <?php else: ?>
                                        <a href="javascript:void(0)" class="btn-vazio" onclick="abrirModal('<?= $data_selecionada ?>', '<?= $h_f ?>', '<?= $p['id_profissional'] ?>')"> + </a>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php $inicio->modify('+30 minutes'); endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div id="modalAgendamento" class="modal-overlay">
        <div class="modal-content">
            <h2 style="margin-top:0; color:#1e3c72;">Novo Agendamento</h2>
            <form method="POST" action="salvar_agendamento.php">
                <input type="hidden" name="id_profissional" id="modal_prof_id">
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;"><label>Data:</label><input type="date" name="data" id="modal_data" readonly></div>
                    <div style="flex:1;"><label>Hora:</label><input type="time" name="hora" id="modal_hora" readonly></div>
                </div>
                <label>Cliente:</label>
                <input list="clientes_list" name="nome_cliente_completo" placeholder="Digite para buscar..." required autocomplete="off">
                <datalist id="clientes_list">
                    <?php foreach($lista_clientes as $c): ?>
                        <option value="<?= $c['nome'] ?> - <?= $c['celular'] ?>">
                    <?php endforeach; ?>
                </datalist>
                <label>Serviço:</label>
                <select name="id_servico" required>
                    <?php foreach($lista_servicos as $s): ?>
                        <option value="<?= $s['id_servico'] ?>"><?= $s['nome'] ?> - R$ <?= $s['valor'] ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn-acao" style="background:#28a745; color:white;">SALVAR</button>
                    <button type="button" onclick="fecharModal()" class="btn-acao" style="background:#666; color:white;">FECHAR</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEdicao" class="modal-overlay">
        <div class="modal-content">
            <h2 style="margin-top:0; color:#1e3c72;">Editar Agendamento</h2>
            <form method="POST" action="editar_agendamento.php" onsubmit="return validarEdicao()">
                <input type="hidden" name="id_agenda" id="edit_id">
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;"><label>Data:</label><input type="date" name="data" id="edit_data" required></div>
                    <div style="flex:1;"><label>Hora:</label><input type="time" name="hora" id="edit_hora" required></div>
                </div>
                <label>Cliente:</label>
                <input type="text" name="cliente" id="edit_cliente" readonly style="background:#f0f0f0;">
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

    <script>
    function abrirModal(data, hora, profId) {
        document.getElementById('modal_data').value = data;
        document.getElementById('modal_hora').value = hora;
        document.getElementById('modal_prof_id').value = profId;
        document.getElementById('modalAgendamento').style.display = 'flex';
    }
    function fecharModal() { document.getElementById('modalAgendamento').style.display = 'none'; }
    function abrirModalEdicao(id, data, hora, cliente, servicoId) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_data').value = data;
        document.getElementById('edit_hora').value = hora;
        document.getElementById('edit_cliente').value = cliente;
        document.getElementById('edit_servico').value = servicoId;
        document.getElementById('modalEdicao').style.display = 'flex';
    }
    function fecharModalEdicao() { document.getElementById('modalEdicao').style.display = 'none'; }
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
    window.onclick = function(event) {
        if (event.target.className === 'modal-overlay') {
            fecharModal();
            fecharModalEdicao();
        }
    }
    </script>
</body>
</html>