<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastre-se</title>
    <link rel="stylesheet" href="../../Includes/css/cadastro-campos.css?v=<?= filemtime(__DIR__ . '/../../Includes/css/cadastro-campos.css') ?>">
    <link rel="stylesheet" href="css/etapa2.css?v=<?php echo filemtime(__DIR__ . "/css/etapa2.css"); ?>">
    <link rel="stylesheet" href="../../Includes/css/botao-voltar.css?v=<?= filemtime(__DIR__ . '/../../Includes/css/botao-voltar.css') ?>">
    <link rel="stylesheet" href="../../Includes/css/painel-texto.css?v=<?= filemtime(__DIR__ . '/../../Includes/css/painel-texto.css') ?>">
</head>
<body>
    <main class="divisao">
        <section id="esquerda">
            <div class="fundo-escuro"></div>
            <?php include __DIR__ . '/../../Includes/painel-texto.php'; ?>
        </section>
        <section id="direita">
            <a href="etapa1.php" class="voltar botao-voltar">Voltar</a>
            <div class="conteudo-final">
                <h1>Bem-Vindo a Nathus!</h1>
                <hr class="barra">
                <p>Seu Perfil foi criado com sucesso!!</p>
                <p>Prepare-se para se conectar aos<br>
                melhores profissionais e transforme seus<br>
                eventos em experiências incríveis, com<br>
                facilidade, agilidade e a tecnologia da<br>
                <span>Nathus!</span></p>
                <a class="botao-explorar" href="../../home/homeAnt.php">Explorar</a>
            </div>
        </section>
    </main>
</body>
</html>
