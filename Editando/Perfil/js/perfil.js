// ============================================================
// JS da página de perfil (Perfil/perfil.php)
// - Botões "Editar perfil" / "Visualizar"
// - Modal de pacotes (abrir/fechar, preview de imagem, preço)
// - Modal "Sobre" (abrir/fechar, preview de banner e foto)
// - Formatação de preço em moeda (R$) nos campos de preço
// ============================================================

// ---------- Botões do topo ----------

const btnEditar = document.getElementById("btnEditar");
btnEditar.addEventListener("click", abrirModalSobre);

// Botão "Visualizar perfil": abre o perfil público do promotor (visão do cliente).
const btnVisualizar = document.getElementById("btnVisualizar");
if(btnVisualizar && btnVisualizar.dataset.idPromotor && btnVisualizar.dataset.idPromotor !== "0"){
    btnVisualizar.addEventListener("click", () => {
        window.location.href = "../Cliente/perfil.php?id=" + encodeURIComponent(btnVisualizar.dataset.idPromotor) + "&ver_como_cliente=1&de=painel";
    });
}

// Edição da seção "Sobre mim"
const btnEditarSobre = document.querySelector(".btn-editar-sobre");
const modalEditarSobre = document.getElementById("modalEditarSobre");
if(btnEditarSobre && modalEditarSobre){
    btnEditarSobre.addEventListener("click", () => modalEditarSobre.showModal());
    document.getElementById("fecharEditarSobre").addEventListener("click", () => modalEditarSobre.close());
    modalEditarSobre.addEventListener("close", () => btnEditarSobre.focus());
}

// ---------- Modal de pacote ----------

const modalPacote = document.getElementById("modalPacote");
const pacoteImagemPreview = document.getElementById("pacoteImagemPreview");
const pacoteImagemIcone = document.getElementById("pacoteImagemIcone");

document.querySelectorAll(".js-editar-pacote").forEach((botao) => {
    botao.addEventListener("click", () => {
        abrirModalPacote(JSON.parse(botao.dataset.pacote));
    });
});

// Botão "Adicionar Novo Pacote"
const btnNovoPacote = document.querySelector(".pacote-novo");
if(btnNovoPacote){
    btnNovoPacote.addEventListener("click", () => {
        if(btnNovoPacote.hasAttribute("data-requer-endereco")){
            abrirModalSobre();
            const primeiroVazio = ["espaco_logradouro", "espaco_numero", "espaco_bairro", "campoCidade", "campoEstado", "espaco_area_m2"]
                .map((id) => document.getElementById(id))
                .find((campo) => campo && !campo.value.trim());
            (primeiroVazio || document.getElementById("espaco_logradouro")).focus();
            return;
        }
        abrirModalPacote(null);
    });
}

// Botão "X" do modal de pacote
const btnFecharPacote = document.querySelector(".modal-pacote .modal-topo button");
if(btnFecharPacote){ btnFecharPacote.addEventListener("click", fecharModalPacote); }

// Preview da imagem escolhida no modal de pacote
const pacoteImagemInput = document.getElementById("pacoteImagemInput");
if(pacoteImagemInput){ pacoteImagemInput.addEventListener("change", () => pacotePreviewImagem(pacoteImagemInput)); }

function abrirModalPacote(pacote){
    document.getElementById("pacoteId").value = pacote ? pacote.id : "";
    // Pacote novo começa com o título VAZIO: o placeholder orienta e a
    // validação impede salvar sem nome (antes vinha pré-preenchido
    // com "Pacote de Serviço" e dava pra salvar um pacote lixo).
    document.getElementById("pacoteTitulo").value = pacote ? pacote.titulo : "";
    document.getElementById("pacoteItens").value = pacote ? pacote.itens : "";
    const campoPreco = document.getElementById("pacotePreco");
    campoPreco.value = pacote ? pacote.preco : "";
    campoPreco.removeAttribute("aria-invalid");
    document.getElementById("erroPacotePreco").hidden = true;

    // Contadores refletem os valores preenchidos (o evento input não dispara
    // sozinho quando o valor é setado via JS).
    document.getElementById("pacoteTitulo").dispatchEvent(new Event("input", { bubbles: true }));
    document.getElementById("pacoteItens").dispatchEvent(new Event("input", { bubbles: true }));

    // Excluir só existe para pacote já salvo (edição).
    if(pacoteExcluirBtn){
        pacoteExcluirBtn.hidden = !pacote;
    }

    if(pacote && pacote.imagem){
        pacoteImagemPreview.src = pacote.imagem;
        pacoteImagemPreview.style.display = "block";
        pacoteImagemIcone.style.display = "none";
    } else {
        pacoteImagemPreview.style.display = "none";
        pacoteImagemIcone.style.display = "block";
    }

    modalPacote.classList.add("aberto");
}

function fecharModalPacote(){
    modalPacote.classList.remove("aberto");
}

function pacotePreviewImagem(input){
    if(input.files && input.files[0]){
        pacoteImagemPreview.src = URL.createObjectURL(input.files[0]);
        pacoteImagemPreview.style.display = "block";
        pacoteImagemIcone.style.display = "none";
    }
}

modalPacote.addEventListener("click", (evento) => {
    if(evento.target === modalPacote){ fecharModalPacote(); }
});

// ---------- Modal "Sobre" (editar perfil) ----------

const modalSobre = document.getElementById("modalSobre");

// Botão "X" do modal "Sobre"
const btnFecharSobre = document.querySelector(".modal-sobre .modal-topo button");
if(btnFecharSobre){ btnFecharSobre.addEventListener("click", fecharModalSobre); }

function abrirModalSobre(){
    modalSobre.classList.add("aberto");
}

function fecharModalSobre(){
    modalSobre.classList.remove("aberto");
}

modalSobre.addEventListener("click", (evento) => {
    if(evento.target === modalSobre){ fecharModalSobre(); }
});

function aplicarPreviewBanner(arquivo){
    const fundo = document.getElementById("previewBannerModal");
    const caixa = document.querySelector(".editar-banner");
    if(!fundo || !caixa) return;
    fundo.style.backgroundImage = arquivo ? "url('" + URL.createObjectURL(arquivo) + "')" : "";
    caixa.classList.toggle("foto-vazia", !arquivo);
}

function aplicarPreviewFoto(arquivo){
    const foto = document.getElementById("previewAvatarModal");
    const caixa = document.querySelector(".editar-avatar");
    if(!foto || !caixa || !arquivo) return;
    foto.src = URL.createObjectURL(arquivo);
    caixa.classList.remove("foto-vazia");
}

// ---------- Modal de pacote: contadores, validação e feedback ----------

const MAX_TAMANHO_FOTO = 5 * 1024 * 1024; // 5 MB

// Contadores de caracteres (título e descrição do pacote)
document.querySelectorAll(".contador").forEach((contador) => {
    const campo = document.getElementById(contador.dataset.para);
    if(!campo) return;

    const maximo = parseInt(contador.dataset.max, 10);
    const atualizar = () => {
        contador.textContent = campo.value.length + "/" + maximo;
        contador.classList.toggle("contador-limite", campo.value.length >= maximo);
    };
    campo.addEventListener("input", atualizar);
    atualizar();
});

// Validação antes de enviar + feedback visual no botão
const formPacote = document.getElementById("formPacote");
if(formPacote){
    const erroPacote = document.getElementById("erroPacote");
    const campoTitulo = document.getElementById("pacoteTitulo");
    const campoImagem = document.getElementById("pacoteImagemInput");
    const campoPreco = document.getElementById("pacotePreco");
    const erroPreco = document.getElementById("erroPacotePreco");
    const botaoSalvar = document.getElementById("pacoteSalvarBtn");
    const maxInteiros = Number(campoPreco.dataset.maxInteiros);
    let antesDaEdicao;

    function digitosInteiros(valor) {
        const inteiro = valor.trim().replace(/^R\$\s*/i, "").split(",", 1)[0];
        return inteiro.replace(/\D/g, "").length;
    }

    function registrarEdicao() {
        antesDaEdicao = {
            valor: campoPreco.value,
            inicio: campoPreco.selectionStart,
            fim: campoPreco.selectionEnd,
        };
    }

    campoPreco.addEventListener("focus", registrarEdicao);
    campoPreco.addEventListener("beforeinput", registrarEdicao);
    campoPreco.addEventListener("input", () => {
        const quantidade = digitosInteiros(campoPreco.value);
        const anterior = antesDaEdicao?.valor ?? "";
        // Um preço legado acima do limite pode ser encurtado aos poucos.
        if (quantidade > maxInteiros && quantidade >= digitosInteiros(anterior)) {
            campoPreco.value = anterior;
            campoPreco.setSelectionRange(antesDaEdicao?.inicio ?? 0, antesDaEdicao?.fim ?? 0);
        }
        registrarEdicao();
        erroPreco.hidden = true;
        campoPreco.removeAttribute("aria-invalid");
    });

    function validarPreco() {
        const entrada = campoPreco.value.trim();
        const valor = entrada.replace(/^R\$\s*/i, "");
        const formatoValido = /^(?:[0-9]+|[0-9]{1,3}(?:\.[0-9]{3})+)(?:,[0-9]{1,2})?$/.test(valor);
        let mensagem = "";
        if (digitosInteiros(valor) > maxInteiros) {
            mensagem = "O preço do pacote ultrapassa o limite permitido.";
        } else if (entrada && !formatoValido) {
            mensagem = "Informe um preço em reais, como 1.500,00.";
        }

        if (mensagem !== erroPreco.textContent) erroPreco.textContent = mensagem;
        erroPreco.hidden = !mensagem;
        if (mensagem) campoPreco.setAttribute("aria-invalid", "true");
        else campoPreco.removeAttribute("aria-invalid");
        return !mensagem;
    }

    formPacote.addEventListener("submit", (evento) => {
        if (evento.submitter === document.getElementById("pacoteExcluirBtn")) return;
        erroPacote.hidden = true;
        campoTitulo.classList.remove("campo-invalido");

        const titulo = campoTitulo.value.trim();

        if(titulo === ""){
            evento.preventDefault();
            erroPacote.textContent = "Dê um nome ao seu pacote.";
            erroPacote.hidden = false;
            campoTitulo.classList.add("campo-invalido");
            campoTitulo.focus();
            return;
        }

        if(campoImagem.files[0] && campoImagem.files[0].size > MAX_TAMANHO_FOTO){
            evento.preventDefault();
            erroPacote.textContent = "A imagem passa de 5 MB. Escolha uma menor.";
            erroPacote.hidden = false;
            campoImagem.value = "";
            return;
        }

        if (!validarPreco()) {
            evento.preventDefault();
            campoPreco.focus();
            return;
        }

        // Enviando: bloqueia cliques repetidos e mostra "Salvando..."
        botaoSalvar.disabled = true;
        botaoSalvar.classList.add("salvando");
        botaoSalvar.querySelector("span").textContent = "Salvando...";
    });
}

// ---------- Modal Sobre: máscara de telefone, validação e feedback ----------

const campoTelefone = document.getElementById("campoTelefone");

const campoCidade = document.getElementById("campoCidade");
const listaCidades = document.getElementById("listaCidades");
const campoEstado = document.getElementById("campoEstado");
const listaEstados = document.getElementById("listaEstados");
if (campoCidade && listaCidades && campoEstado && listaEstados) {
    const opcoesCidade = Array.from(listaCidades.querySelectorAll("button[data-valor]"));

    function semAcento(texto) {
        return texto.toLocaleLowerCase("pt-BR").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    function configurarSugestoes(campo, lista, selecionar, podeMostrar = () => true) {
        const grupo = campo.closest(".campo-localizacao");
        const opcoes = Array.from(lista.querySelectorAll("button[data-valor]"));

        function filtrar(busca = "") {
            const termo = semAcento(busca.trim());
            let temOpcao = false;
            opcoes.forEach((opcao) => {
                const visivel = podeMostrar(opcao) && semAcento(opcao.dataset.valor).includes(termo);
                opcao.parentElement.hidden = !visivel;
                temOpcao ||= visivel;
            });
            lista.hidden = !temOpcao;
        }

        function fecharAoSair(evento) {
            if (!grupo.contains(evento.target)) lista.hidden = true;
        }

        campo.addEventListener("focus", () => filtrar());
        campo.addEventListener("click", () => {
            if (lista.hidden) filtrar();
        });
        campo.addEventListener("input", () => filtrar(campo.value));
        lista.addEventListener("click", (evento) => {
            const opcao = evento.target.closest("button[data-valor]");
            if (!opcao || opcao.parentElement.hidden) return;
            campo.value = opcao.dataset.valor;
            selecionar(opcao);
            campo.focus();
            lista.hidden = true;
        });
        grupo.addEventListener("keydown", (evento) => {
            if (evento.key === "Escape" && !lista.hidden) {
                campo.focus();
                lista.hidden = true;
            }
        });
        document.addEventListener("focusin", fecharAoSair);
        document.addEventListener("click", fecharAoSair);
    }

    configurarSugestoes(campoCidade, listaCidades, (opcao) => {
        campoEstado.value = opcao.dataset.uf;
    }, (opcao) => {
        const uf = campoEstado.value.trim().toUpperCase();
        return uf === "" || opcao.dataset.uf === uf;
    });

    configurarSugestoes(campoEstado, listaEstados, () => {
        const cidade = semAcento(campoCidade.value.trim());
        if (cidade && !opcoesCidade.some((opcao) => opcao.dataset.uf === campoEstado.value
            && semAcento(opcao.dataset.valor) === cidade)) {
            campoCidade.value = "";
        }
    });
}

// Validação antes de enviar + feedback visual no botão
const formSobre = document.getElementById("formSobre");
if(formSobre){
    const erroSobre = document.getElementById("erroSobre");
    const campoNome = document.getElementById("campoNome");
    const botaoSalvarSobre = document.getElementById("sobreSalvarBtn");
    const enderecoEspaco = document.getElementById("enderecoEspaco");
    const campoArea = formSobre.elements.espaco_area_m2;
    const erroEndereco = document.getElementById("erroEnderecoEspaco");
    const camposEnderecoObrigatorios = ["espaco_logradouro", "espaco_numero", "espaco_bairro", "campoCidade", "campoEstado"]
        .map((id) => document.getElementById(id));

    formSobre.querySelectorAll('input[name="campos[]"]').forEach((opcao) => {
        opcao.addEventListener("change", () => {
            if(!enderecoEspaco) return;
            const mostrarEndereco = opcao.value === "local";
            enderecoEspaco.hidden = !mostrarEndereco;
            enderecoEspaco.disabled = !mostrarEndereco;
            erroEndereco.hidden = true;
        });
    });

    formSobre.addEventListener("submit", (evento) => {
        erroSobre.hidden = true;
        erroEndereco.hidden = true;
        campoNome.classList.remove("campo-invalido");
        campoTelefone.classList.remove("campo-invalido");
        if(campoArea){
            campoArea.classList.remove("campo-invalido");
            campoArea.removeAttribute("aria-invalid");
        }
        camposEnderecoObrigatorios.forEach((campo) => {
            campo.classList.remove("campo-invalido");
            campo.removeAttribute("aria-invalid");
        });

        const nome = campoNome.value.trim();
        const telefone = campoTelefone.value.replace(/\D/g, "");

        if(nome === ""){
            evento.preventDefault();
            erroSobre.textContent = "Informe seu nome.";
            erroSobre.hidden = false;
            campoNome.classList.add("campo-invalido");
            campoNome.focus();
            return;
        }

        if(nome.length < 3){
            evento.preventDefault();
            erroSobre.textContent = "Nome muito curto (mínimo 3 letras).";
            erroSobre.hidden = false;
            campoNome.classList.add("campo-invalido");
            campoNome.focus();
            return;
        }

        // Telefone é opcional, mas se preenchido precisa ter DDD + número
        if(telefone !== "" && telefone.length < 10){
            evento.preventDefault();
            erroSobre.textContent = "Telefone incompleto: informe DDD + número.";
            erroSobre.hidden = false;
            campoTelefone.classList.add("campo-invalido");
            campoTelefone.focus();
            return;
        }

        if(enderecoEspaco && !enderecoEspaco.disabled){
            const campoFaltando = camposEnderecoObrigatorios.find((campo) => !campo.value.trim());
            if(campoFaltando){
                evento.preventDefault();
                erroEndereco.textContent = "Preencha o logradouro, número, bairro, cidade e UF do espaço.";
                erroEndereco.hidden = false;
                campoFaltando.classList.add("campo-invalido");
                campoFaltando.setAttribute("aria-invalid", "true");
                campoFaltando.focus();
                return;
            }
        }

        if (campoArea && enderecoEspaco && !enderecoEspaco.disabled && campoArea.value.trim() === "") {
            evento.preventDefault();
            erroEndereco.textContent = "Informe a área do espaço em m²: ela é obrigatória para locais de festas.";
            erroEndereco.hidden = false;
            campoArea.classList.add("campo-invalido");
            campoArea.setAttribute("aria-invalid", "true");
            campoArea.focus();
            return;
        }

        if (campoArea && !enderecoEspaco.disabled && campoArea.value.trim() !== ""
            && (!campoArea.validity.valid || Number(campoArea.value.replace(/\./g, "").replace(",", ".")) <= 0)) {
            evento.preventDefault();
            erroSobre.textContent = "Informe uma área maior que zero, com até duas casas decimais.";
            erroSobre.hidden = false;
            campoArea.focus();
            return;
        }

        // Enviando: bloqueia cliques repetidos e mostra "Salvando..."
        botaoSalvarSobre.disabled = true;
        botaoSalvarSobre.classList.add("salvando");
        botaoSalvarSobre.querySelector("span").textContent = "Salvando...";
    });
}

// ---------- Galeria: confirmação antes de apagar a foto ----------

document.querySelectorAll(".galeria-apagar-formulario").forEach((form) => {
    form.addEventListener("submit", (evento) => {
        if(!confirm("Tem certeza que deseja apagar esta foto? Essa ação não pode ser desfeita.")){
            evento.preventDefault();
        }
    });
});

// ---------- Pacotes: botão Excluir dentro do modal de edição ----------

const pacoteExcluirBtn = document.getElementById("pacoteExcluirBtn");
if(pacoteExcluirBtn){
    pacoteExcluirBtn.addEventListener("click", (evento) => {
        if(!confirm("Tem certeza que deseja excluir este pacote? Essa ação não pode ser desfeita.")){
            evento.preventDefault();
        }
    });
}

// ---------- Galerias: envia o formulário ao escolher a foto ----------

const inputGaleria = document.getElementById("inputGaleria");
if(inputGaleria){
    inputGaleria.addEventListener("change", () => inputGaleria.form.submit());
}



// Editor de enquadramento para imagens dos pacotes e da galeria.
// O arquivo só é enviado depois de a pessoa confirmar o recorte.
(() => {
    const pacoteInput = document.getElementById("pacoteImagemInput");
    const galeriaInput = document.getElementById("inputGaleria");
    const editarInput = document.getElementById("arquivoEditarGaleria");
    const editarOriginalInput = document.getElementById("arquivoEditarGaleriaOriginal");
    const galeriaOriginalInput = document.getElementById("inputGaleriaOriginal");
    const editarForm = document.getElementById("formEditarGaleria");
    let modo = "", arquivoOriginal = null, imagem = new Image(), zoom = 1, offsetX = 0, offsetY = 0;
    let arrastando = false, inicioX = 0, inicioY = 0, blobAtual = null, nomeAtual = "imagem.jpg";

    const modal = document.createElement("div");
    modal.className = "editor-recorte-overlay";
    modal.id = "editorRecorteImagem";
    modal.innerHTML = `
      <div class="editor-recorte" role="dialog" aria-modal="true" aria-labelledby="tituloRecorte">
        <header class="editor-recorte-topo">
          <button type="button" class="recorte-cancelar recorte-voltar" aria-label="Voltar" title="Voltar">
            <span aria-hidden="true">←</span>
          </button>
          <h3 id="tituloRecorte">Editar imagem</h3>
          <button type="button" class="recorte-confirmar">Aplicar</button>
        </header>
        <div class="editor-recorte-area">
          <canvas width="1200" height="675" aria-label="Prévia do enquadramento da imagem"></canvas>
        </div>
        <footer class="editor-recorte-rodape">
          <button type="button" class="zoom-menos" aria-label="Diminuir zoom" title="Diminuir zoom">⌕−</button>
          <input class="editor-zoom-controle" type="range" min="1" max="3" step="0.01" value="1" aria-label="Zoom da imagem">
          <button type="button" class="zoom-mais" aria-label="Aumentar zoom" title="Aumentar zoom">⌕+</button>
        </footer>
      </div>`;
    document.body.appendChild(modal);
    const canvas = modal.querySelector("canvas"), ctx = canvas.getContext("2d");
    const zoomInput = modal.querySelector('input[type="range"]');
    const bannerInput = document.getElementById("inputBannerModal");
    const fotoInput = document.getElementById("inputFotoModal");
    const removerBanner = document.getElementById("removerBanner");
    const aspectos = { banner: 1600 / 560, foto: 1, pacote: 16 / 9, "galeria-nova": 16 / 9, "galeria-editar": 16 / 9 };
    let crop = { x: 0, y: 0, w: 1, h: 1 };
    let stage = { w: 1, h: 1, dpr: 1 };
    let originalBanner = null;
    let bannerPendente = false;
    let fotoPendente = false;

    function medir() {
        const area = canvas.parentElement;
        const sw = area.clientWidth;
        const sh = area.clientHeight;
        if (sw < 20 || sh < 20) return false;
        const ratio = aspectos[modo] || (16 / 9);
        let fw = sw * 0.82;
        let fh = fw / ratio;
        const limite = sh * 0.72;
        if (fh > limite) { fh = limite; fw = fh * ratio; }
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        stage = { w: sw, h: sh, dpr };
        crop = { x: (sw - fw) / 2, y: (sh - fh) / 2, w: fw, h: fh };
        canvas.width = Math.max(1, Math.round(sw * dpr));
        canvas.height = Math.max(1, Math.round(sh * dpr));
        canvas.style.width = sw + "px";
        canvas.style.height = sh + "px";
        return true;
    }
    function desenhar() {
        if (!imagem.naturalWidth || !medir()) return;
        ctx.setTransform(stage.dpr, 0, 0, stage.dpr, 0, 0);
        ctx.fillStyle = "#100d24";
        ctx.fillRect(0, 0, stage.w, stage.h);
        const base = Math.max(crop.w / imagem.naturalWidth, crop.h / imagem.naturalHeight);
        const escala = base * zoom;
        const dw = imagem.naturalWidth * escala, dh = imagem.naturalHeight * escala;
        const maxX = Math.max(0, (dw - crop.w) / 2), maxY = Math.max(0, (dh - crop.h) / 2);
        offsetX = Math.max(-maxX, Math.min(maxX, offsetX));
        offsetY = Math.max(-maxY, Math.min(maxY, offsetY));
        ctx.drawImage(imagem, crop.x + (crop.w - dw) / 2 + offsetX, crop.y + (crop.h - dh) / 2 + offsetY, dw, dh);
        ctx.strokeStyle = "rgba(255,255,255,.95)";
        ctx.lineWidth = 2;
        ctx.strokeRect(crop.x + 1, crop.y + 1, crop.w - 2, crop.h - 2);
    }
    function abrir(file, modoNovo) {
        if (!file || !file.type.startsWith("image/")) return;
        arquivoOriginal = file; modo = modoNovo; zoom = 1; offsetX = 0; offsetY = 0;
        zoomInput.value = "1"; nomeAtual = file.name || "imagem.jpg";
        imagem = new Image();
        imagem.onload = () => {
            modal.classList.add("aberto");
            requestAnimationFrame(desenhar);
        };
        imagem.onerror = () => alert("Não foi possível abrir essa imagem. Tente outro arquivo.");
        imagem.src = URL.createObjectURL(file);
    }
    async function abrirEndereco(url, modoNovo) {
        const resposta = await fetch(url, { cache: "no-store" });
        if (!resposta.ok) throw new Error("image");
        const blob = await resposta.blob();
        const arquivo = new File([blob], "imagem.jpg", { type: blob.type || "image/jpeg" });
        if (modoNovo === "banner") originalBanner = arquivo;
        abrir(arquivo, modoNovo);
    }
    function arquivoRecortado(callback) {
        const saidaLargura = modo === "foto" ? 800 : modo === "banner" ? 1600 : 1400;
        const saidaAltura = Math.max(1, Math.round(saidaLargura / (aspectos[modo] || (16 / 9))));
        const saida = document.createElement("canvas");
        saida.width = saidaLargura;
        saida.height = saidaAltura;
        const contexto = saida.getContext("2d");
        const base = Math.max(crop.w / imagem.naturalWidth, crop.h / imagem.naturalHeight);
        const escala = base * zoom;
        const dw = imagem.naturalWidth * escala;
        const dh = imagem.naturalHeight * escala;
        const proporcao = saidaLargura / crop.w;
        contexto.fillStyle = "#100d24";
        contexto.fillRect(0, 0, saidaLargura, saidaAltura);
        contexto.drawImage(
            imagem,
            ((crop.w - dw) / 2 + offsetX) * proporcao,
            ((crop.h - dh) / 2 + offsetY) * proporcao,
            dw * proporcao,
            dh * proporcao
        );
        saida.toBlob(callback, "image/jpeg", 0.92);
    }
    function confirmar() {
        arquivoRecortado(blob => {
            if (!blob) { alert("Não foi possível ajustar essa imagem."); return; }
            blobAtual = blob;
            const tipo = "image/jpeg";
            const arquivo = new File([blob], (nomeAtual.replace(/\.[^.]+$/, "") || "imagem") + "-ajustada.jpg", {type:tipo});
            const transfer = new DataTransfer(); transfer.items.add(arquivo);
            if (modo === "pacote") {
                pacoteInput.files = transfer.files;
                pacoteInput.dataset.recortePronto = "1";
                pacoteInput.dispatchEvent(new Event("change", {bubbles:true}));
            } else if (modo === "banner") {
                bannerInput.files = transfer.files;
                originalBanner = arquivoOriginal;
                bannerPendente = false;
                if (removerBanner) removerBanner.value = "";
                aplicarPreviewBanner(arquivo);
            } else if (modo === "foto") {
                fotoInput.files = transfer.files;
                fotoPendente = false;
                aplicarPreviewFoto(arquivo);
            } else if (modo === "galeria-nova") {
                galeriaInput.files = transfer.files;
                if (galeriaOriginalInput && arquivoOriginal) {
                    const originalTransfer = new DataTransfer();
                    originalTransfer.items.add(arquivoOriginal);
                    galeriaOriginalInput.files = originalTransfer.files;
                }
                modal.classList.remove("aberto");
                galeriaInput.form.submit();
                return;
            } else if (modo === "galeria-editar") {
                editarInput.files = transfer.files;
                if (editarOriginalInput && arquivoOriginal) {
                    const originalTransfer = new DataTransfer();
                    originalTransfer.items.add(arquivoOriginal);
                    editarOriginalInput.files = originalTransfer.files;
                }
                modal.classList.remove("aberto");
                editarForm.submit();
                return;
            }
            modal.classList.remove("aberto");
        });
    }
    if (pacoteInput) pacoteInput.addEventListener("change", e => {
        if (e.target.dataset.recortePronto === "1") { delete e.target.dataset.recortePronto; return; }
        const file = e.target.files && e.target.files[0];
        if (file) abrir(file, "pacote");
    }, true);
    // Intercepta o envio automático antigo da galeria para abrir primeiro o editor.
    if (galeriaInput) galeriaInput.addEventListener("change", e => {
        if (e.target.dataset.recortePronto === "1") { delete e.target.dataset.recortePronto; return; }
        const file = e.target.files && e.target.files[0];
        if (file) { e.stopImmediatePropagation(); e.preventDefault(); abrir(file, "galeria-nova"); }
    }, true);
    function aoEscolherArquivo(input, modoNovo) {
        input.addEventListener("change", e => {
            if (e.target.dataset.recortePronto === "1") { delete e.target.dataset.recortePronto; return; }
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            e.stopImmediatePropagation();
            e.preventDefault();
            if (modoNovo === "banner") bannerPendente = true;
            if (modoNovo === "foto") fotoPendente = true;
            abrir(file, modoNovo);
        }, true);
    }
    if (bannerInput) aoEscolherArquivo(bannerInput, "banner");
    if (fotoInput) aoEscolherArquivo(fotoInput, "foto");
    const btnTrocarBanner = document.getElementById("btnTrocarBanner");
    const btnEnquadrarBanner = document.getElementById("btnEnquadrarBanner");
    const btnRemoverBanner = document.getElementById("btnRemoverBanner");
    if (btnTrocarBanner && bannerInput) btnTrocarBanner.addEventListener("click", () => bannerInput.click());
    if (btnEnquadrarBanner) btnEnquadrarBanner.addEventListener("click", async () => {
        try {
            if (originalBanner) { abrir(originalBanner, "banner"); return; }
            const fonte = document.getElementById("previewBannerModal");
            const url = fonte ? fonte.dataset.src : "";
            if (!url) return;
            await abrirEndereco(url, "banner");
        } catch (_) { alert("Não foi possível abrir o banner para edição."); }
    });
    if (btnRemoverBanner) btnRemoverBanner.addEventListener("click", () => {
        if (bannerInput) bannerInput.value = "";
        if (removerBanner) removerBanner.value = "1";
        originalBanner = null;
        aplicarPreviewBanner(null);
    });
    document.querySelectorAll(".galeria-editar").forEach(btn => btn.addEventListener("click", async () => {
        try {
            const baseSrc = btn.dataset.fotoBaseSrc || btn.dataset.fotoSrc;
            let response = await fetch(baseSrc, {cache:"no-store"});
            if (!response.ok && btn.dataset.fotoSrc && baseSrc !== btn.dataset.fotoSrc) {
                response = await fetch(btn.dataset.fotoSrc, {cache:"no-store"});
            }
            if (!response.ok) throw new Error("image");
            const blob = await response.blob();
            document.getElementById("idFotoGaleria").value = btn.dataset.fotoId;
            abrir(new File([blob], "galeria.jpg", {type:blob.type || "image/jpeg"}), "galeria-editar");
        } catch (_) { alert("Não foi possível abrir essa imagem para edição."); }
    }));
    zoomInput.addEventListener("input", () => { zoom = Number(zoomInput.value); desenhar(); });
    modal.querySelector(".zoom-menos").addEventListener("click", () => {
        zoomInput.value = String(Math.max(Number(zoomInput.min), Number(zoomInput.value) - 0.15));
        zoom = Number(zoomInput.value); desenhar();
    });
    modal.querySelector(".zoom-mais").addEventListener("click", () => {
        zoomInput.value = String(Math.min(Number(zoomInput.max), Number(zoomInput.value) + 0.15));
        zoom = Number(zoomInput.value); desenhar();
    });
    canvas.addEventListener("pointerdown", e => { arrastando = true; inicioX = e.clientX - offsetX; inicioY = e.clientY - offsetY; canvas.setPointerCapture(e.pointerId); });
    canvas.addEventListener("pointermove", e => { if (!arrastando) return; offsetX = e.clientX - inicioX; offsetY = e.clientY - inicioY; desenhar(); });
    canvas.addEventListener("pointerup", () => arrastando = false);
    canvas.addEventListener("pointercancel", () => arrastando = false);
    modal.querySelector(".recorte-cancelar").addEventListener("click", () => {
        modal.classList.remove("aberto");
        if (modo === "pacote" && pacoteInput) pacoteInput.value = "";
        if (modo === "galeria-nova" && galeriaInput) galeriaInput.value = "";
        if (modo === "banner" && bannerPendente && bannerInput) { bannerInput.value = ""; bannerPendente = false; }
        if (modo === "foto" && fotoPendente && fotoInput) { fotoInput.value = ""; fotoPendente = false; }
    });
    window.addEventListener("resize", () => { if (modal.classList.contains("aberto")) desenhar(); });
    modal.querySelector(".recorte-confirmar").addEventListener("click", confirmar);
    modal.addEventListener("click", e => { if (e.target === modal) modal.querySelector(".recorte-cancelar").click(); });
})();
