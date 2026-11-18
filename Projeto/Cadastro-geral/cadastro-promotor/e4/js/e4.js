// ============================================================
// JS da etapa 5 do cadastro (Método de pagamento)
// - Aponta o action do formulário pro fluxo escolhido
// ============================================================

document.querySelector(".conteudo-pagamento").addEventListener("submit", function () {
    this.action = document.getElementById("cartao").checked
        ? "../e5-cartao/cartao.php"
        : "../e5-pix/pix.php";
    return true;
});
