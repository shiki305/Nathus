<?php
session_start();

if(!isset($_SESSION["cadastro"])){
    $_SESSION["cadastro"] = [];
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    foreach($_POST as $chave => $valor){
        $_SESSION["cadastro"][$chave] = $valor;
    }
    header("Location: e4.php", true, 303);
    exit;
}

$titulo = "Método de pagamento";
$css = "css/e4.css";
$voltar = "../e3/e3.php";
include "../topo.php";
?>
            <form class="conteudo-pagamento" method="post">
                <div class="titulo">
                    <h1>Método de pagamento</h1>
                    <p>Escolha pagar em <strong>pix</strong> ou <strong>cartão</strong></p>
                    <hr>
                </div>

                <div class="metodos">
                    <input class="metodo-input" type="radio" name="metodo" id="pix" value="pix" <?= ($_SESSION["cadastro"]["metodo"] ?? "pix") === "pix" ? "checked" : "" ?>>
                    <label class="metodo-cartao" for="pix">
                        <img class="icone-metodo" src="../../../assets/cadastro-pix.svg" alt="">
                        <img class="icone-metodo icone-roxo" src="../../../assets/cadastro-pix-roxo.svg" alt="">
                        <h2>PIX</h2>
                    </label>

                    <input class="metodo-input" type="radio" name="metodo" id="cartao" value="cartao" <?= ($_SESSION["cadastro"]["metodo"] ?? "") === "cartao" ? "checked" : "" ?>>
                    <label class="metodo-cartao" for="cartao">
                        <img class="icone-metodo icone-cartao" src="../../../assets/cadastro-cartao.svg" alt="">
                        <img class="icone-metodo icone-cartao icone-roxo" src="../../../assets/cadastro-cartao-roxo.svg" alt="">
                        <h2>Cartão</h2>
                    </label>
                </div>

                <button type="submit">Continuar</button>
            </form>
<script src="js/e4.js"></script>

<?php include "../fim.php"; ?>
