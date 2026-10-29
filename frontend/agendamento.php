<?php
require_once("../backend/profissional.php");
require_once("../backend/agenda.php");

// Verifica se cliente está logado
session_start();

if (!isset($_SESSION['cliente_id'])) {
    header("Location: index.php?erro=login");
    exit;
}

$idCliente = $_SESSION['cliente_id'];

$profissionais = listarProfissionais();
$servicos = listarServicos();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $idProfissional = $_POST['profissional'];
    $idServico      = $_POST['servico'];
    $data            = $_POST['data'];
    $hora            = $_POST['hora'];

    // 🔒 TENTA SALVAR O AGENDAMENTO (COM TODAS AS VALIDAÇÕES)
    $resultado = salvarAgendamento(
        $idCliente,
        $idProfissional,
        $idServico,
        $data,
        $hora
    );

    if ($resultado === true) {
        header("Location: confirmacao.php");
        exit;
    }
    elseif ($resultado === "FECHADO") {
        $msg = "❌ A barbearia não funciona neste dia.";
    }
    elseif ($resultado === "FORA_HORARIO") {
        $msg = "❌ Horário fora do expediente da barbearia.";
    }
    elseif ($resultado === "OCUPADO") {
        $msg = "⚠️ Este horário já está ocupado. Escolha outro.";
    }
    else {
        $msg = "❌ Erro inesperado ao realizar o agendamento.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
    <title>Agendar Horário - Barbearia</title>

    <style>
                body {
            background-image: url('imagem/FUNDO.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #fff;
        }

        .container {
            background: #ffffff;
            width: 660px;
            padding: 28px 32px;
            border-radius: 16px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.25);
        }

        h2 {
            text-align: center;
            margin-top: 0;
            color: #660a18;
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: 700;
        }
        span{
            font-weight: bold;
            color: #222;
            margin-bottom: 8px;
            display: block;
            font-size: 1.7em;
        }

        label {
            font-weight: bold;
            color: #222;
            margin-bottom: 5px;
            display: block;
        }

        select, input[type="date"], input[type="time"] {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #888;
            font-size: 1em;
            margin-bottom: 18px;
            outline: none;
            transition: 0.2s;
        }

        select:focus, input:focus {
            border-color: #660a18;
            background: #fdf2f4;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #660a18, #8a1224);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            transform: scale(1.03);
            background: linear-gradient(135deg, #8a1224, #660a18);
        }

        .erro {
            background: #ffdddd;
            border-left: 5px solid red;
            padding: 10px;
            margin-bottom: 18px;
            border-radius: 6px;
            color: #a30000;
            font-weight: bold;
            text-align: center;
        }

        .voltar {
            display: block;
            text-align: center;
            padding: 10px;
            background: #660a18;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            margin-top: 18px;
            transition: 0.2s;
        }

        .voltar:hover {
            background: #480610;
            transform: scale(1.02);
        }
        .calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-weight: bold;
}

.calendar-header button {
    background: #660a18;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 12px 10px;
    cursor: pointer;
}

.calendar-week, #calendario {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}

.calendar-week div {
    text-align: center;
    font-weight: bold;
}

.dia {
    padding: 10px;
    text-align: center;
    border-radius: 8px;
    background: #660a18;
    color: white;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s;
}

.dia:hover:not(.passado):not(.fechado) {
    background: #8a1224;
    transform: scale(1.05);
}

.dia.passado {
    background: #e0e0e0;
    color: #999;
}

.dia.fechado {
    background: #ccc;
    text-decoration: line-through;
    cursor: not-allowed;
}

.dia.selecionado {
    background: #8a1224;
    color: white;
    box-shadow: 0 0 8px rgba(138, 18, 36, 0.7);
}
.meus-horarios {
    display: block;
    text-align: center;
    padding: 10px;
    background: #28a745;
    color: #fff;
    text-decoration: none;
    font-weight: bold;
    border-radius: 8px;
    margin-bottom: 18px;
    transition: 0.2s;
}

.meus-horarios:hover {
    background: #1f7a34;
    transform: scale(1.02);
}

    
/* ========== RESPONSIVIDADE (CELULARES) ========== */
@media (max-width: 768px) {
    .container {
        width: 90% !important;
        max-width: none !important;
        margin: 20px auto !important;
        padding: 20px !important;
    }
    .menu {
        flex-direction: column !important;
        align-items: center !important;
        gap: 10px !important;
        padding: 15px !important;
    }
    .menu a {
        width: 100% !important;
        text-align: center !important;
        box-sizing: border-box !important;
    }
    .logo {
        width: 100% !important;
        max-width: 250px !important;
        margin-bottom: -40px !important;
    }
    table {
        display: block !important;
        overflow-x: auto !important;
        white-space: nowrap !important;
    }
}
</style>

</head>
<body>
    

<div class="container">

    <h2>Agendar Horário</h2>
    <a href="cliente_agendamentos.php" class="meus-horarios">
    📅 Meus Agendamentos
</a>


    <?php if ($msg): ?>
        <div class="erro"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">

        
        <label>Profissional:</label>
<select name="profissional" id="profissional" onchange="carregarHorarios()">

            <option value="">Selecione</option>
            <?php foreach ($profissionais as $p): ?>
                <option value="<?= $p['id_profissional'] ?>">
                    <?= $p['nome'] ?> - <?= $p['especialidade'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Serviço:</label>
        <select name="servico" id="servico" onchange="carregarHorarios()">
            <option value="">Selecione</option>
            <?php foreach ($servicos as $s): ?>
                <option value="<?= $s['id_servico'] ?>">
                    <?= $s['nome'] ?> (<?= $s['duracao'] ?>min - R$<?= $s['valor'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

       <label>Escolha a Data:</label>
<span id="mesAno"></span>

<div class="calendar-header">
    <button type="button" onclick="mesAnterior()">‹</button>
    <button type="button" onclick="mesProximo()">›</button>
</div>

<div class="calendar-week">
    <div>Dom</div><div>Seg</div><div>Ter</div>
    <div>Qua</div><div>Qui</div><div>Sex</div><div>Sáb</div>
</div>

<div id="calendario"></div>

<input type="hidden" name="data" id="dataSelecionada">


    

     <select name="hora" id="horarios" required>
    <option value="">Selecione a Hora </option>
</select>



        <button type="submit">Agendar</button>


    </form>

    <a href="index.php" class="voltar">⬅ Voltar</a>

</div>


<script>
let dataAtual = new Date();
let mes = dataAtual.getMonth();
let ano = dataAtual.getFullYear();

function gerarCalendario() {
    const calendario = document.getElementById("calendario");
    calendario.innerHTML = "";

    const mesAno = document.getElementById("mesAno");
    const meses = ["Janeiro","Fevereiro","Março","Abril","Maio","Junho",
                   "Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"];

    mesAno.innerText = meses[mes] + " " + ano;

    let primeiroDia = new Date(ano, mes, 1).getDay();
    let ultimoDia = new Date(ano, mes + 1, 0).getDate();

    // espaços vazios antes do dia 1
    for (let i = 0; i < primeiroDia; i++) {
        calendario.innerHTML += "<div></div>";
    }

    for (let dia = 1; dia <= ultimoDia; dia++) {
        let btn = document.createElement("div");
        btn.className = "dia";
        btn.innerText = dia;

        let data = new Date(ano, mes, dia);
        let hoje = new Date();
        hoje.setHours(0,0,0,0);

        if (data < hoje) {
            btn.classList.add("passado");
        }

        btn.onclick = () => selecionarDia(btn, data);
        calendario.appendChild(btn);
    }
}

function selecionarDia(btn, data) {
    document.querySelectorAll(".dia").forEach(d => d.classList.remove("selecionado"));
    btn.classList.add("selecionado");

    document.getElementById("dataSelecionada").value =
        data.toISOString().split('T')[0];


    carregarHorarios();
}


function mesAnterior() {
    mes--;
    if (mes < 0) { mes = 11; ano--; }
    gerarCalendario();
}

function mesProximo() {
    mes++;
    if (mes > 11) { mes = 0; ano++; }
    gerarCalendario();
}

gerarCalendario();


function carregarHorarios() {
    const data = document.getElementById("dataSelecionada").value;
    const servico = document.getElementById("servico").value;
    const profissional = document.getElementById("profissional").value;
    
    console.log(data, servico, profissional);

    if (!data || !servico || !profissional) return;

    fetch(`../backend/buscar_horarios.php?data=${data}&servico=${servico}&profissional=${profissional}`)

    .then(r => r.json())
    .then(lista => {
        const select = document.getElementById("horarios");
        select.innerHTML = "";

        if (lista.length === 0) {
            select.innerHTML = "<option>Sem horários disponíveis</option>";
            return;
        }

        lista.forEach(h => {
            select.innerHTML += `<option value="${h}">${h}</option>`;
        });
    });
}
 



</script>


</body>
</html>
