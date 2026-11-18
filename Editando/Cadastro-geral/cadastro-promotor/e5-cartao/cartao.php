<?php
session_start();

if(!isset($_SESSION["cadastro"])){
    $_SESSION["cadastro"] = [];
}

$_SESSION["cadastro"]["metodo"] = "cartao";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Location: cartao.php", true, 303);
    exit;
}
$plano = $_SESSION["cadastro"]["plano"] ?? "mensal";
$nome_plano = ($plano == "anual") ? "Anual" : "Mensal";
$valor_plano = ($plano == "anual") ? "R$ 315,00" : "R$ 30,00";

$titulo = "Pagamento com cartão";
$css = "css/cartao.css";
$voltar = "../e4/e4.php";
include "../topo.php";
?>
            <form class="conteudo-cartao" action="../salvar-promotor.php" method="post">
                <div class="titulo">
                    <h1>Pagamento com cartão</h1>
                    <hr>
                </div>

                <div class="resumo-pagamento">
                    <p>Plano <strong><?= $nome_plano ?></strong></p>
                    <p>Valor <strong><?= $valor_plano ?></strong></p>
                </div>

                <div class="form-cartao">
                    <div class="cartao-campo">
                        <span class="rotulo-cartao" id="rotulo-titular">Titular do cartão</span>
                        <div class="cartao-valor" contenteditable="plaintext-only" role="textbox" aria-labelledby="rotulo-titular" aria-multiline="false" spellcheck="false" data-max="100"></div>
                    </div>

                    <div class="cartao-campo">
                        <span class="rotulo-cartao" id="rotulo-numero">Número do cartão</span>
                        <div class="campo-cartao">
                            <img src="../../../assets/cadastro-card-flags.svg" alt="">
                            <div class="cartao-valor" contenteditable="plaintext-only" role="textbox" aria-labelledby="rotulo-numero" aria-multiline="false" aria-placeholder="0000 0000 0000 0000" inputmode="numeric" spellcheck="false" data-max="19" data-numerico="espacos"></div>
                        </div>
                    </div>

                    <div class="cartao-campo">
                        <label class="rotulo-cartao" for="modo-pagamento">Modo de pagamento</label>
                        <select id="modo-pagamento" name="modo-pagamento" required>
                            <option value="" disabled selected hidden>Débito ou crédito</option>
                            <option value="debito">Débito</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>

                    <div class="linha-formulario">
                        <div class="cartao-campo">
                            <span class="rotulo-cartao" id="rotulo-validade">Data de validade</span>
                            <div class="cartao-valor" contenteditable="plaintext-only" role="textbox" aria-labelledby="rotulo-validade" aria-multiline="false" aria-placeholder="Mês/Ano" inputmode="numeric" spellcheck="false" data-max="4" data-numerico="validade"></div>
                        </div>

                        <div class="cartao-campo">
                            <span class="rotulo-cartao" id="rotulo-cvv">Código de segurança</span>
                            <div class="cartao-valor" contenteditable="plaintext-only" role="textbox" aria-labelledby="rotulo-cvv" aria-multiline="false" aria-placeholder="CVV" inputmode="numeric" spellcheck="false" data-max="3" data-numerico></div>
                        </div>
                    </div>
                </div>

                <button class="btn-finalizar-cartao" type="submit">Finalizar</button>
            </form>
<script src="js/cartao.js?v=<?= filemtime(__DIR__ . '/js/cartao.js') ?>"></script>
<?php include "../fim.php"; ?>
