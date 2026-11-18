<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");
include_once("../Includes/perfil_foto.php");

// Página exclusiva de PROMOTOR: cliente é levado ao "Explorar"
// (antes o acesso de cliente CRIAVA um perfil de promotor vazio).
$id_promotor = exigirPromotor($conexao);

$base_url = "../";
$pagina_atual = "assinatura";
$titulo_pagina = "Minha Assinatura";

$planos = [
    "mensal" => [
        "nome" => "Mensal",
        "descricao" => "Perfeito para quem quer testar sem compromisso",
        "preco" => "30",
        "periodo" => "/mês",
        "valor_texto" => "R$ 30,00",
        "itens" => [
            "Acesso ilimitado à plataforma",
            "Personalização básica do perfil",
            "Cancele quando quiser",
        ],
    ],
    "anual" => [
        "nome" => "Anual",
        "descricao" => "Pague menos e aproveite por um ano inteiro",
        "preco" => "315",
        "periodo" => "/ano",
        "valor_texto" => "R$ 315,00",
        "itens" => [
            "Atendimento prioritário",
            "Economize R$ 45,00 em relação a 12 mensalidades",
            "Tudo do plano mensal",
        ],
    ],
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $novo_plano = strtolower(trim((string) ($_POST["plano"] ?? "")));
    if (isset($planos[$novo_plano])) {
        $upd = $conexao->prepare("UPDATE promotor SET plano = ? WHERE id = ?");
        $upd->bind_param("si", $novo_plano, $id_promotor);
        $upd->execute();
        header("Location: assinatura.php?atualizado=1");
        exit;
    }
}

$stmt = $conexao->prepare(
    "SELECT c.nome, p.plano, p.status_plano, p.metodo_pagamento, p.nota_media, p.nome_fantasia
     FROM promotor p
     INNER JOIN cliente c ON c.id = p.id_cliente
     WHERE p.id = ?
     LIMIT 1"
);
$stmt->bind_param("i", $id_promotor);
$stmt->execute();
$dados = $stmt->get_result()->fetch_assoc() ?: [];

$plano_atual = strtolower(trim((string) ($dados["plano"] ?? "")));
if (!isset($planos[$plano_atual])) {
    $plano_atual = "";
}

$status_plano = strtolower(trim((string) ($dados["status_plano"] ?? "")));

$metodo = strtolower(trim((string) ($dados["metodo_pagamento"] ?? "")));
$metodo_texto = $metodo === "cartao" ? "Cartão" : ($metodo === "pix" ? "PIX" : "Não informado");

$rotulos_atuacao = [
    "organizacao" => "Organizador",
    "buffet" => "Buffet",
    "local" => "Local",
];
$atuacao = "Promotor";
$stmtCampo = $conexao->prepare("SELECT campo FROM promotor_campo WHERE id_promotor = ? LIMIT 1");
$stmtCampo->bind_param("i", $id_promotor);
$stmtCampo->execute();
$campo = $stmtCampo->get_result()->fetch_assoc();
if ($campo && isset($rotulos_atuacao[$campo["campo"]])) {
    $atuacao = $rotulos_atuacao[$campo["campo"]];
}

[$foto_perfil, $tem_foto] = resolver_foto_perfil($conexao);
$nome = trim((string) ($dados["nome_fantasia"] ?? "")) !== ""
    ? $dados["nome_fantasia"]
    : ($dados["nome"] ?: ($_SESSION["usuario_nome"] ?? "Promotor"));
$nota = number_format((float) ($dados["nota_media"] ?? 0), 1, ",", "");
$plano_nome = $plano_atual !== "" ? $planos[$plano_atual]["nome"] : "Nenhum";
$valor_atual = $plano_atual !== "" ? $planos[$plano_atual]["valor_texto"] : "—";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Assinatura - Nathus</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/layout.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/layout.css"); ?>">
    <link rel="stylesheet" href="CSS/assinatura.css?v=<?php echo filemtime(__DIR__ . "/CSS/assinatura.css"); ?>">
</head>
<body>
    <div class="painel-container">
        <?php include_once("../Includes/sidebar.php"); ?>

        <main class="conteudo-principal">
            <?php include_once("../Includes/header.php"); ?>

            <div class="assinatura-pagina">
                <?php if (isset($_GET["atualizado"])): ?>
                    <p class="aviso-ok" role="status">Seu plano foi atualizado com sucesso.</p>
                <?php endif; ?>

                <section class="resumo-assinatura" aria-labelledby="titulo-resumo-assinatura">
                    <h2 id="titulo-resumo-assinatura">Resumo da assinatura</h2>
                    <div class="resumo-conteudo">
                        <div class="resumo-pessoa">
                            <div class="resumo-foto <?php echo $tem_foto ? "" : "vazia"; ?>">
                                <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="">
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($nome); ?></strong>
                                <span><?php echo htmlspecialchars($atuacao); ?></span>
                            </div>
                        </div>

                        <div class="resumo-dados">
                            <div class="dado">
                                <small>Plano atual</small>
                                <strong><?php echo htmlspecialchars($plano_nome); ?></strong>
                            </div>
                            <div class="dado">
                                <small>Status</small>
                                <strong class="status-<?php echo $status_plano === "ativo" ? "ativo" : "inativo"; ?>">
                                    <?php echo $status_plano === "ativo" ? "Ativo" : "Inativo"; ?>
                                </strong>
                            </div>
                            <div class="dado">
                                <small>Valor</small>
                                <strong><?php echo htmlspecialchars($valor_atual); ?></strong>
                            </div>
                            <div class="dado">
                                <small>Forma cadastrada</small>
                                <strong><?php echo htmlspecialchars($metodo_texto); ?></strong>
                            </div>
                            <?php if ((float) ($dados["nota_media"] ?? 0) > 0): ?>
                                <div class="dado">
                                    <small>Avaliação</small>
                                    <strong><?php echo $nota; ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <section class="dados-plano">
                    <h2>Dados do plano</h2>
                    <p>Esta tela registra a escolha do plano. As alterações feitas aqui não geram cobrança.</p>
                </section>

                <section class="grade-planos">
                    <?php foreach ($planos as $chave => $plano): ?>
                        <?php $atual = $chave === $plano_atual; ?>
                        <article class="plano-cartao <?php echo $atual ? "atual" : ""; ?>">
                            <header class="plano-cabecalho">
                                <h2><?php echo htmlspecialchars($plano["nome"]); ?></h2>
                                <?php if ($atual): ?>
                                    <span class="selo-atual">Plano atual</span>
                                <?php endif; ?>
                            </header>
                            <p class="plano-desc"><?php echo htmlspecialchars($plano["descricao"]); ?></p>
                            <p class="plano-preco">
                                <span>R$</span>
                                <strong><?php echo htmlspecialchars($plano["preco"]); ?></strong>
                                <small><?php echo htmlspecialchars($plano["periodo"]); ?></small>
                            </p>
                            <ul>
                                <?php foreach ($plano["itens"] as $item): ?>
                                    <li>
                                        <img src="<?php echo $base_url; ?>assets/cadastro-funcionalidade.svg" alt="">
                                        <?php echo htmlspecialchars($item); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (!$atual): ?>
                                <form method="POST">
                                    <input type="hidden" name="plano" value="<?php echo htmlspecialchars($chave); ?>">
                                    <button type="submit" class="botao-plano" onclick="return confirm('Deseja realmente mudar para o plano <?php echo htmlspecialchars($plano["nome"]); ?>?');">
                                        Mudar para este plano
                                    </button>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </section>

            </div>
        </main>
    </div>
</body>
</html>
