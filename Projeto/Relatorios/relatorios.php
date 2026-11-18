<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");

include_once(__DIR__ . "/dados.php");

$id_promotor = exigirPromotor($conexao);
garantir_colunas_solicitacoes($conexao);

$base_url = "../";
$pagina_atual = "relatorios";
$titulo_pagina = "Relatórios";

$relatorio = relatorio_dados($conexao, $id_promotor, $_GET["ano"] ?? date("Y"));
$anos = $relatorio["anos"];
$ano = $relatorio["ano"];
$linhas = $relatorio["linhas"];
$contagem = $relatorio["contagem"];
$por_mes = $relatorio["por_mes"];
$convidados = $relatorio["convidados"];
$total = $relatorio["total"];
$aceitas = $relatorio["aceitas"];
$taxa = $relatorio["taxa"];
$pico = $relatorio["pico"];
$nomes_meses = $relatorio["nomes_meses"];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Nathus</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/layout.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/layout.css"); ?>">
    <link rel="stylesheet" href="CSS/relatorios.css?v=<?php echo filemtime(__DIR__ . "/CSS/relatorios.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/icones-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/icones-solicitacao.css') ?>">
</head>
<body>
    <div class="painel-container">
        <?php include_once("../Includes/sidebar.php"); ?>

        <main class="conteudo-principal">
            <?php include_once("../Includes/header.php"); ?>

            <div class="rel-pagina">
                <form class="rel-ano" method="get" action="relatorios.php">
                    <label for="ano">Ano</label>
                    <select id="ano" name="ano" onchange="this.form.submit()">
                        <?php foreach ($anos as $opcao): ?>
                            <option value="<?php echo $opcao; ?>" <?php echo $opcao === $ano ? "selected" : ""; ?>>
                                <?php echo $opcao; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <section class="rel-cards">
                    <article class="rel-card">
                        <span>Recebidas</span>
                        <strong><?php echo $total; ?></strong>
                    </article>
                    <article class="rel-card">
                        <span>Aceitas</span>
                        <strong><?php echo $aceitas; ?></strong>
                    </article>
                    <article class="rel-card">
                        <span>Recusadas</span>
                        <strong><?php echo $contagem["Recusada"]; ?></strong>
                    </article>
                    <article class="rel-card">
                        <span>Canceladas</span>
                        <strong><?php echo $contagem["Cancelada"]; ?></strong>
                    </article>
                    <article class="rel-card">
                        <span>Taxa de aceite</span>
                        <strong><?php echo $taxa === null ? "—" : $taxa . "%"; ?></strong>
                    </article>
                </section>

                <section class="rel-painel">
                    <div class="rel-cabecalho">
                        <h2>Solicitações em <?php echo $ano; ?></h2>
                        <p><?php echo $convidados > 0 ? $convidados . " convidados nos eventos aceitos" : "Nenhum evento aceito neste ano"; ?></p>
                    </div>
                    <?php if ($total === 0): ?>
                        <p class="rel-vazio">Nenhuma solicitação recebida em <?php echo $ano; ?>.</p>
                    <?php else: ?>
                        <div class="rel-grafico" aria-hidden="true">
                            <?php foreach ($nomes_meses as $indice => $nome): ?>
                                <?php
                                    $quantidade = $por_mes[$indice + 1];
                                    $altura = ($quantidade === 0 || $pico === 0) ? 0 : max(8, (int) round(($quantidade / $pico) * 100));
                                ?>
                                <div class="rel-coluna" title="<?php echo $nome . ": " . $quantidade; ?>">
                                    <div class="rel-barra" style="height: <?php echo $altura; ?>%"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="rel-meses">
                            <?php foreach ($nomes_meses as $nome): ?>
                                <span><?php echo $nome; ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="rel-painel">
                    <div class="rel-cabecalho">
                        <h2>Detalhes</h2>
                        <p><?php echo $contagem["Pendente"]; ?> pendentes · <?php echo $contagem["Concluída"]; ?> concluídas</p>
                    </div>
                    <?php if ($total === 0): ?>
                        <p class="rel-vazio">Quando chegarem solicitações, elas aparecem aqui.</p>
                    <?php else: ?>
                        <div class="rel-tabela-wrap">
                            <table class="rel-tabela">
                                <thead>
                                    <tr>
                                        <th scope="col"><span class="rel-rotulo rel-rotulo--evento">Evento</span></th>
                                        <th scope="col"><span class="rel-rotulo label--convidados">Cliente</span></th>
                                        <th scope="col"><span class="rel-rotulo label--data">Data da festa</span></th>
                                        <th scope="col"><span class="rel-rotulo label--convidados">Convidados</span></th>
                                        <th scope="col"><span class="rel-rotulo rel-rotulo--status">Status</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($linhas as $linha): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($linha["nome_evento"] ?: "Sem nome"); ?></td>
                                            <td><?php echo htmlspecialchars($linha["nome_cliente"] ?: "—"); ?></td>
                                            <td><?php echo htmlspecialchars(relatorio_mostrar_data($linha["data_festa"] ?? "")); ?></td>
                                            <td><?php echo (int) ($linha["numero_convidados"] ?? 0) > 0 ? (int) $linha["numero_convidados"] : "—"; ?></td>
                                            <td><span class="rel-status rel-status--<?php echo relatorio_classe_status($linha["status"] ?? ""); ?>"><?php echo relatorio_rotulo_status($linha["status"] ?? ""); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <a class="rel-pdf" href="pdf.php?ano=<?php echo (int) $ano; ?>">Baixar PDF</a>
            </div>
        </main>
    </div>
</body>
</html>
