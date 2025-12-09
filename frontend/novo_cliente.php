<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Cliente - Barbearia</title>

    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #ffffff;
            width: 360px;
            padding: 30px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.25);
        }

        h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 25px;
            color: #1e3c72;
            font-size: 1.7em;
            font-weight: 700;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
            color: #222;
        }

        input {
            width: 100%;
            padding: 11px 12px;
            border-radius: 8px;
            border: 1px solid #333;
            font-size: 1em;
            margin-bottom: 18px;
            outline: none;
            transition: 0.2s;
        }

        input:focus {
            border-color: #1e3c72;
            background: #f2f6ff;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: transform 0.2s, background 0.2s;
        }

        button:hover {
            transform: scale(1.03);
            background: linear-gradient(135deg, #2a5298, #1e3c72);
        }

        .voltar {
            display: block;
            text-align: center;
            background: #1e3c72;
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            margin-top: 18px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s;
        }

        .voltar:hover {
            background: #16315c;
            transform: scale(1.02);
        }
    </style>

</head>
<body>

<div class="container">

    <h2>Cadastro Inicial</h2>

    <form method="GET" action="cadastro.php">
        <label>Digite seu celular:</label>
        <input type="text" name="celular" required>

        <button type="submit">Continuar</button>
    </form>

    <a href="index.php" class="voltar">⬅ Voltar ao Login</a>

</div>

</body>
</html>
