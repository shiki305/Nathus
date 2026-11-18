// ============================================================
// JS da etapa PIX do cadastro
// - Copia o código PIX para a área de transferência
// ============================================================

var botaoCopiar = document.getElementById('copiar-pix');
var codigoPix = document.getElementById('codigo-pix');

botaoCopiar.addEventListener('click', function () {
    navigator.clipboard.writeText(codigoPix.value).then(function () {
        botaoCopiar.querySelector('span').textContent = 'Copiado';
    });
});
