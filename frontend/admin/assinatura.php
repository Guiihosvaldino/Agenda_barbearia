<?php
session_start();

// Verifica se o admin está logado
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

// =========================================================================
// VARIÁVEIS PARA SIMULAÇÃO DO TCC
// =========================================================================
// Mude para "true" caso queira simular que ele ainda está no período de teste
$planoAtivo = false; 
$diasRestantes = 0; 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Assinatura - Admin</title>
    <style>
        body {
            margin: 0;
            background-image: url('../imagem/FUNDO.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #fff;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            /* Um fundo mais escuro para dar um ar mais dramático de "bloqueio" */
            background: rgba(0,0,0,0.65); 
            z-index: 0;
        }
        .menu {
            background: #380309;
            padding: 14px 25px;
            display: flex;
            gap: 25px;
            font-size: 1.1em;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .menu a {
            color: #fff;
            text-decoration: none;
            padding: 6px 12px;
            font-weight: bold;
            border-radius: 6px;
        }
        .menu a:hover {
            background: #660a18;
        }
        
        .container {
            position: relative;
            z-index: 1;
            background: #ffffff;
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            border-radius: 16px;
            color: #222;
            box-shadow: 0 8px 30px rgba(0,0,0,0.4);
            text-align: center;
        }

        h2 {
            color: #660a18;
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        .status-box {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeeba;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-size: 1.2em;
            font-weight: bold;
        }

        .status-box.expired {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .icon-lock {
            font-size: 4.5em;
            color: #dc3545;
            margin-bottom: 10px;
            text-shadow: 0 4px 10px rgba(220,53,69,0.3);
        }

        .pricing-card {
            background: #f8f9fa;
            border: 2px solid #660a18;
            border-radius: 12px;
            padding: 30px;
            margin: 20px auto;
            max-width: 420px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .price {
            font-size: 2.5em;
            font-weight: bold;
            color: #660a18;
            margin: 15px 0;
        }

        .features {
            list-style: none;
            padding: 0;
            margin-bottom: 25px;
            text-align: left;
        }

        .features li {
            padding: 10px 0;
            font-size: 1.1em;
            border-bottom: 1px solid #ddd;
            color: #444;
            display: flex;
            align-items: center;
        }

        .features li::before {
            content: '✔';
            color: #28a745;
            font-weight: bold;
            margin-right: 12px;
            font-size: 1.2em;
        }

        .btn-pay {
            display: inline-block;
            background: linear-gradient(135deg, #28a745, #218838);
            color: #fff;
            padding: 15px 30px;
            font-size: 1.3em;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
            cursor: pointer;
            border: none;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-pay:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .pix-area {
            display: none;
            margin-top: 25px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 10px;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pix-code {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            font-family: monospace;
            border: 1px dashed #6c757d;
            margin: 15px 0;
            color: #333;
            font-weight: bold;
        }

        .btn-copy {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            font-size: 1.1em;
            transition: 0.2s;
        }
        
        .btn-copy:hover {
            background: #5a6268;
        }

        .logo {
            width: 250px;
            margin: 0 auto 10px;
            display: block;
        }

        /* ========== RESPONSIVIDADE (CELULARES) ========== */
        @media (max-width: 768px) {
            .menu {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
            .container {
                width: 90%;
                padding: 25px 20px;
            }
            .icon-lock {
                font-size: 3.5em;
            }
        }
    </style>
    <link rel="stylesheet" href="../css/menu-lateral.css">
</head>
<body>

<?php require __DIR__ . '/menu_lateral.php'; ?>

<div class="container">
    
    <img src="../../frontend/imagem/logo1.png" class="logo" alt="Logo Barbearia">

    <?php if (!$planoAtivo): ?>
        <div class="icon-lock">🔒</div>
        <h2>Acesso Bloqueado</h2>
        <div class="status-box expired">
            ⚠️ O seu período de teste gratuito de 30 dias expirou.<br><br>
            Para voltar a ter acesso total às funções do sistema (Agenda, Financeiro, Serviços, Relatórios, etc.), é necessário ativar sua assinatura premium.
        </div>
    <?php else: ?>
        <h2 style="color: #28a745;">Minha Assinatura</h2>
        <div class="status-box">
            ✅ Seu período de teste está ativo. Você tem <strong><?= $diasRestantes ?></strong> dias restantes gratuitos! Aproveite todas as funcionalidades.
        </div>
    <?php endif; ?>

    <div class="pricing-card">
        <h3>Plano Profissional ✂️</h3>
        <div class="price">R$ 49,90<span style="font-size: 0.4em; color: #666; font-weight: normal;">/mês</span></div>
        
        <ul class="features">
            <li>Agendamentos Ilimitados</li>
            <li>Gestão Financeira Completa</li>
            <li>Cadastro de Profissionais e Serviços</li>
            <li>Relatórios e Gráficos de Desempenho</li>
            <li>Suporte Técnico Prioritário</li>
        </ul>

        <?php if (!$planoAtivo): ?>
            <button class="btn-pay" onclick="mostrarPix()">Liberar Acesso Agora (PIX)</button>
        <?php else: ?>
            <button class="btn-pay" onclick="mostrarPix()">Assinar Antecipadamente</button>
        <?php endif; ?>

        <div class="pix-area" id="pixArea">
            <p><strong>Escaneie o QR Code ou copie o código abaixo:</strong></p>
            
            <!-- Imagem de QR Code Fictícia Gerada Dinamicamente para dar realidade ao TCC -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=00020126360014BR.GOV.BCB.PIX0114+5511999999999520400005303986540549.905802BR5915BARBEARIA+SISTEMAS6009SAO+PAULO62070503***63041A2B" alt="QR Code PIX">
            
            <div class="pix-code">
                00020126360014BR.GOV.BCB.PIX0114+5511999999999520400005303986540549.905802BR5915BARBEARIASISTEMAS6009SAO+PAULO62070503***63041A2B
            </div>
            
            <button class="btn-copy" onclick="copiarPix()">📋 Copiar Código PIX ("Copia e Cola")</button>
            
            <p style="margin-top: 15px; font-size: 0.9em; color: #28a745; font-weight: bold;">
                <i style="color: #28a745;">⏳</i> A liberação ocorre em até 5 minutos após o pagamento.
            </p>
        </div>
    </div>

</div>

<script>
    function mostrarPix() {
        document.getElementById('pixArea').style.display = 'block';
    }

    function copiarPix() {
        var codigo = "00020126360014BR.GOV.BCB.PIX0114+5511999999999520400005303986540549.905802BR5915BARBEARIASISTEMAS6009SAO+PAULO62070503***63041A2B";
        navigator.clipboard.writeText(codigo).then(function() {
            alert("✅ Código PIX copiado com sucesso! Agora é só colar no seu app do banco.");
        }, function(err) {
            console.error('Erro ao copiar texto: ', err);
        });
    }
</script>

</body>
</html>
