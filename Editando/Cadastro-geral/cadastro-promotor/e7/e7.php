<?php
session_start();
require_once __DIR__ . '/../../../Includes/promotor.php';

if(!isset($_SESSION["usuario_id"])){
    header("Location: ../../../Login/login.php");
    exit;
}

$titulo = "Finalizar perfil";
$css = "css/e7.css";
include "../topo.php";
?>
            <form class="conteudo-perfil" action="../salvar-perfil.php" method="post" enctype="multipart/form-data">
                <h1>Insira sua foto de perfil e uma descrição!</h1>

                <div class="seletor-foto">
                    <input class="input-foto" type="file" id="foto_perfil" name="foto_perfil" accept="image/png, image/jpeg, image/webp" aria-label="Escolher foto de perfil">
                    <label class="foto-perfil" for="foto_perfil" id="labelFotoPerfil">
                        <img src="../../../assets/user_dashboard.svg?v=<?= filemtime(__DIR__ . '/../../../assets/user_dashboard.svg') ?>" alt="" id="iconeFotoPerfil">
                        <span class="foto-perfil-overlay" aria-hidden="true">
                            <img src="../../../assets/camera-avatar.svg" alt="">
                        </span>
                    </label>
                </div>

                <hr class="barra-perfil">

                <div class="descricao-perfil">
                    <label for="frase_destaque">Frase de destaque</label>
                    <input type="text" name="frase_destaque" id="frase_destaque" maxlength="150"
                        placeholder="Ex: Transformando Sonhos em Eventos Inesquecíveis!"
                        aria-describedby="limite-frase">
                    <p class="limite-descricao" id="limite-frase">Até 150 caracteres.</p>
                </div>

                <div class="descricao-perfil">
                    <label for="descricao">Descrição do perfil</label>
                    <textarea name="descricao" id="descricao" aria-describedby="limite-descricao" maxlength="<?= LIMITE_DESCRICAO_PROMOTOR ?>"></textarea>
                    <?php if (($_GET['erro'] ?? '') === 'descricao'): ?>
                        <p class="limite-descricao" id="limite-descricao" role="alert">Informe uma descrição de até <?= LIMITE_DESCRICAO_PROMOTOR ?> caracteres.</p>
                    <?php else: ?>
                        <p class="limite-descricao" id="limite-descricao">Até <?= LIMITE_DESCRICAO_PROMOTOR ?> caracteres.</p>
                    <?php endif; ?>
                </div>

                <button type="submit">Continuar</button>
            </form>
<script src="js/e7.js"></script>

<?php include "../fim.php"; ?>
