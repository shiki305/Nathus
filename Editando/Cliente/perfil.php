<?php
require_once __DIR__ . "/dados.php";
require_once __DIR__ . "/../Includes/endereco-espaco.php";

$area_espaco = area_espaco_formatada($promotor["espaco_area_m2"] ?? null);
if ($area_espaco !== "" && !promotor_eh_espaco($conexao, $id_promotor)) {
    $area_espaco = "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["enviar_avaliacao"])) {
    $nota = (int) ($_POST["nota"] ?? 0);
    if ($nota >= 1 && $nota <= 5 && $mostrar_avaliar) {
        $_SESSION["avaliacao_promotor"][$id_promotor] = [
            "nota" => $nota,
            "data" => date("Y-m-d H:i:s"),
        ];
        if ($id_promotor > 0) {
            $total = $total_avaliacoes + 1;
            $media = (((float) ($promotor["nota_media"] ?? 0) * $total_avaliacoes) + $nota) / $total;
            $updNota = $conexao->prepare("UPDATE promotor SET nota_media = ?, total_avaliacoes = ? WHERE id = ?");
            if ($updNota) {
                $updNota->bind_param("dii", $media, $total, $id_promotor);
                $updNota->execute();
            }
        }
        header("Location: perfil.php?id=" . $id_promotor . "&avaliado=1");
        exit;
    }
}
$base_url = "../";
$vt_inicio = "../home/homeAnt.php";
$vt_acao = "../home/salvar-conta.php";
$vt_voltar = "../Cliente/perfil.php?id=" . (int) $id_promotor;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nome_promotor); ?> | Nathus</title>
    <link rel="icon" type="image/png" href="../assets/favicon.png?v=2">
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="../home/css/vitrine.css?v=<?php echo filemtime(__DIR__ . "/../home/css/vitrine.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/botao-voltar.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/botao-voltar.css"); ?>">
    <link rel="stylesheet" href="css/perfil.css?v=<?php echo filemtime(__DIR__ . "/css/perfil.css"); ?>">
</head>
<body class="vt perfil-publico">
    <?php include __DIR__ . "/../home/topo-vitrine.php"; ?>

<main class="pagina" id="pagina">
    <?php
        $href_voltar = "../home/home.php?campo=" . rawurlencode($campo_lista);
        if (!empty($eh_dono_perfil) && (($_GET["de"] ?? "") === "painel" || !isset($_GET["campo"]))) {
            $href_voltar = "../Perfil/perfil.php";
        }
    ?>
    <a class="botao-voltar" href="<?php echo htmlspecialchars($href_voltar, ENT_QUOTES, "UTF-8"); ?>">Voltar</a>
    <section class="card-topo" id="card-topo">
        <div class="capa" id="capa">
            <?php if ($banner): ?>
                <button class="abrir-foto" type="button" data-foto="<?php echo htmlspecialchars($banner); ?>" aria-label="Ampliar banner">
                    <img src="<?php echo htmlspecialchars($banner); ?>" alt="">
                </button>
            <?php endif; ?>
        </div>
        <?php
            $nome_pessoa = trim($promotor["nome_cliente"] ?? "");
            $nome_titulo = trim($nome_promotor);
        ?>
        <div class="corpo-topo" id="info-perfil">
            <div class="foto-perfil <?php echo $tem_foto ? "" : "foto--vazia"; ?>" id="foto-perfil">
                <?php if ($tem_foto): ?>
                    <button class="abrir-foto" type="button" data-foto="<?php echo htmlspecialchars($foto_perfil); ?>" aria-label="Ampliar foto de perfil">
                        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="<?php echo htmlspecialchars($nome_promotor); ?>">
                    </button>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="<?php echo htmlspecialchars($nome_promotor); ?>">
                <?php endif; ?>
            </div>
            <div class="linha-topo">
                <div class="dados-topo">
                    <div class="bloco-nome">
                        <h1 class="nome" id="nome"><?php echo htmlspecialchars($nome_titulo); ?></h1>
                        <?php if ($mostrar_avaliar): ?>
                            <button class="btn-avaliar" id="botao-avaliar" type="button">
                                <img src="<?php echo $base_url; ?>assets/avaliar_perfil.svg" alt="">
                                Avaliar Promotor
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if ($nome_pessoa !== "" && $nome_pessoa !== $nome_titulo): ?>
                        <p class="nome-pessoa"><?php echo htmlspecialchars($nome_pessoa); ?></p>
                    <?php endif; ?>
                    <div class="meta" id="meta">
                        <span class="item-meta">
                            <img src="<?php echo $base_url; ?>assets/estrela_perfilprom.svg" alt="">
                            <b><?php echo $nota_media; ?></b> (<?php echo $total_avaliacoes; ?> avaliações)
                        </span>
                        <span class="item-meta">
                            <img src="<?php echo $base_url; ?>assets/local_perfilprom.svg" alt="">
                            <?php echo htmlspecialchars($localizacao); ?>
                        </span>
                        <?php
                        $telefone_perfil = $promotor['telefone_promotor'] ?? '';
                        include __DIR__ . '/../Includes/telefone-perfil.php';
                        ?>
                    </div>
                </div>
                <div class="preco-minimo" id="preco-minimo">
                    <img src="<?php echo $base_url; ?>assets/perfil-bolsa.png" alt="">
                    <div>
                        <span>Preço mínimo por evento</span>
                        <strong><?php echo $preco_minimo_fmt; ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card" id="sobre">
        <div class="titulo-secao">
            <h2>Sobre mim</h2>
        </div>
        <?php if (!empty($promotor["frase_destaque"])): ?>
            <h3 class="titulo-sobre"><?php echo htmlspecialchars($promotor["frase_destaque"]); ?></h3>
        <?php endif; ?>
        <p class="texto-sobre"><?php echo nl2br(htmlspecialchars($promotor["descricao"] ?? "")); ?></p>

        <div class="titulo-secao titulo-especialidades">
            <h2>Especialidades</h2>
        </div>
        <div class="tags" id="especialidades">
            <?php if (!$especialidades && $capacidade_maxima <= 0 && $area_espaco === ""): ?>
                <span class="tag">Nenhuma especialidade cadastrada</span>
            <?php endif; ?>
            <?php foreach ($especialidades as $esp): ?>
                <span class="tag">
                    <?php if (!empty($esp["icone"])): ?>
                        <span class="icone-especialidade" data-icone="<?= htmlspecialchars($esp['icone']) ?>" aria-hidden="true"></span>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($esp["label"]); ?>
                </span>
            <?php endforeach; ?>
            <?php if ($area_espaco !== ""): ?>
                <span class="tag">Área: <?= htmlspecialchars($area_espaco) ?> m²</span>
            <?php endif; ?>
            <?php if ($capacidade_maxima > 0): ?>
                <span class="tag">Até <?php echo $capacidade_maxima; ?> convidados</span>
            <?php endif; ?>
        </div>
    </section>

    <section class="card" id="pacotes">
        <div class="titulo-secao">
            <h2>Pacotes de Serviços</h2>
        </div>
        <?php if (!$pacotes): ?>
            <p>Nenhum pacote cadastrado.</p>
        <?php else: ?>
        <div class="grade-pacotes">
            <?php foreach ($pacotes as $pacote): ?>
                <?php
                    $imagem_pacote = midia_publica($pacote["imagem"] ?? "", ["pacotes"], $base_url);
                    $preco_pacote = is_numeric($pacote["preco"])
                        ? "R$ " . number_format((float) $pacote["preco"], 2, ",", ".")
                        : (trim((string) ($pacote["preco"] ?? "")) !== "" ? $pacote["preco"] : "Preço sob consulta");
                ?>
                <article class="card-pacote" id="pacote-<?php echo (int) $pacote["id"]; ?>">
                    <h3><?php echo htmlspecialchars($pacote["titulo"]); ?></h3>
                    <?php if ($imagem_pacote !== ""): ?>
                    <button class="foto-pacote abrir-foto" type="button" data-grupo="pacotes" data-foto="<?php echo htmlspecialchars($imagem_pacote); ?>" aria-label="Ampliar foto do pacote">
                        <img src="<?php echo htmlspecialchars($imagem_pacote); ?>" alt="">
                    </button>
                    <?php else: ?>
                    <div class="foto-pacote foto--vazia">
                        <span class="foto-pacote-vazio-texto">Sem imagem</span>
                    </div>
                    <?php endif; ?>
                    <?php include __DIR__ . '/../Includes/itens-pacote.php'; ?>
                    <div class="preco-pacote">
                        <img src="<?php echo $base_url; ?>assets/perfil-bolsa.png" alt="">
                        <?php echo htmlspecialchars($preco_pacote); ?>
                    </div>
                    <?php if ($eh_dono_perfil): ?>
                        <span class="btn-solicitar desativado" aria-disabled="true" title="Este é o seu perfil">Solicitar Serviço</span>
                    <?php elseif (!empty($_SESSION["usuario_id"]) && obterTipoConta($conexao, (int) $_SESSION["usuario_id"]) === "promotor"): ?>
                        <span class="btn-solicitar desativado" aria-disabled="true" title="A solicitação é enviada pela conta do cliente">Somente clientes</span>
                    <?php else: ?>
                        <a class="btn-solicitar" href="solicitacao.php?pacote=<?php echo (int) $pacote["id"]; ?>&id=<?php echo $id_promotor; ?>">
                            <?php echo ($status === "pendente" || $status === "aceita") ? "Nova solicitação" : "Solicitar Serviço"; ?>
                        </a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <?php
    $fotos_galeria = [];
    foreach ($galeria as $foto) {
        $url_galeria = midia_publica($foto["arquivo"] ?? "", ["galeria"], $base_url);
        if ($url_galeria !== "") {
            $fotos_galeria[] = $url_galeria;
        }
    }
    ?>
    <section class="card" id="galeria">
        <div class="titulo-secao">
            <h2>Galeria</h2>
        </div>
        <?php if (!$fotos_galeria): ?>
            <p>Nenhuma foto cadastrada na galeria.</p>
        <?php else: ?>
        <div class="grade-galeria">
            <?php foreach ($fotos_galeria as $i => $foto): ?>
                <div class="item-galeria">
                    <button class="abrir-foto" type="button" data-grupo="galeria" data-foto="<?php echo htmlspecialchars($foto); ?>" aria-label="Ampliar foto <?php echo $i + 1; ?> da galeria">
                        <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto <?php echo $i + 1; ?> da galeria">
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php if ($enviado): ?>
<div class="fundo-modal aberto" id="fundo-sucesso">
    <div class="modal-sucesso" id="modal-sucesso" role="dialog">
        <span class="icone-solicitacao-enviada" aria-hidden="true"></span>
        <p>Solicitação Enviada com Sucesso</p>
    </div>
</div>
<?php endif; ?>

<?php if ($avaliado): ?>
<div class="fundo-modal aberto" id="fundo-avaliado">
    <div class="modal-sucesso" role="dialog">
        <img src="<?php echo $base_url; ?>assets/check-dourado.png" alt="">
        <p>Avaliação enviada. Obrigado!</p>
    </div>
</div>
<?php endif; ?>

<?php if ($mostrar_avaliar): ?>
<div class="fundo-modal" id="fundo-avaliar">
    <div class="modal-avaliar" id="modal-avaliar" role="dialog">
        <img class="foto-modal <?php echo $tem_foto ? "" : "foto--vazia"; ?>" src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="<?php echo htmlspecialchars($nome_promotor); ?>">
        <h2><?php echo htmlspecialchars($nome_promotor); ?></h2>
        <p class="cargo">Organizadora · ★ <?php echo $nota_media; ?> (<?php echo $total_avaliacoes; ?>)</p>
        <form method="post" action="perfil.php?id=<?php echo $id_promotor; ?>" id="form-avaliar">
            <input type="hidden" name="nota" id="campo-nota" value="0">
            <div class="estrelas" id="estrelas">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button class="estrela" type="button" data-nota="<?php echo $i; ?>" aria-label="<?php echo $i; ?> estrela">
                        <img class="estrela--vazia" src="<?php echo $base_url; ?>assets/av2_perfil.svg" alt="">
                        <img class="estrela--cheia" src="<?php echo $base_url; ?>assets/av1_perfil.svg" alt="">
                    </button>
                <?php endfor; ?>
            </div>
            <div class="acoes-modal">
                <button class="btn-enviar" name="enviar_avaliacao" type="submit">Enviar</button>
                <button class="btn-cancelar" id="botao-cancelar" type="button">Cancelar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="visor-foto" id="visor-foto" hidden>
    <button class="visor-foto-fechar" type="button" aria-label="Fechar">×</button>
    <button class="visor-foto-nav visor-foto-anterior" type="button" aria-label="Foto anterior" hidden>‹</button>
    <img alt="">
    <button class="visor-foto-nav visor-foto-proxima" type="button" aria-label="Próxima foto" hidden>›</button>
</div>

<script>
(function () {
    var fundoSucesso = document.getElementById("fundo-sucesso");
    var fundoAvaliado = document.getElementById("fundo-avaliado");
    var fundoAvaliar = document.getElementById("fundo-avaliar");
    var botaoAvaliar = document.getElementById("botao-avaliar");
    var botaoCancelar = document.getElementById("botao-cancelar");
    var campoNota = document.getElementById("campo-nota");
    var estrelas = document.querySelectorAll(".estrela");

    function fechar(el) {
        if (el) el.classList.remove("aberto");
    }
    if (fundoSucesso) {
        try {
            localStorage.removeItem("nathus_solicitacao_rascunho");
        } catch (erro) {
            // O aviso de sucesso independe do armazenamento local.
        }
        fundoSucesso.addEventListener("click", function (e) {
            if (e.target === fundoSucesso) fechar(fundoSucesso);
        });
        setTimeout(function () { fechar(fundoSucesso); }, 2800);
    }
    if (fundoAvaliado) {
        fundoAvaliado.addEventListener("click", function (e) {
            if (e.target === fundoAvaliado) fechar(fundoAvaliado);
        });
        setTimeout(function () { fechar(fundoAvaliado); }, 2800);
    }
    if (botaoAvaliar) {
        botaoAvaliar.addEventListener("click", function () {
            fundoAvaliar.classList.add("aberto");
        });
    }
    if (botaoCancelar) {
        botaoCancelar.addEventListener("click", function () { fechar(fundoAvaliar); });
    }
    if (fundoAvaliar) {
        fundoAvaliar.addEventListener("click", function (e) {
            if (e.target === fundoAvaliar) fechar(fundoAvaliar);
        });
        setTimeout(function () { fechar(fundoAvaliar); }, 2800);
    }
    estrelas.forEach(function (botao) {
        botao.addEventListener("click", function () {
            var nota = Number(botao.getAttribute("data-nota"));
            campoNota.value = nota;
            estrelas.forEach(function (item) {
                item.classList.toggle("ativa", Number(item.getAttribute("data-nota")) <= nota);
            });
        });
    });

    var visor = document.getElementById("visor-foto");
    if (visor) {
        var fotoVisor = visor.querySelector("img");
        var botaoFechar = visor.querySelector(".visor-foto-fechar");
        var botaoAnterior = visor.querySelector(".visor-foto-anterior");
        var botaoProxima = visor.querySelector(".visor-foto-proxima");
        var fotosVisor = [];
        var indiceVisor = 0;

        function mostrarFoto() {
            fotoVisor.src = fotosVisor[indiceVisor] || "";
            var varias = fotosVisor.length > 1;
            botaoAnterior.hidden = !varias;
            botaoProxima.hidden = !varias;
        }

        function abrirVisor(urls, indice) {
            fotosVisor = urls.filter(function (url) { return url; });
            if (!fotosVisor.length) return;
            indiceVisor = Math.max(0, Math.min(indice, fotosVisor.length - 1));
            mostrarFoto();
            visor.hidden = false;
            visor.classList.add("aberto");
            document.body.classList.add("visor-aberto");
        }

        function fecharVisor() {
            visor.classList.remove("aberto");
            visor.hidden = true;
            fotoVisor.removeAttribute("src");
            document.body.classList.remove("visor-aberto");
        }

        document.querySelectorAll(".abrir-foto").forEach(function (botao) {
            botao.addEventListener("click", function () {
                var grupo = botao.getAttribute("data-grupo");
                var urls = [botao.getAttribute("data-foto")];
                var indice = 0;
                if (grupo) {
                    var irmaos = document.querySelectorAll('.abrir-foto[data-grupo="' + grupo + '"]');
                    urls = [];
                    irmaos.forEach(function (item, i) {
                        urls.push(item.getAttribute("data-foto"));
                        if (item === botao) indice = i;
                    });
                }
                abrirVisor(urls, indice);
            });
        });

        botaoFechar.addEventListener("click", fecharVisor);
        botaoAnterior.addEventListener("click", function () {
            indiceVisor = (indiceVisor - 1 + fotosVisor.length) % fotosVisor.length;
            mostrarFoto();
        });
        botaoProxima.addEventListener("click", function () {
            indiceVisor = (indiceVisor + 1) % fotosVisor.length;
            mostrarFoto();
        });
        visor.addEventListener("click", function (e) {
            if (e.target === visor) fecharVisor();
        });
        document.addEventListener("keydown", function (e) {
            if (visor.hidden) return;
            if (e.key === "Escape") fecharVisor();
            if (e.key === "ArrowLeft" && fotosVisor.length > 1) botaoAnterior.click();
            if (e.key === "ArrowRight" && fotosVisor.length > 1) botaoProxima.click();
        });
    }
})();
</script>
<?php include __DIR__ . "/../home/conta-modal.php"; ?>
</body>
</html>
