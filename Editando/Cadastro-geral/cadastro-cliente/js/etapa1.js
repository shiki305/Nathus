// ============================================================
// JS da etapa 1 do cadastro de cliente
// - Preview da foto escolhida antes de enviar o formulário
// ============================================================

const inputFoto = document.getElementById('input-foto');
const previewFoto = document.getElementById('preview-foto');

inputFoto.addEventListener('change', function (e) {
    const arquivo = e.target.files[0];

    if (arquivo) {
        const reader = new FileReader();

        reader.onload = function (evento) {
            previewFoto.src = evento.target.result;
            previewFoto.classList.add('com-foto'); // Aplica o estilo de foto preenchida
        };

        reader.readAsDataURL(arquivo);
    }
});
