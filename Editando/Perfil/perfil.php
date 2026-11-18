<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/perfil_foto.php");
include_once("../Includes/imagens.php");
include_once("../Includes/promotor.php");
include_once("../Includes/cidades.php");
require_once __DIR__ . '/../Includes/endereco-espaco.php';
require_once __DIR__ . '/../Includes/moeda.php';

// Página exclusiva de PROMOTOR: cliente é levado ao "Explorar".
$id_promotor_aux = exigirPromotor($conexao);
$id_cliente_logado = (int) $_SESSION["usuario_id"];
$_SESSION["csrf_sobre"] ??= bin2hex(random_bytes(32));

$base_url = "../";
$pagina_atual = "perfil";

// Limpa registros cujos arquivos sumiram da pasta uploads (galeria, pacotes,
// banner e foto de perfil) ANTES de qualquer leitura/cache de imagem.
limpar_uploads_orfaos($conexao, $id_promotor_aux);

$sql = "SELECT c.*, p.id AS id_promotor, p.nome_fantasia, p.cnpj,
               p.descricao, p.banner, p.frase_destaque, p.cidade, p.estado,
               precos.preco_minimo, p.capacidade_maxima, p.nota_media, p.total_avaliacoes,
               p.espaco_logradouro, p.espaco_numero, p.espaco_bairro, p.espaco_complemento, p.espaco_area_m2
        FROM cliente c
        LEFT JOIN promotor p ON p.id_cliente = c.id
        LEFT JOIN (" . SQL_PRECOS_MINIMOS . ") precos ON precos.id_promotor = p.id
        WHERE c.id = ?
        LIMIT 1";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_cliente_logado);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Sessão apontando para conta inexistente: encerra em vez de renderizar vazia.
if(!$usuario){
    header("Location: ../Includes/logout.php");
    exit;
}
$id_promotor = $usuario["id_promotor"] ?? null;

// O título do painel segue o campo Nome, que é o que o promotor edita.
// Nome fantasia diferente aparece logo abaixo e segue sendo o nome público.
$titulo_perfil = trim((string) ($usuario["nome"] ?? ""));
if ($titulo_perfil === "") {
    $titulo_perfil = trim((string) ($usuario["nome_fantasia"] ?? ""));
}
if ($titulo_perfil === "") {
    $titulo_perfil = "Usuário";
}
$fantasia_perfil = trim((string) ($usuario["nome_fantasia"] ?? ""));
if (mb_strtolower($fantasia_perfil) === mb_strtolower($titulo_perfil)) {
    $fantasia_perfil = "";
}

$pasta_uploads = __DIR__ . "/../uploads/";

[$foto_perfil, $tem_foto_perfil] = resolver_foto_perfil($conexao);

$banner = banner_publico_promotor($usuario["banner"] ?? "", $base_url);

require_once __DIR__ . "/../Includes/tipos-festa.php";
$rotulos_especialidade = TIPOS_FESTA + [
    "massas"    => ["label" => "Massas",            "icone" => ""],
    "infantil"  => ["label" => "Buffet infantil",   "icone" => ""],
    "drinks"    => ["label" => "Buffet de drinks",  "icone" => ""],
    "pratos"    => ["label" => "Pratos principais", "icone" => ""],
    "geral"     => ["label" => "Buffet geral",      "icone" => ""],
];
$rotulos_buffet = [
    "massas" => "Massas",
    "infantil" => "Buffet infantil",
    "drinks" => "Buffet de drinks",
    "pratos" => "Pratos principais",
    "geral" => "Buffet geral",
];

$rotulos_atuacao = [
    "organizacao" => "Organizador",
    "buffet"      => "Buffet",
    "local"       => "Local",
];

$campos_marcados = [];
if($id_promotor){
    $stmtCampos = $conexao->prepare("SELECT campo FROM promotor_campo WHERE id_promotor = ? ORDER BY id");
    $stmtCampos->bind_param("i", $id_promotor);
    $stmtCampos->execute();
    $resCampos = $stmtCampos->get_result();
    while($linha = $resCampos->fetch_assoc()){
        if(isset($rotulos_atuacao[$linha["campo"]])){
            $campos_marcados[] = $linha["campo"];
        }
    }
}
$eh_espaco = in_array("local", $campos_marcados, true);
$campo_unico = $eh_espaco ? "local" : ($campos_marcados[0] ?? "");
$area_espaco = $eh_espaco ? area_espaco_formatada($usuario["espaco_area_m2"] ?? null) : "";
$pacote_requer_endereco = $eh_espaco && (endereco_espaco_formatado($usuario) === null || $area_espaco === "");

$especialidades = [];
$especialidades_ativas = [];
if($id_promotor){
    $stmtEsp = $conexao->prepare("SELECT tipo FROM promotor_especialidade WHERE id_promotor = ?");
    $stmtEsp->bind_param("i", $id_promotor);
    $stmtEsp->execute();
    $resEsp = $stmtEsp->get_result();
    while($linha = $resEsp->fetch_assoc()){
        if(isset($rotulos_especialidade[$linha["tipo"]])){
            $especialidades[] = $rotulos_especialidade[$linha["tipo"]];
            $especialidades_ativas[] = $linha["tipo"];
        }
    }
}

$pacotes = [];
if($id_promotor){
    $stmtPac = $conexao->prepare("SELECT * FROM promotor_pacote WHERE id_promotor = ? ORDER BY ordem, id");
    $stmtPac->bind_param("i", $id_promotor);
    $stmtPac->execute();
    $pacotes = $stmtPac->get_result()->fetch_all(MYSQLI_ASSOC);
}

$galeria = [];
if($id_promotor){
    $stmtGal = $conexao->prepare("SELECT * FROM promotor_galeria WHERE id_promotor = ? ORDER BY id DESC");
    $stmtGal->bind_param("i", $id_promotor);
    $stmtGal->execute();
    $galeria = $stmtGal->get_result()->fetch_all(MYSQLI_ASSOC);
}

$localizacao = trim(($usuario["cidade"] ?? "") . (!empty($usuario["estado"]) ? ", " . $usuario["estado"] : ""));
if($localizacao === ""){ $localizacao = "Localização não informada"; }

$preco_minimo = $usuario["preco_minimo"] ?? null;
$preco_minimo_fmt = $preco_minimo !== null ? "R$ " . number_format((float)$preco_minimo, 2, ",", ".") : "A combinar";

$nota_media = number_format((float)($usuario["nota_media"] ?? 0), 1, ",", ".");
$total_avaliacoes = (int)($usuario["total_avaliacoes"] ?? 0);

$titulo_pagina = "Meu Perfil";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Nathus</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/layout.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/layout.css"); ?>">
        <link rel="stylesheet" href="CSS/perfil.css?v=<?php echo filemtime(__DIR__ . "/CSS/perfil.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/icones-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/icones-solicitacao.css') ?>">
</head>
<body>
    <div class="painel-container">
        <?php include_once("../Includes/sidebar.php"); ?>

        <main class="conteudo-principal">
            <?php include_once("../Includes/header.php"); ?>

            <div class="perfil-pagina">

                <?php
                    // Mensagens amigáveis para rejeições do servidor (validação backend).
                    $mensagens_erro = [
                        "pacote_titulo" => "Não foi possível salvar o pacote: informe um nome para ele.",
                        "preco_invalido" => "Informe um preço em reais, como 1.500,00.",
                        "preco_acima_limite" => "O preço do pacote ultrapassa o limite permitido.",
                        "dados_perfil"  => "Não foi possível salvar o perfil: verifique o nome e o telefone informados.",
                        "endereco_invalido" => "Confira o logradouro, número, bairro, cidade e UF do espaço.",
                        "area_invalida" => "Informe uma área maior que zero, com até duas casas decimais.",
                        "area_obrigatoria" => "Informe a área do espaço em m²: ela é obrigatória para locais de festas.",
                        "sobre_invalido" => "Não foi possível salvar Sobre mim. Abra o formulário e tente novamente.",
                        "descricao_invalida" => "Informe uma descrição de até " . LIMITE_DESCRICAO_PROMOTOR . " caracteres.",
                        "pacote_endereco" => "Cadastre o endereço e a área do espaço antes de criar um pacote.",
                    ];
                    $codigo_erro = $_GET["erro"] ?? "";
                ?>
                <?php if(isset($mensagens_erro[$codigo_erro])): ?>
                    <div class="aviso-erro">
                        <?php echo htmlspecialchars($mensagens_erro[$codigo_erro]); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET["apagado"])): ?>
                    <div class="aviso-sucesso">
                        <img src="<?php echo $base_url; ?>assets/check_dashboard.svg" alt="">
                        Foto removida da galeria com sucesso!
                    </div>
                <?php elseif(isset($_GET["excluido"])): ?>
                    <div class="aviso-sucesso">
                        <img src="<?php echo $base_url; ?>assets/check_dashboard.svg" alt="">
                        Pacote excluído com sucesso!
                    </div>
                <?php elseif(isset($_GET["sucesso"])): ?>
                    <div class="aviso-sucesso">
                        <img src="<?php echo $base_url; ?>assets/check_dashboard.svg" alt="">
                        Alterações salvas com sucesso!
                    </div>
                <?php endif; ?>

                <section class="card-topo" id="cardTopo">

                    <div class="topo-banner <?php echo $banner ? "" : "sem-foto"; ?>" <?php if($banner): ?>style="background-image:url('<?php echo htmlspecialchars($banner); ?>')"<?php endif; ?>></div>

                    <div class="topo-corpo">
                        <div class="topo-foto <?php echo $tem_foto_perfil ? "" : "foto-vazia"; ?>">
                            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil">
                        </div>

                        <div class="topo-linha">
                            <div class="topo-dados">
                                <h1><?php echo htmlspecialchars($titulo_perfil); ?></h1>
                                <?php if ($fantasia_perfil !== ""): ?>
                                    <p class="topo-fantasia"><?php echo htmlspecialchars($fantasia_perfil); ?></p>
                                <?php endif; ?>

                                <div class="topo-meta">
                                    <span class="meta-item">
                                        <img src="<?php echo $base_url; ?>assets/estrela_perfilprom.svg" alt="">
                                        <b><?php echo $nota_media; ?></b> (<?php echo $total_avaliacoes; ?> avaliações)
                                    </span>
                                    <span class="meta-item">
                                        <img src="<?php echo $base_url; ?>assets/local_perfilprom.svg" alt="">
                                        <?php echo htmlspecialchars($localizacao); ?>
                                    </span>
                                    <?php
                                    $telefone_perfil = $usuario['telefone'] ?? '';
                                    include __DIR__ . '/../Includes/telefone-perfil.php';
                                    ?>
                                </div>

                                <div class="topo-botoes">
                                    <button type="button" id="btnVisualizar" class="btn btn-visualizar-perfil"
                                        data-id-promotor="<?php echo (int) ($id_promotor ?? 0); ?>">
                                        <span class="icone-visualizar-perfil" aria-hidden="true"></span> Visualizar perfil
                                    </button>
                                    <button type="button" id="btnEditar" class="btn btn-editar-perfil">
                                        <span class="icone-editar-perfil" aria-hidden="true"></span> Editar perfil
                                    </button>
                                </div>
                            </div>

                            <div class="topo-preco">
                                <img src="<?php echo $base_url; ?>assets/perfil-bolsa.png" alt="">
                                <div>
                                    <span>Preço mínimo por evento</span>
                                    <strong><?php echo $preco_minimo_fmt; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card-sobre" id="sobre-mim">
                    <div class="titulo-secao">
                        <h2>Sobre mim</h2>
                        <button type="button" class="btn btn-editar-perfil btn-editar-sobre" aria-haspopup="dialog" aria-controls="modalEditarSobre" aria-label="Editar Sobre mim">
                            <span class="icone-editar-perfil" aria-hidden="true"></span> Editar
                        </button>
                    </div>

                    <?php if(!empty($usuario["frase_destaque"])): ?>
                        <h3><?php echo htmlspecialchars($usuario["frase_destaque"]); ?></h3>
                    <?php endif; ?>

                    <p><?php echo nl2br(htmlspecialchars($usuario["descricao"] ?? "Este promotor ainda não escreveu uma descrição.")); ?></p>

                    <div class="titulo-secao titulo-secao-especialidades"><h2>Especialidades</h2></div>

                    <div class="tags">
                        <?php if(!empty($especialidades)): ?>
                            <?php foreach($especialidades as $esp): ?>
                                <span class="tag">
                                    <?php if (!empty($esp['icone'])): ?>
                                        <span class="icone-especialidade" data-icone="<?= htmlspecialchars($esp['icone']) ?>" aria-hidden="true"></span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($esp['label']); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php elseif ($area_espaco === "" && (int) ($usuario["capacidade_maxima"] ?? 0) <= 0): ?>
                            <span class="tag tag-vazia">Nenhuma especialidade cadastrada</span>
                        <?php endif; ?>
                        <?php if ($area_espaco !== ""): ?>
                            <span class="tag">Área: <?= htmlspecialchars($area_espaco) ?> m²</span>
                        <?php endif; ?>
                        <?php if ((int) ($usuario["capacidade_maxima"] ?? 0) > 0): ?>
                            <span class="tag">Até <?php echo (int) $usuario["capacidade_maxima"]; ?> convidados</span>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="card-pacotes" id="pacotes-servicos">
                    <div class="titulo-secao"><h2>Pacotes de Serviços</h2></div>
                    <?php if($pacote_requer_endereco): ?>
                        <p class="aviso-erro">Cadastre o endereço e a área do espaço antes de criar um pacote.</p>
                    <?php endif; ?>

                    <div class="pacotes-grid">
                        <?php foreach($pacotes as $pacote): ?>
                            <?php
                                $imagem_pacote = midia_publica($pacote["imagem"] ?? "", ["pacotes"], $base_url);
                                $preco_pacote = is_numeric($pacote["preco"])
                                    ? "R$ " . number_format((float)$pacote["preco"], 2, ",", ".")
                                    : "Preço sob consulta";
                            ?>
                            <article class="pacote-cartao">
                                <h4><?php echo htmlspecialchars($pacote["titulo"]); ?></h4>

                                <div class="pacote-imagem <?php echo $imagem_pacote ? "" : "pacote-imagem-vazia"; ?>">
                                    <?php if($imagem_pacote): ?>
                                        <img src="<?php echo htmlspecialchars($imagem_pacote); ?>" alt="">
                                    <?php else: ?>
                                        <span class="pacote-imagem-vazio-texto">Sem imagem</span>
                                    <?php endif; ?>
                                </div>

                                <?php include __DIR__ . '/../Includes/itens-pacote.php'; ?>

                                <div class="pacote-preco">
                                    <img src="<?php echo $base_url; ?>assets/perfil-bolsa.png" alt="">
                                    <?php echo $preco_pacote; ?>
                                </div>

                                <?php
                                    $dados_pacote = json_encode([
                                        "id"      => (int)$pacote["id"],
                                        "titulo"  => $pacote["titulo"],
                                        "itens"   => $pacote["itens"],
                                        "preco"   => is_numeric($pacote["preco"])
                                            ? number_format((float) $pacote["preco"], 2, ",", ".")
                                            : ($pacote["preco"] ?? ""),
                                        "imagem"  => $imagem_pacote,
                                    ]);
                                ?>
                                <button type="button" class="btn-editar-pacote js-editar-pacote"
                                    data-pacote='<?php echo htmlspecialchars($dados_pacote, ENT_QUOTES, "UTF-8"); ?>'>
                                    <img src="<?php echo $base_url; ?>assets/editar_perfilprom.svg" alt=""> Editar Pacote
                                </button>
                            </article>
                        <?php endforeach; ?>

                        <button type="button" class="pacote-novo"<?php if($pacote_requer_endereco): ?> data-requer-endereco<?php endif; ?>>
                            <img src="<?php echo $base_url; ?>assets/adicionar_pacote_perfilprom.svg" alt="">
                            <span><?php echo $pacote_requer_endereco ? "Completar dados do espaço" : "Adicionar Novo Pacote"; ?></span>
                        </button>
                    </div>
                </section>

                <section class="card-galeria">
                    <div class="titulo-secao"><h2>Galeria</h2></div>

                    <div class="galeria-grid">
                        <?php foreach($galeria as $foto): ?>
                            <?php
                                // Pula registros órfãos (arquivo apagado da pasta uploads).
                                if(!is_file($pasta_uploads . "galeria/" . $foto["arquivo"])) continue;
                            ?>
                            <div class="galeria-item">
                                <img src="<?php echo $base_url; ?>uploads/galeria/<?php echo htmlspecialchars($foto['arquivo']); ?>" alt="">
                                <div class="galeria-acoes-foto">
                                    <button type="button" class="galeria-editar" title="Editar imagem" aria-label="Editar imagem" data-foto-id="<?php echo (int)$foto['id']; ?>" data-foto-src="<?php echo $base_url; ?>uploads/galeria/<?php echo htmlspecialchars($foto['arquivo'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-foto-base-src="<?php echo $base_url; ?>uploads/galeria/<?php echo htmlspecialchars($foto['arquivo'] . '.base.jpg', ENT_QUOTES, 'UTF-8'); ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                                    </button>
                                    <form action="galeria-apagar.php" method="post" class="galeria-apagar-formulario" onsubmit="return confirm('Tem certeza que deseja excluir esta foto da galeria?');">
                                        <input type="hidden" name="id_foto" value="<?php echo (int)$foto['id']; ?>">
                                        <button type="submit" class="galeria-apagar" title="Excluir imagem" aria-label="Excluir imagem">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <form action="galeria-salvar.php" method="post" enctype="multipart/form-data" class="form-galeria">
                            <label for="inputGaleria" class="galeria-item galeria-add">
                                <img src="<?php echo $base_url; ?>assets/adicionar_pacote_perfilprom.svg" alt="">
                                <span>Adicionar</span>
                            </label>
                            <input type="file" name="imagem" id="inputGaleria" accept="image/*">
                            <input type="file" name="imagem_original" id="inputGaleriaOriginal" accept="image/*" hidden>
                        </form>
                    </div>
                    <form action="galeria-salvar.php" method="post" enctype="multipart/form-data" id="formEditarGaleria" hidden>
                        <input type="hidden" name="id_foto" id="idFotoGaleria" value="">
                        <input type="file" name="imagem" id="arquivoEditarGaleria" accept="image/png,image/jpeg,image/webp">
                        <input type="file" name="imagem_original" id="arquivoEditarGaleriaOriginal" accept="image/*" hidden>
                    </form>
                </section>

            </div>
        </main>
    </div>

    <div class="overlay-modal" id="modalPacote">
        <form action="pacote-salvar.php" method="post" enctype="multipart/form-data" class="modal-pacote" id="formPacote" novalidate>
            <div class="modal-topo">
                <label class="campo-label" for="pacoteTitulo">Nome do pacote</label>
                <button type="button" aria-label="Fechar"><img src="<?php echo $base_url; ?>assets/fechar.svg" alt=""></button>
            </div>
            <input type="text" name="titulo" id="pacoteTitulo" class="modal-campo" placeholder="Ex: Pacote Festa Completa" maxlength="80" required>
            <span class="contador" data-para="pacoteTitulo" data-max="80">0/80</span>

            <input type="hidden" name="id_pacote" id="pacoteId" value="">

            <label for="pacoteImagemInput" class="pacote-imagem-upload">
                <img id="pacoteImagemPreview" alt="">
                <img src="<?php echo $base_url; ?>assets/editar_foto_perfil.svg?v=<?php echo filemtime(__DIR__ . "/../assets/editar_foto_perfil.svg"); ?>" id="pacoteImagemIcone" class="icone-upload" alt="">
            </label>
            <input type="file" name="imagem" id="pacoteImagemInput" accept="image/png, image/jpeg, image/webp">

            <label class="campo-label" for="pacoteItens">Descrição (um item por linha)</label>
            <textarea name="itens" id="pacoteItens" rows="5" placeholder="Adicione uma descrição ao seu pacote de serviço&#10;(um item por linha)" maxlength="1000"></textarea>
            <span class="contador" data-para="pacoteItens" data-max="1000">0/1000</span>

            <div class="erro-modal" id="erroPacote" hidden></div>

            <div class="pacote-preco-campo">
                <img src="<?php echo $base_url; ?>assets/preco_perfilprom.svg" alt="">
                <span aria-hidden="true">R$</span>
                <input type="text" name="preco" id="pacotePreco" aria-label="Preço do pacote em reais"
                    placeholder="Adicione seu preço" inputmode="decimal" data-mascara="moeda"
                    data-max-inteiros="<?= MOEDA_BR_MAX_DIGITOS_INTEIROS ?>"
                    aria-describedby="erroPacotePreco">
            </div>
            <p class="erro-modal" id="erroPacotePreco" role="alert" hidden></p>

            <div class="modal-acoes">
                <button type="submit" id="pacoteExcluirBtn" class="pacote-apagar" formaction="pacote-apagar.php" formnovalidate hidden>
                    Excluir
                </button>
                <button type="submit" id="pacoteSalvarBtn" class="btn-salvar-pacote">
                    <img src="<?php echo $base_url; ?>assets/perfil-salvar.svg?v=<?php echo filemtime(__DIR__ . "/../assets/perfil-salvar.svg"); ?>" alt=""> <span>Salvar Pacote</span>
                </button>
            </div>
        </form>
    </div>

    <div class="overlay-modal" id="modalSobre">
      <div class="modal-sobre-caixa">
        <form action="salvar-editar-perfil.php" method="post" enctype="multipart/form-data" class="modal-sobre" id="formSobre" novalidate>
            <div class="modal-topo">
                <h3>Editar perfil</h3>
                <button type="button" ><img src="<?php echo $base_url; ?>assets/fechar.svg" alt=""></button>
            </div>

            <div class="editar-fotos">
                <div class="editar-banner <?php echo $banner ? "" : "foto-vazia"; ?>">
                    <span class="editar-banner-fundo" id="previewBannerModal" <?php if($banner): ?>data-src="<?php echo htmlspecialchars($banner, ENT_QUOTES, "UTF-8"); ?>" style="background-image:url('<?php echo htmlspecialchars($banner, ENT_QUOTES, "UTF-8"); ?>')"<?php endif; ?>></span>
                    <div class="editar-midia-acoes">
                        <button type="button" class="midia-acao" id="btnTrocarBanner" title="Trocar banner" aria-label="Trocar banner">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h3l2-2h6l2 2h3v11H4z"/><circle cx="12" cy="13" r="3.2"/></svg>
                        </button>
                        <button type="button" class="midia-acao midia-acao-enquadrar" id="btnEnquadrarBanner" title="Ajustar enquadramento" aria-label="Ajustar enquadramento">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4M4 20v-4M20 4h-4M20 4v4M14 7l-7 7 1.5 1.5L15.5 8.5z"/></svg>
                        </button>
                        <button type="button" class="midia-acao midia-acao-remover" id="btnRemoverBanner" title="Remover banner" aria-label="Remover banner">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                        </button>
                    </div>
                </div>
                <label class="editar-avatar <?php echo $tem_foto_perfil ? "" : "foto-vazia"; ?>" for="inputFotoModal" title="Trocar foto de perfil">
                    <img id="previewAvatarModal" src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="">
                    <span class="editar-foto-botao"><img src="<?php echo $base_url; ?>assets/camera-avatar.svg" alt=""></span>
                </label>
            </div>
            <input type="file" name="banner" id="inputBannerModal" accept="image/png, image/jpeg, image/webp">
            <input type="hidden" name="remover_banner" id="removerBanner" value="">
            <input type="file" name="foto_perfil" id="inputFotoModal" accept="image/png, image/jpeg, image/webp">

            <div>
                <label class="campo-label" for="campoNome">Nome</label>
                <input type="text" name="nome" id="campoNome" maxlength="45" required
                    value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>">
                <span class="contador" data-para="campoNome" data-max="45">0/45</span>
            </div>

            <div>
                <label class="campo-label" for="campoFantasia">Nome fantasia</label>
                <input type="text" name="nome_fantasia" id="campoFantasia" maxlength="80"
                    placeholder="Ex: Eventos Natal (opcional)"
                    value="<?php echo htmlspecialchars($usuario['nome_fantasia'] ?? ''); ?>">
                <span class="contador" data-para="campoFantasia" data-max="80">0/80</span>
            </div>

            <div>
                <label class="campo-label" for="campoTelefone">Celular</label>
                <input type="tel" name="telefone" id="campoTelefone" data-mascara="telefone" maxlength="19" autocomplete="tel"
                    placeholder="Ex: (11) 91234-5678"
                    value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>">
            </div>

            <?php
                $cidades_disponiveis = cidades_cadastro();
                $ufs_disponiveis = array_unique(array_column($cidades_disponiveis, "uf"));
                sort($ufs_disponiveis);
            ?>
            <div class="campo-localizacao">
                <label class="campo-label" for="campoCidade">Cidade</label>
                <input type="text" name="cidade" id="campoCidade" maxlength="60" autocomplete="off" aria-controls="listaCidades"
                    placeholder="Busque a cidade"
                    value="<?php echo htmlspecialchars($usuario['cidade'] ?? ''); ?>">
                <ul class="lista-localizacao" id="listaCidades" hidden>
                    <?php foreach ($cidades_disponiveis as $item): ?>
                        <li>
                            <button type="button" data-valor="<?php echo htmlspecialchars($item['cidade']); ?>" data-uf="<?php echo htmlspecialchars($item['uf']); ?>">
                                <?php echo htmlspecialchars($item['cidade']); ?>
                                <small><?php echo htmlspecialchars($item['uf']); ?></small>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="campo-localizacao">
                <label class="campo-label" for="campoEstado">Estado (UF)</label>
                <input type="text" name="estado" id="campoEstado" maxlength="2" autocomplete="off" aria-controls="listaEstados"
                    placeholder="Ex: RJ" style="text-transform:uppercase"
                    value="<?php echo htmlspecialchars($usuario['estado'] ?? ''); ?>">
                <ul class="lista-localizacao" id="listaEstados" hidden>
                    <?php foreach ($ufs_disponiveis as $uf): ?>
                        <li><button type="button" data-valor="<?php echo htmlspecialchars($uf); ?>"><?php echo htmlspecialchars($uf); ?></button></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="erro-modal" id="erroSobre" hidden></div>

            <?php if ($campo_unico === "local"): ?>
                <div>
                    <label class="campo-label label--convidados" for="campoCapacidade">Capacidade máxima de convidados</label>
                    <input type="number" name="capacidade_maxima" id="campoCapacidade" min="1" max="100000"
                        placeholder="Ex: 150"
                        value="<?php echo (int) ($usuario['capacidade_maxima'] ?? 0) > 0 ? (int) $usuario['capacidade_maxima'] : ''; ?>">
                </div>
            <?php endif; ?>
            <div>
                <label class="campo-label">Campo de atuação</label>
                <div class="campo-especialidades">
                    <?php foreach($rotulos_atuacao as $campo => $label): ?>
                        <label class="checkbox-especialidade">
                            <input type="radio" name="campos[]" value="<?php echo $campo; ?>"
                                <?php echo $campo === $campo_unico ? "checked" : ""; ?>>
                            <span class="icone-atuacao <?php echo $campo; ?>"></span>
                            <?php echo htmlspecialchars($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <fieldset id="enderecoEspaco" class="endereco-espaco"<?php if(!$eh_espaco): ?> hidden disabled<?php endif; ?>>
                <legend>Endereço do espaço</legend>
                <p>Preencha logradouro, número, bairro, cidade e UF. O cliente verá este endereço ao solicitar um serviço.</p>
                <div class="erro-modal" id="erroEnderecoEspaco" role="alert" hidden></div>
                <?php foreach(CAMPOS_ENDERECO_ESPACO as $campo => [$rotulo, $limite]): ?>
                    <div>
                        <label class="campo-label" for="<?php echo $campo; ?>"><?php echo $rotulo; ?></label>
                        <input type="text" name="<?php echo $campo; ?>" id="<?php echo $campo; ?>"
                            maxlength="<?php echo $limite; ?>" value="<?php echo htmlspecialchars($usuario[$campo] ?? ''); ?>"
                            <?php if($campo === "espaco_numero"): ?>placeholder="Ex: 120 ou s/n"<?php endif; ?>>
                    </div>
                <?php endforeach; ?>
                <div>
                    <label class="campo-label" for="espaco_area_m2">Área do espaço (m², obrigatório)</label>
                    <input type="text" inputmode="decimal" name="espaco_area_m2" id="espaco_area_m2"
                        data-mascara="area" maxlength="13" required aria-required="true"
                        pattern="([0-9]{1,8}|[0-9]{1,2}\.[0-9]{3}\.[0-9]{3}|[0-9]{1,3}\.[0-9]{3})(,[0-9]{1,2})?"
                        placeholder="Ex.: 1.200,50"
                        value="<?= htmlspecialchars(area_espaco_formatada($usuario['espaco_area_m2'] ?? null)) ?>">
                </div>
            </fieldset>

            <button type="submit" id="sobreSalvarBtn" class="btn-salvar-sobre">
                <img src="<?php echo $base_url; ?>assets/perfil-salvar.svg?v=<?php echo filemtime(__DIR__ . "/../assets/perfil-salvar.svg"); ?>" alt=""> <span>Salvar</span>
            </button>
        </form>
      </div>
    </div>

    <dialog id="modalEditarSobre" class="modal-sobre-caixa modal-editar-sobre" aria-labelledby="tituloEditarSobre">
        <form class="modal-sobre" action="sobre-salvar.php" method="post">
            <div class="modal-topo">
                <h3 id="tituloEditarSobre">Editar Sobre mim</h3>
                <button type="button" id="fecharEditarSobre" aria-label="Fechar">
                    <img src="<?= $base_url ?>assets/fechar.svg" alt="">
                </button>
            </div>
            <input type="hidden" name="csrf_sobre" value="<?= htmlspecialchars($_SESSION['csrf_sobre']) ?>">
            <div>
                <label class="campo-label" for="campoFrase">Frase de destaque</label>
                <input type="text" name="frase_destaque" id="campoFrase" maxlength="150"
                    placeholder="Ex: Transformando Sonhos em Eventos Inesquecíveis!"
                    value="<?php echo htmlspecialchars($usuario['frase_destaque'] ?? ''); ?>">
                <span class="contador" data-para="campoFrase" data-max="150">0/150</span>
            </div>

            <div>
                <label class="campo-label" for="campoDescricao">Descrição</label>
                <textarea name="descricao" id="campoDescricao" rows="5" maxlength="<?= LIMITE_DESCRICAO_PROMOTOR ?>"
                    placeholder="Conte um pouco sobre você e o seu trabalho"><?php echo htmlspecialchars($usuario['descricao'] ?? ''); ?></textarea>
                <span class="contador" data-para="campoDescricao" data-max="<?= LIMITE_DESCRICAO_PROMOTOR ?>">0/<?= LIMITE_DESCRICAO_PROMOTOR ?></span>
            </div>
            <fieldset>
                <legend class="campo-label">Especialidades</legend>
                <?php if ($campo_unico === "buffet"): ?>
                    <div class="campo-especialidades">
                        <?php foreach($rotulos_buffet as $tipo => $rotulo): ?>
                            <label class="checkbox-especialidade">
                                <input type="checkbox" name="especialidades[]" value="<?php echo $tipo; ?>"
                                    <?php echo in_array($tipo, $especialidades_ativas, true) ? "checked" : ""; ?>>
                                <?php echo htmlspecialchars($rotulo); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="campo-especialidades">
                        <?php foreach(TIPOS_FESTA as $tipo => $info): ?>
                            <label class="checkbox-especialidade">
                                <input type="checkbox" name="especialidades[]" value="<?php echo $tipo; ?>"
                                    <?php echo in_array($tipo, $especialidades_ativas, true) ? "checked" : ""; ?>>
                                <?php if ($info["icone"] !== ""): ?>
                                    <span class="icone-especialidade" data-icone="<?= htmlspecialchars($info['icone']) ?>" aria-hidden="true"></span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($info['label']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </fieldset>
            <button type="submit" class="btn-salvar-sobre">
                <img src="<?= $base_url ?>assets/perfil-salvar.svg" alt=""> <span>Salvar</span>
            </button>
        </form>
    </dialog>

    <?php require_once __DIR__ . '/../Includes/mascaras.php'; ?>
    <script src="js/perfil.js?v=<?php echo filemtime(__DIR__ . "/js/perfil.js"); ?>"></script>
</body>
</html>
