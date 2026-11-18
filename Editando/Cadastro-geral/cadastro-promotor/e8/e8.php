<?php session_start(); ?>
<?php $titulo = "Perfil pronto"; $css = "css/e8.css"; include "../topo.php"; ?>
            <div class="conteudo-final">
                <div class="perfil-concluido" aria-hidden="true">
                    <img src="../../../assets/check_dashboard.svg" alt="">
                </div>
                <h1>Seu perfil está pronto</h1>
                <p>Adicione seus pacotes e fotos para mostrar aos clientes o que você oferece.</p>
                <a class="botao-perfil" href="../../../Perfil/perfil.php">Ir para meu perfil</a>
            </div>
<?php include "../fim.php"; ?>
