<?php
require_once __DIR__ . "/../Includes/solicitacoes-pendentes.php";

session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");

// Página exclusiva de PROMOTOR: cliente é levado ao "Explorar".
$id_promotor_logado = exigirPromotor($conexao);

$base_url = "../";
$pagina_atual = "solicitacoes";
$titulo_pagina = "Solicitações";

// Migração automática das colunas novas (id_promotor, status) — ver Includes/conexao.php.
garantir_colunas_solicitacoes($conexao);

$solicitacoes = solicitacoes_pendentes($conexao, $id_promotor_logado);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações - Nathus</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/layout.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/layout.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/icones-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/icones-solicitacao.css') ?>">
    <link rel="stylesheet" href="../Includes/css/card-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/card-solicitacao.css') ?>">
</head>
<body>
    <div class="painel-container">
        <?php include_once("../Includes/sidebar.php"); ?>

        <main class="conteudo-principal">
            <?php include_once("../Includes/header.php"); ?>

            <div class="solicitacoes-pagina">

                <?php if (isset($_GET["status_atualizado"])): ?>
                    <?php if ($_GET["status_atualizado"] === "pendente"): ?>
                        <div style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                            Status da solicitação revertido para <strong>pendente</strong>.
                        </div>
                    <?php elseif ($_GET["status_atualizado"] === "aceito"): ?>
                        <div style="background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                            Solicitação <strong>aceita</strong> com sucesso!
                        </div>
                    <?php elseif ($_GET["status_atualizado"] === "recusado"): ?>
                        <div style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                            Solicitação <strong>recusada</strong>.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <section aria-labelledby="solicitacoes-pendentes">
                    <header class="solicitacoes-cabecalho">
                        <h2 id="solicitacoes-pendentes">Pendentes</h2>
                        <span class="solicitacoes-contagem"><?= count($solicitacoes) ?></span>
                    </header>

                    <div class="lista-cards-solicitacao">
                        <?php if (!$solicitacoes): ?>
                            <p class="solicitacoes-vazio">Nenhuma solicitação pendente no momento.</p>
                        <?php else: ?>
                            <?php foreach ($solicitacoes as $solicitacao): ?>
                                <?php include __DIR__ . '/../Includes/card-solicitacao.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

            </div>
        </main>
    </div>
</body>
</html>
