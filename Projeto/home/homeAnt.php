<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nathus</title>
    <link rel="icon" type="image/png" href="../assets/favicon.png?v=2">
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . '/../Includes/css/global.css'); ?>">
    <link rel="stylesheet" href="css/vitrine.css?v=<?php echo filemtime(__DIR__ . '/css/vitrine.css'); ?>">
</head>
<body class="vt">
    <?php include __DIR__ . '/topo-vitrine.php'; ?>

    <section class="vt-hero" aria-label="Nathus">
        <img src="../assets/HomeHeromelhor.png" alt="">
        <div class="vt-hero-texto">
            <h1>Conecte seu evento aos<br><span>melhores profissionais.</span></h1>
            <p>Descubra promotores, buffets, espaços e fornecedores verificados. Avalie portfólios e negocie diretamente com quem entende do seu evento.</p>
        </div>
    </section>

    <section class="vt-escolha" aria-label="Por onde você quer começar">
        <p class="vt-kicker">Tudo para o seu evento</p>
        <h2>Por onde você quer começar?</h2>
        <p>Explore o ecossistema Nathus e encontre parceiros confiáveis para cada etapa da sua produção.</p>

        <div class="vt-grade">
            <a class="entrada-card" href="home.php?campo=organizadores">
                <img src="../assets/home/o2.png" alt="">
                <div class="entrada-texto">
                    <small>Quem planeja o evento</small>
                    <h3>Promotores &amp; Organizadores</h3>
                    <p>Encontre profissionais com experiência, reputação e uma rede pronta para transformar sua ideia em um evento memorável.</p>
                </div>
            </a>

            <a class="entrada-card" href="home.php?campo=buffet">
                <img src="../assets/home/cardapio1.png" alt="">
                <div class="entrada-texto">
                    <small>Experiências que ficam na memória</small>
                    <h3>Buffet &amp; Gastronomia</h3>
                    <p>Conheça opções deliciosas, estilos de serviço e equipes gastronômicas para eventos de todos os formatos e tamanhos.</p>
                </div>
            </a>

            <a class="entrada-card" href="home.php?campo=espaco">
                <img src="../assets/home/EC.png" alt="">
                <div class="entrada-texto">
                    <small>O cenário ideal para a celebração</small>
                    <h3>Espaços &amp; Casas de Festas</h3>
                    <p>Compare ambientes, estruturas, capacidades e localizações para encontrar o espaço perfeito para o seu evento.</p>
                </div>
            </a>
        </div>
    </section>
    <?php include __DIR__ . '/conta-modal.php'; ?>
    <?php include __DIR__ . '/../Includes/footer-home.php'; ?>
</body>
</html>
