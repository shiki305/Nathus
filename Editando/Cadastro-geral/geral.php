<?php
session_start();

if(!isset($_SESSION["cadastro"])){
    $_SESSION["cadastro"] = [];
}

// Escolha do tipo de conta: o POST volta pra cá mesma e o PHP
// encaminha pro fluxo correspondente (funciona até sem JavaScript).
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $tipo = $_POST["tipo_usuario"] ?? "";

    if($tipo === "promotor" || $tipo === "cliente"){
        // Novo fluxo: zera rascunho anterior para não misturar dados
        // de cliente/promotor testados na mesma sessão do navegador.
        $_SESSION["cadastro"] = ["tipo_usuario" => $tipo];
        header("Location: " . ($tipo === "promotor" ? "cadastro-promotor/e1/e1.php" : "cadastro-cliente/etapa1.php"), true, 303);
        exit;
    }
}

$titulo = "Tipo de usuário";
$css = "css/escolha.css";
$voltar = "../Landing%20Page/landingpage.php";
$painel_acao = "../Login/login.php";
$painel_rotulo = "Já tenho conta";
include "cadastro-promotor/topo.php";
?>
            <form class="conteudo-escolha" action="geral.php" method="post">
                <div class="titulo">
                    <h1>Tipo de usuário</h1>
                    <p>Nos diga se você é <strong>cliente</strong> ou <strong>promotor</strong></p>
                    <hr>
                </div>

                <div class="linha-cartoes">
                    <input class="opcao-input" type="radio" name="tipo_usuario" id="cliente" value="cliente" required>
                    <label class="cartao" for="cliente">
                        <img class="icone-cliente" src="../assets/cadastro-cliente.svg" alt="">
                        <img class="icone-cliente icone-roxo" src="../assets/cadastro-cliente-roxo.svg" alt="">
                        <h2>Cliente</h2>
                        <p>Deseja contratar serviços com promotores para organizarem seu evento.</p>
                    </label>

                    <input class="opcao-input" type="radio" name="tipo_usuario" id="promotor" value="promotor" required>
                    <label class="cartao" for="promotor">
                        <img src="../assets/cadastro-promotor.svg" alt="">
                        <img class="icone-roxo" src="../assets/cadastro-promotor-roxo.svg" alt="">
                        <h2>Promotor</h2>
                        <p>Vai oferecer serviços para organizar eventos, como buffet, equipes e salões de festas.</p>
                    </label>
                </div>

                <button type="submit">Continuar</button>
            </form>
<?php include "cadastro-promotor/fim.php"; ?>
