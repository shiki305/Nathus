<?php
session_start();

if(!isset($_SESSION["usuario_id"])){
    header("Location: ../e1/e1.php");
    exit;
}

$titulo = "Cadastro criado";
$css = "css/e6.css";
include "../topo.php";
?>
            <div class="conteudo-sucesso">
                <div class="icone-sucesso" aria-hidden="true">
                    <span></span>
                </div>
                <div class="titulo">
                    <h1>Cadastro criado</h1>
                </div>

                <p class="texto-sucesso">
                    Agora, adicione sua foto e uma descrição para concluir seu perfil.
                </p>

                <a class="botao-continuar" href="../e7/e7.php">Concluir meu perfil</a>
            </div>
<?php include "../fim.php"; ?>
