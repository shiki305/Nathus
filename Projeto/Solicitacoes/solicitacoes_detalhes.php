<?php
require_once __DIR__ . "/../Includes/agenda-solicitacoes.php";
require_once __DIR__ . "/../Includes/perfil_foto.php";

session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");

// Página exclusiva de PROMOTOR: cliente é levado ao "Explorar".
$id_promotor_logado = exigirPromotor($conexao);

// Migração automática das colunas novas (id_promotor, status).
garantir_colunas_solicitacoes($conexao);

// 2. Pega o ID da URL e verifica se é válido
$id_solicitacao = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Se não tiver ID na URL, redireciona para a lista principal
if ($id_solicitacao === 0) {
    header("Location: solicitacoes.php");
    exit;
}

// 3. Ações de Aceitar, Recusar ou Reverter (Botões)
// Só a conta do promotor destinatário altera a solicitação do cliente.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $stmt_dono = $conexao->prepare("SELECT id_promotor, data_festa, status FROM solicitacoes WHERE id = ? LIMIT 1");
    $stmt_dono->bind_param("i", $id_solicitacao);
    $stmt_dono->execute();
    $linha_dono = $stmt_dono->get_result()->fetch_assoc();
    if (!$linha_dono || (int) $linha_dono["id_promotor"] !== (int) $id_promotor_logado) {
        header("Location: solicitacoes.php");
        exit;
    }

    $novo_status = '';
    
    if ($_POST['acao'] === 'aceitar') {
        if (!in_array($linha_dono["status"], [null, '', 'pendente'], true)) {
            header("Location: solicitacoes_detalhes.php?id=" . $id_solicitacao, true, 303);
            exit;
        }
        $novo_status = 'aceito';
    } elseif ($_POST['acao'] === 'recusar') {
        $novo_status = 'recusado';
    } elseif ($_POST['acao'] === 'reverter') {
        $novo_status = 'pendente';
    }
    
    if ($novo_status !== '') {
        if ($novo_status === 'pendente') {
            $stmt_update = $conexao->prepare("UPDATE solicitacoes SET status = ?, respondida_em = NULL, resposta_pendente = NULL
                WHERE id = ? AND id_promotor = ? AND status IN ('aceito', 'aceita', 'recusado', 'recusada')");
            $stmt_update->bind_param("sii", $novo_status, $id_solicitacao, $id_promotor_logado);
        } else {
            $stmt_update = $conexao->prepare("UPDATE solicitacoes SET status = ?, respondida_em = NOW(), resposta_pendente = ?
                WHERE id = ? AND id_promotor = ? AND (status IS NULL OR status IN ('', 'pendente'))");
            $stmt_update->bind_param("ssii", $novo_status, $novo_status, $id_solicitacao, $id_promotor_logado);
        }
        if ($novo_status === 'aceito') {
            $conexao->begin_transaction();
            try {
                bloquear_agenda_promotor($conexao, $id_promotor_logado);
                $periodo = intervalo_dias_evento((string) $linha_dono["data_festa"]);
                if ($periodo === null || periodo_tem_reserva(dias_reservados_promotor($conexao, $id_promotor_logado, $id_solicitacao), ...$periodo)) {
                    $conexao->rollback();
                    $_SESSION["solicitacao_erro_agenda"] = [
                        "id" => $id_solicitacao,
                        "mensagem" => $periodo === null
                            ? "A data do evento é inválida. Confirme o período antes de aceitar. A solicitação continua pendente."
                            : "Já existe uma reserva neste período. A solicitação continua pendente.",
                    ];
                    header("Location: solicitacoes_detalhes.php?id=" . $id_solicitacao, true, 303);
                    exit;
                }
                $stmt_update->execute();
                $conexao->commit();
            } catch (mysqli_sql_exception $erro) {
                $conexao->rollback();
                throw $erro;
            }
        } else {
            $stmt_update->execute();
        }
        if ($stmt_update->affected_rows === 0) {
            header("Location: solicitacoes_detalhes.php?id=" . $id_solicitacao);
            exit;
        }

        // Ao aceitar, o serviço já existe com o mesmo id: leva direto para ele.
        $destino = $novo_status === 'aceito'
            ? "../Servicos/servicos.php?id=" . $id_solicitacao
            : "solicitacoes_detalhes.php?id=" . $id_solicitacao;
        header("Location: " . $destino, true, 303);
        exit;
    }
}

require_once __DIR__ . "/../Includes/buscar-detalhe-solicitacao.php";
$dados = buscar_detalhe_solicitacao($conexao, $id_solicitacao, $id_promotor_logado);
if (!$dados) {
    header("Location: solicitacoes.php");
    exit;
}

$base_url = "../";
$detalhe_servico = false;
$aviso_agenda = "";
$erro_agenda = $_SESSION["solicitacao_erro_agenda"] ?? [];
if (($erro_agenda["id"] ?? null) === $id_solicitacao) {
    $aviso_agenda = $erro_agenda["mensagem"];
    unset($_SESSION["solicitacao_erro_agenda"]);
}
$pagina_atual = "solicitacoes";
$titulo_pagina = "Detalhes da solicitação";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação #<?= $id_solicitacao ?> | Nathus</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?= filemtime(__DIR__ . '/../Includes/css/global.css') ?>">
    <link rel="stylesheet" href="../Includes/css/layout.css?v=<?= filemtime(__DIR__ . '/../Includes/css/layout.css') ?>">
    <link rel="stylesheet" href="../Includes/css/icones-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/icones-solicitacao.css') ?>">
    <link rel="stylesheet" href="../Includes/css/detalhe-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/detalhe-solicitacao.css') ?>">
    <link rel="stylesheet" href="../Includes/css/botao-voltar.css?v=<?= filemtime(__DIR__ . '/../Includes/css/botao-voltar.css') ?>">
</head>
<body>
<div class="painel-container">
    <?php include __DIR__ . '/../Includes/sidebar.php'; ?>
    <main class="conteudo-principal">
        <?php include __DIR__ . '/../Includes/header.php'; ?>
        <?php include __DIR__ . "/../Includes/detalhe-solicitacao.php"; ?>
    </main>
</div>
</body>
</html>
