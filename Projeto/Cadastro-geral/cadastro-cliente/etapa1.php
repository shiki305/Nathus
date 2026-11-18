<?php 

session_start(); if(!isset($_SESSION["cadastro"])) { $_SESSION["cadastro"] = []; }
 ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastre-se</title>
    <link rel="stylesheet" href="../../Includes/css/cadastro-campos.css?v=<?= filemtime(__DIR__ . '/../../Includes/css/cadastro-campos.css') ?>">
    <link rel="stylesheet" href="css/etapa1.css?v=<?php echo filemtime(__DIR__ . "/css/etapa1.css"); ?>">
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
        <a href="../geral.php" class="voltar botao-voltar">Voltar</a>
        <div class="conteudo-formulario">
                <h1>Insira suas informações</h1>
                <hr class="barra">

                <?php if(isset($_GET["erro"]) && $_GET["erro"] === "email"){ ?>
                    <p class="erro-cadastro">Este e-mail já está cadastrado. Faça login ou use outro e-mail.</p>
                <?php } elseif(($_GET["erro"] ?? "") === "foto"){ ?>
                    <p class="erro-cadastro" role="alert">Não foi possível salvar a foto. Escolha outra imagem JPG, PNG ou WebP, ou continue sem foto.</p>
                <?php } elseif(($_GET["erro"] ?? "") === "contato"){ ?>
                    <p class="erro-cadastro">Confira o telefone com DDD e informe um CPF com 11 dígitos.</p>
                <?php } elseif(isset($_GET["erro"])){ ?>
                    <p class="erro-cadastro">Não foi possível concluir o cadastro. Tente novamente.</p>
                <?php } ?>

        <form action="incluircliente.php" method="post" enctype="multipart/form-data">

                    <label for="input-foto" class="foto-cliente" title="Clique para escolher uma foto">
                    <img id="preview-foto" src="../../assets/user_dashboard.svg" alt="Escolher foto do perfil">
                    <span class="foto-cliente-overlay" aria-hidden="true"><img src="../../assets/camera-avatar.svg" alt=""></span>
                    <input type="file" id="input-foto" name="foto" accept="image/jpeg,image/png,image/webp">
                    </label>

                    <div class="campo campo-grande">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo isset($_SESSION['cadastro']['nome']) ? htmlspecialchars($_SESSION['cadastro']['nome']) : ""; ?>" required>
                    </div>

                    <div class="campo campo-grande">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['cadastro']['email']) ? htmlspecialchars($_SESSION['cadastro']['email']) : ""; ?>" required>
                    </div>

                    <div class="campo campo-grande">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>

                    <div class="linha-formulario">
                        <div class="campo">
                            <label for="telefone">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" data-mascara="telefone" maxlength="19" autocomplete="tel" value="<?php echo isset($_SESSION['cadastro']['telefone']) ? htmlspecialchars($_SESSION['cadastro']['telefone']) : ""; ?>" required placeholder="(XX) XXXXX-XXXX">
                        </div>

                        <div class="campo">
                            <label for="cpf">CPF</label>
                            <input type="text" id="cpf" name="cpf" data-mascara="cpf" maxlength="14" value="<?php echo isset($_SESSION['cadastro']['cpf']) ? htmlspecialchars($_SESSION['cadastro']['cpf']) : ""; ?>" required inputmode="numeric" placeholder="XXX.XXX.XXX-XX">
                        </div>
                    </div>

                    <button type="submit">Continuar</button>
        </form>
        </div>
        </section>
    </main>

    <?php require_once __DIR__ . '/../../Includes/mascaras.php'; ?>
    <script src="js/etapa1.js"></script>
</body>
</html>
