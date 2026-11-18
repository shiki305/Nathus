(() => {
    const digitos = (valor) => valor.replace(/\D/g, "");

    function cpf(valor) {
        const numero = digitos(valor).slice(0, 11);
        return numero.replace(/^(\d{3})(?=\d)/, "$1.")
            .replace(/^(\d{3}\.\d{3})(?=\d)/, "$1.")
            .replace(/^(\d{3}\.\d{3}\.\d{3})(?=\d)/, "$1-");
    }

    function cnpj(valor) {
        return valor.toUpperCase().replace(/[^A-Z0-9]/g, "").slice(0, 14)
            .replace(/^(.{2})(?=.)/, "$1.")
            .replace(/^(.{6})(?=.)/, "$1.")
            .replace(/^(.{10})(?=.)/, "$1/")
            .replace(/^(.{15})(?=.)/, "$1-");
    }

    function telefone(valor) {
        let numero = digitos(valor);
        const internacional = valor.trim().startsWith("+");
        if (internacional && numero.length < 2) return "+" + numero;
        const temPais = internacional || (/^55\d{10,11}$/.test(valor.trim()));
        if (temPais && !numero.startsWith("55")) return "+" + numero.slice(0, 13);
        if (temPais) numero = numero.slice(2);
        numero = numero.slice(0, 11);
        const prefixo = temPais ? "+55" : "";
        if (!numero) return prefixo;
        const ddd = numero.slice(0, 2);
        if (numero.length <= 2) return (prefixo ? prefixo + " " : "") + "(" + ddd;
        const corte = numero.length === 11 ? 7 : 6;
        const final = numero.length > corte ? "-" + numero.slice(corte) : "";
        return (prefixo ? prefixo + " " : "") + "(" + ddd + ") " + numero.slice(2, corte) + final;
    }

    function moeda(valor, finalizar, colagem) {
        const limpo = valor.trim().replace(/^R\$\s*/i, "");
        if ((finalizar || colagem) && !/^(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{0,2})?$/.test(limpo)) return valor;
        const partes = limpo.match(/^(\d[\d.]*|(?=,))(?:,(\d{0,2}))?$/);
        if (!partes) return valor;
        const inteiros = partes[1].replace(/\./g, "").replace(/^0+(?=\d)/, "") || "0";
        if (inteiros.length > 8) return valor;
        const agrupado = inteiros.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        return agrupado + "," + (partes[2] ?? "").padEnd(2, "0");
    }

    const data = (valor) => digitos(valor).slice(0, 8)
        .replace(/^(\d{2})(?=\d)/, "$1/").replace(/^(\d{2}\/\d{2})(?=\d)/, "$1/");
    const horario = (valor) => digitos(valor).slice(0, 4).replace(/^(\d{2})(?=\d)/, "$1:");
    function area(valor, finalizar, colagem) {
        let entrada = valor.trim().replace(/\s*m²$/i, "");
        if (finalizar || colagem) {
            if (/^\d{1,8}\.\d{1,2}$/.test(entrada)) entrada = entrada.replace(".", ",");
            if (!/^(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{0,2})?$/.test(entrada)) return valor;
        }
        const partes = entrada.match(/^(\d[\d.]*|(?=,))(?:,(\d{0,2}))?$/);
        if (!partes) return valor;
        const inteiro = digitos(partes[1]).replace(/^0+(?=\d)/, "") || "0";
        if (inteiro.length > 8) return valor;
        const agrupado = inteiro.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        return partes[2] === undefined || (finalizar && partes[2] === "")
            ? agrupado : agrupado + "," + partes[2];
    }
    const mascaras = { cpf, cnpj, telefone, moeda, data, horario, area };
    function formatar(campo, finalizar = false, colagem = false) {
        const mascara = mascaras[campo.dataset.mascara];
        if (!mascara) return;
        const valor = campo.value;
        const formatado = mascara(valor, finalizar, colagem);
        if (formatado === valor) return;

        const cursor = campo.selectionStart;
        campo.value = formatado;
        if (cursor === null || document.activeElement !== campo) return;
        if (cursor === valor.length && campo.dataset.mascara !== "moeda") {
            campo.setSelectionRange(formatado.length, formatado.length);
            return;
        }

        const caracteres = ["moeda", "area"].includes(campo.dataset.mascara) ? /[^\d,]/g
            : campo.dataset.mascara === "cnpj" ? /[^a-z0-9]/gi : /\D/g;
        const antes = valor.slice(0, cursor).replace(caracteres, "").length;
        let posicao = 0;
        while (posicao < formatado.length && formatado.slice(0, posicao).replace(caracteres, "").length < antes) {
            posicao++;
        }
        if (campo.dataset.mascara === "moeda" && cursor <= (valor.indexOf(",") < 0 ? valor.length : valor.indexOf(","))) {
            posicao = Math.min(posicao, formatado.indexOf(","));
        }
        campo.setSelectionRange(posicao, posicao);
    }

    function formatarCampos() {
        document.querySelectorAll("input[data-mascara]").forEach((campo) => formatar(campo, true));
    }

    const seletorMoeda = 'input[data-mascara="moeda"]';
    const zerosTemporarios = new WeakSet();
    let cliqueInicial;

    document.addEventListener("pointerdown", (evento) => {
        const campo = evento.target;
        cliqueInicial = campo.matches(seletorMoeda) && campo.value === "" ? campo : null;
    });
    document.addEventListener("focusin", (evento) => {
        const campo = evento.target;
        if (!campo.matches(seletorMoeda) || campo.value !== "") return;
        campo.value = "0,00";
        zerosTemporarios.add(campo);
        campo.setSelectionRange(1, 1);
    });
    document.addEventListener("click", (evento) => {
        const campo = evento.target;
        if (campo === cliqueInicial && zerosTemporarios.has(campo)) campo.setSelectionRange(1, 1);
        cliqueInicial = null;
    });
    function limparZeroTemporario(campo) {
        if (zerosTemporarios.delete(campo)) campo.value = "";
    }
    document.addEventListener("focusout", (evento) => {
        limparZeroTemporario(evento.target);
    });

    document.addEventListener("beforeinput", (evento) => {
        const campo = evento.target;
        if (!campo.matches(seletorMoeda) || evento.inputType !== "insertText" || evento.isComposing) return;
        const virgula = campo.value.indexOf(",");
        if (virgula === -1 || campo.selectionStart !== campo.selectionEnd) return;

        if (evento.data === "," || evento.data === ".") {
            evento.preventDefault();
            campo.setSelectionRange(virgula + 1, virgula + 1);
        } else if (/^\d$/.test(evento.data ?? "") && campo.selectionStart > virgula) {
            evento.preventDefault();
            if (campo.selectionStart < campo.value.length) {
                const inicio = campo.selectionStart;
                campo.setRangeText(evento.data, inicio, inicio + 1, "end");
                campo.dispatchEvent(new Event("input", { bubbles: true }));
            }
        }
    });
    document.addEventListener("input", (evento) => {
        if (!evento.isComposing && evento.target.matches("input[data-mascara]")) {
            zerosTemporarios.delete(evento.target);
            formatar(evento.target, false, evento.inputType === "insertFromPaste");
        }
    });
    document.addEventListener("change", (evento) => {
        if (evento.target.matches("input[data-mascara]")) formatar(evento.target, true);
    });
    document.addEventListener("submit", (evento) => {
        evento.target.querySelectorAll(seletorMoeda).forEach((campo) => {
            limparZeroTemporario(campo);
            formatar(campo, true);
        });
    }, true);
    window.addEventListener("pageshow", formatarCampos);
    formatarCampos();
})();
