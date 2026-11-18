<?php
session_start();

if(!isset($_SESSION["cadastro"])){
    $_SESSION["cadastro"] = [];
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    foreach($_POST as $chave => $valor){
        $_SESSION["cadastro"][$chave] = $valor;
    }
    header("Location: pix.php", true, 303);
    exit;
}

$_SESSION["cadastro"]["metodo"] = "pix";
$plano = $_SESSION["cadastro"]["plano"] ?? "mensal";
$nome_plano = ($plano == "anual") ? "Anual" : "Mensal";
$valor_plano = ($plano == "anual") ? "R$ 315,00" : "R$ 30,00";

$titulo = "Pagamento Pix";
$css = "css/pix.css";
$voltar = "../e4/e4.php";
include "../topo.php";
?>
            <div class="conteudo-pix">
                <div class="titulo">
                    <h1>Pagamento Pix</h1>
                    <hr>
                </div>

                <div class="painel-pix">
                    <div class="resumo-pagamento">
                        <p>Plano <strong><?= $nome_plano ?></strong></p>
                        <p>Valor <strong><?= $valor_plano ?></strong></p>
                    </div>
                    <div class="qr-caixa">
                        <img src="../../../assets/cadastro-qr-generico.png" alt="QR Code Pix">
                    </div>
                    <div class="codigo-pix">
                        <label for="codigo-pix">Código Pix</label>
                        <textarea id="codigo-pix" rows="5" readonly>00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540530.005802BR5925Promotor Exemplo SaaS Festa6009Sao Paulo62070503***6304ABCD</textarea>
                        <button type="button" id="copiar-pix">
                            <img src="../../../assets/cadastro-copiar.svg" alt="">
                            <span aria-live="polite">Copiar código</span>
                        </button>
                    </div>
                </div>

                <form action="../salvar-promotor.php" method="post">
                    <button class="btn-continuar-pix" type="submit">Continuar cadastro</button>
                </form>
            </div>
<script src="js/pix.js?v=<?= filemtime(__DIR__ . '/js/pix.js') ?>"></script>

<?php include "../fim.php"; ?>
