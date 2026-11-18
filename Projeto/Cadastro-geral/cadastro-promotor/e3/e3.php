<?php
session_start();

if(!isset($_SESSION["cadastro"])){
    $_SESSION["cadastro"] = [];
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    foreach($_POST as $chave => $valor){
        $_SESSION["cadastro"][$chave] = $valor;
    }
    header("Location: e3.php", true, 303);
    exit;
}

// Plano pré-escolhido pela landing page (ex: "QUERO ESTE PLANO" do anual)
$plano_pre_escolhido = $_GET["plano"] ?? ($_SESSION["cadastro"]["plano"] ?? "");

$titulo = "Escolha seu plano";
$css = "css/e3.css";
$voltar = "../e2/e2.php";
include "../topo.php";
?>
            <form class="conteudo-plano" action="../e4/e4.php" method="post">
                <div class="titulo">
                    <h1>Escolha seu plano</h1>
                    <p>Você vai pagar <strong>mensalmente</strong> ou <strong>anualmente?</strong></p>
                    <hr>
                </div>

                <div class="planos">
                    <input class="plano-input" type="radio" name="plano" id="mensal" value="mensal" <?= $plano_pre_escolhido === "mensal" ? "checked" : "" ?> required>
                    <label class="plano-cartao" for="mensal">
                        <h2>Mensal</h2>
                        <p class="descricao">Perfeito para quem quer testar sem compromisso</p>

                        <div class="preco">
                            <span>R$</span>
                            <strong>30</strong>
                            <p>/mês</p>
                        </div>

                        <ul>
                            <li><img class="funcionalidade-icone" src="../../../assets/cadastro-funcionalidade.svg" alt=""><span>Acesso ilimitado à plataforma</span></li>
                            <li><img class="funcionalidade-icone" src="../../../assets/cadastro-funcionalidade.svg" alt=""><span>Personalização básica do perfil</span></li>
                            <li><img class="funcionalidade-icone" src="../../../assets/cadastro-funcionalidade.svg" alt=""><span>Cancele quando quiser</span></li>
                        </ul>

                        <div class="botao-cartao">
                            <span class="escolher">Escolher o plano</span>
                            <span class="escolhido">Plano escolhido</span>
                        </div>
                    </label>

                    <input class="plano-input" type="radio" name="plano" id="anual" value="anual" <?php echo $plano_pre_escolhido === "anual" ? "checked" : ""; ?>>
                    <label class="plano-cartao" for="anual">
                        <h2>Anual</h2>
                        <p class="descricao">Pague menos e aproveite por um ano inteiro</p>

                        <div class="preco">
                            <span>R$</span>
                            <strong>315</strong>
                            <p>/ano</p>
                        </div>

                        <ul>
                            <li><img class="funcionalidade-icone" src="../../../assets/cadastro-funcionalidade.svg" alt=""><span>Atendimento prioritário</span></li>
                            <li><img class="funcionalidade-icone" src="../../../assets/cadastro-funcionalidade.svg" alt=""><span>Preço com 15% de desconto</span></li>
                            <li><img class="funcionalidade-icone" src="../../../assets/cadastro-funcionalidade.svg" alt=""><span>Tudo do plano mensal</span></li>
                        </ul>

                        <div class="botao-cartao">
                            <span class="escolher">Escolher o plano</span>
                            <span class="escolhido">Plano escolhido</span>
                        </div>
                    </label>
                </div>

                <button type="submit">Continuar</button>
            </form>
<?php include "../fim.php"; ?>
