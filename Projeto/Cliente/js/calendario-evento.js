(function () {
    const dialogo = document.getElementById("calendario-evento");
    if (typeof dialogo?.show !== "function") return;

    const titulo = dialogo.querySelector("#calendario-titulo");
    const mes = dialogo.querySelector("[data-calendario-mes]");
    const ano = dialogo.querySelector("[data-calendario-ano]");
    const dias = dialogo.querySelector("[data-calendario-dias]");
    const anterior = dialogo.querySelector("[data-mes-anterior]");
    const seguinte = dialogo.querySelector("[data-mes-seguinte]");
    const nomeMes = new Intl.DateTimeFormat("pt-BR", {month: "long"});
    const nomeData = new Intl.DateTimeFormat("pt-BR", {dateStyle: "full"});
    const reservas = JSON.parse(document.getElementById("form-solicitacao").dataset.reservas);
    let campo;
    let ancora;
    let origem;
    let selecionada;
    let referencia;

    function posicionar() {
        if (!dialogo.open) return;
        const margemVertical = 12;
        const margemHorizontal = parseFloat(getComputedStyle(dialogo).getPropertyValue("--calendario-margem-inline"));
        const distancia = 8;
        const botao = ancora.getBoundingClientRect();
        const {width, height} = dialogo.getBoundingClientRect();
        const largura = document.documentElement.clientWidth;
        const altura = document.documentElement.clientHeight;
        const cabeDireita = botao.right + distancia + width <= largura - margemHorizontal;
        const cabeEsquerda = botao.left - distancia - width >= margemHorizontal;
        let esquerda = botao.right + distancia;
        let topo = botao.top;

        if (!cabeDireita && cabeEsquerda) {
            esquerda = botao.left - distancia - width;
        } else if (!cabeDireita) {
            esquerda = botao.right - width;
            topo = botao.bottom + distancia + height <= altura - margemVertical
                ? botao.bottom + distancia
                : botao.top - distancia - height;
        }

        dialogo.style.left = `${Math.max(margemHorizontal, Math.min(esquerda, largura - width - margemHorizontal))}px`;
        dialogo.style.top = `${Math.max(margemVertical, Math.min(topo, altura - height - margemVertical))}px`;
    }

    function fechar(foco) {
        dialogo.close();
        ancora.setAttribute("aria-expanded", "false");
        foco?.focus({preventScroll: true});
    }

    window.addEventListener("resize", posicionar);
    window.addEventListener("scroll", posicionar, {capture: true, passive: true});
    document.addEventListener("pointerdown", (evento) => {
        if (dialogo.open && !dialogo.contains(evento.target) && evento.target !== ancora && evento.target !== campo) {
            fechar();
        }
    });
    document.addEventListener("keydown", (evento) => {
        if (!dialogo.open || evento.key !== "Escape" || evento.defaultPrevented) return;
        evento.preventDefault();
        fechar(origem);
    });

    function criarData(anoData, mesData, diaData) {
        const data = new Date();
        data.setHours(12, 0, 0, 0);
        data.setFullYear(anoData, mesData, diaData);
        return data;
    }

    function formatar(data) {
        const dia = String(data.getDate()).padStart(2, "0");
        const numeroMes = String(data.getMonth() + 1).padStart(2, "0");
        return `${dia}/${numeroMes}/${String(data.getFullYear()).padStart(4, "0")}`;
    }

    for (let indice = 0; indice < 12; indice++) {
        mes.add(new Option(nomeMes.format(criarData(2000, indice, 1)), indice));
    }

    function renderizar() {
        const anoVisivel = referencia.getFullYear();
        const mesVisivel = referencia.getMonth();
        const inicio = criarData(anoVisivel, mesVisivel, 1);
        const primeiroDia = 1 - inicio.getDay();
        const hoje = formatar(new Date());
        mes.value = mesVisivel;
        ano.value = anoVisivel;
        anterior.disabled = anoVisivel === 1 && mesVisivel === 0;
        seguinte.disabled = anoVisivel === 9999 && mesVisivel === 11;
        dias.replaceChildren();

        for (let dia = primeiroDia; dia < primeiroDia + 42; dia++) {
            const data = criarData(anoVisivel, mesVisivel, dia);
            if (data.getFullYear() < 1 || data.getFullYear() > 9999) {
                const espaco = document.createElement("span");
                espaco.setAttribute("aria-hidden", "true");
                dias.append(espaco);
                continue;
            }
            const valor = formatar(data);
            const iso = valor.split("/").reverse().join("-");
            const reservado = reservas.some(([inicio, fim]) => inicio <= iso && iso <= fim);
            const botao = document.createElement("button");
            botao.type = "button";
            botao.textContent = data.getDate();
            botao.value = valor;
            botao.dataset.diaRelativo = dia;
            botao.classList.toggle("dia-fora-mes", data.getMonth() !== mesVisivel);
            botao.tabIndex = dia === referencia.getDate() ? 0 : -1;
            botao.setAttribute("aria-label", nomeData.format(data) + (reservado ? ", dia reservado" : ""));
            if (reservado) botao.setAttribute("aria-disabled", "true");
            botao.setAttribute("aria-pressed", String(valor === selecionada));
            if (valor === hoje) botao.setAttribute("aria-current", "date");
            dias.append(botao);
        }
    }

    function moverMes(delta) {
        referencia = criarData(referencia.getFullYear(), referencia.getMonth() + delta, 1);
        renderizar();
    }

    anterior.addEventListener("click", () => moverMes(-1));
    seguinte.addEventListener("click", () => moverMes(1));
    mes.addEventListener("change", () => {
        referencia = criarData(referencia.getFullYear(), Number(mes.value), 1);
        renderizar();
    });
    ano.addEventListener("change", () => {
        if (ano.value && ano.validity.valid) referencia = criarData(ano.valueAsNumber, referencia.getMonth(), 1);
        renderizar();
    });

    dias.addEventListener("keydown", (evento) => {
        const deslocamentos = {ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7};
        const delta = deslocamentos[evento.key];
        if (!delta || evento.target.dataset.diaRelativo === undefined) return;
        evento.preventDefault();
        const dia = Number(evento.target.dataset.diaRelativo);
        const proxima = criarData(referencia.getFullYear(), referencia.getMonth(), dia + delta);
        if (proxima.getFullYear() < 1 || proxima.getFullYear() > 9999) return;
        referencia = proxima;
        renderizar();
        dias.querySelector('[tabindex="0"]').focus();
    });

    dias.addEventListener("click", (evento) => {
        const botao = evento.target.closest("button[data-dia-relativo]");
        if (!botao || botao.getAttribute("aria-disabled") === "true") return;
        campo.value = botao.value;
        campo.dispatchEvent(new Event("change", {bubbles: true}));
        fechar(campo);
    });

    dialogo.querySelector("[data-calendario-fechar]").addEventListener("click", () => fechar(origem));

    for (const grupo of document.querySelectorAll(".campo-data")) {
        const entrada = grupo.querySelector(".field");
        const botao = grupo.querySelector(".btn-calendario");
        botao.hidden = false;
        botao.setAttribute("aria-expanded", "false");
        grupo.classList.add("campo-data--com-calendario");

        function abrir(evento) {
            if (entrada.disabled) return;
            if (dialogo.open) {
                if (ancora === botao) {
                    if (evento.currentTarget === botao) fechar(botao);
                    return;
                }
                fechar();
            }
            ancora = botao;
            origem = evento.currentTarget;
            campo = entrada;
            selecionada = entrada.value;
            const partes = selecionada.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            const escrita = partes && criarData(Number(partes[3]), Number(partes[2]) - 1, Number(partes[1]));
            referencia = escrita && escrita.getFullYear() > 0 && formatar(escrita) === selecionada ? escrita : new Date();
            titulo.textContent = entrada.labels[0].textContent;
            renderizar();
            dialogo.show();
            posicionar();
            ancora.setAttribute("aria-expanded", "true");
            dias.querySelector('[tabindex="0"]').focus({preventScroll: true});
        }

        botao.addEventListener("click", abrir);
        entrada.addEventListener("click", (evento) => {
            if (!entrada.value) abrir(evento);
        });
    }
})();
