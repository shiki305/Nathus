// ============================================================
// JS da etapa 8 do cadastro (Finalizar perfil)
// - Mostra a foto escolhida no círculo antes de enviar
// ============================================================

(function () {
    var input = document.getElementById('foto_perfil');
    var label = document.getElementById('labelFotoPerfil');
    var icone = document.getElementById('iconeFotoPerfil');
    if (!input || !label || !icone) return;

    input.addEventListener('change', function () {
        var arquivo = input.files && input.files[0];
        if (!arquivo) return;

        var leitor = new FileReader();
        leitor.onload = function (e) {
            icone.src = e.target.result;
            label.classList.add('tem-foto');
        };
        leitor.readAsDataURL(arquivo);
    });
})();
