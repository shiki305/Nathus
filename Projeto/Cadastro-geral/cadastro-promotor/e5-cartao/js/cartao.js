document.querySelectorAll(".cartao-valor[contenteditable]").forEach((campo) => {
    campo.addEventListener("keydown", (evento) => {
        if (evento.key === "Enter" && !evento.isComposing) {
            evento.preventDefault();
        }
    });

    function ajustarTexto() {
        const texto = campo.textContent;
        let valor = texto.replace(/[\r\n\t]/g, " ").replace(/\u00a0/g, " ");

        if (campo.hasAttribute("data-numerico")) {
            const modo = campo.dataset.numerico;

            if (modo === "espacos") {
                // Numero do cartao: ate 16 digitos, em grupos de 4.
                const digitos = valor.replace(/[^0-9]/g, "").slice(0, 16);
                valor = digitos.replace(/(\d{4})(?=\d)/g, "$1 ");
            } else {
                valor = valor.replace(/[^0-9]/g, "").slice(0, Number(campo.dataset.max));
                if (modo === "validade" && valor.length > 2) {
                    valor = valor.slice(0, 2) + "/" + valor.slice(2);
                }
            }
        } else {
            valor = valor.slice(0, Number(campo.dataset.max));
        }

        if (texto !== valor || campo.childElementCount > 0) {
            campo.textContent = valor;
            window.getSelection()?.collapse(campo, campo.childNodes.length);
        }
    }

    campo.addEventListener("input", (evento) => {
        if (!evento.isComposing) ajustarTexto();
    });
    campo.addEventListener("compositionend", ajustarTexto);
});
