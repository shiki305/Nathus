<?php
require_once __DIR__ . "/../Includes/periodo-evento.php";
require_once __DIR__ . "/../Includes/agenda-solicitacoes.php";
require_once __DIR__ . "/../Includes/perfil_foto.php";

session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");

// Página exclusiva de PROMOTOR: cliente é levado ao "Explorar".
$id_promotor_logado = exigirPromotor($conexao);

// Migração automática das colunas novas (id_promotor, status).
garantir_colunas_solicitacoes($conexao);

$base_url = "../";
$pagina_atual = "servicos";
$titulo_pagina = "Meus Serviços";

function timestamp_festa($valor) {
    $valor = trim((string) $valor);
    if ($valor === "") {
        return 0;
    }
    if (preg_match("/^(\d{2})\/(\d{2})\/(\d{2,4})$/", $valor, $m)) {
        $ano = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];
        return mktime(0, 0, 0, (int) $m[2], (int) $m[1], $ano);
    }
    if (preg_match("/^(\d{4})-(\d{2})-(\d{2})/", $valor, $m)) {
        return mktime(0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1]);
    }
    $ts = strtotime(str_replace("/", "-", $valor));
    return $ts ?: 0;
}

function fim_servico(string $data, string $horario): ?DateTimeImmutable {
    $data = trim($data);
    $horario = str_replace("–", "-", trim($horario));
    if (data_evento_iso($data) !== null) {
        $data = "$data a $data";
    }
    return fim_periodo_evento($data, $horario);
}

function festa_ja_aconteceu($valor, $horario) {
    $fim = fim_servico((string) $valor, (string) $horario);
    if ($fim !== null) return new DateTimeImmutable() >= $fim;
    if (datas_periodo_evento((string) $valor)) return false;
    $ts = timestamp_festa($valor);
    if ($ts <= 0) {
        return false;
    }
    return strtotime(date("Y-m-d")) >= strtotime(date("Y-m-d", $ts));
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["concluir_servico"])) {
    $id_concluir = (int) ($_POST["id"] ?? 0);
    if ($id_concluir > 0) {
        $busca = $conexao->prepare("SELECT data_festa, horario, status, id_promotor FROM solicitacoes WHERE id = ? LIMIT 1");
        $busca->bind_param("i", $id_concluir);
        $busca->execute();
        $servico_concluir = $busca->get_result()->fetch_assoc();
        $status_ok = $servico_concluir && in_array(strtolower(trim((string) ($servico_concluir["status"] ?? ""))), ["aceito", "aceita"], true);

        // Só pode concluir serviço próprio (cliente sem promotor também não).
        $id_dono = (int) ($servico_concluir["id_promotor"] ?? 0);
        if ($id_promotor_logado === null || $id_dono !== $id_promotor_logado) {
            header("Location: servicos.php");
            exit;
        }

        if ($status_ok && festa_ja_aconteceu($servico_concluir["data_festa"] ?? "", $servico_concluir["horario"] ?? "")) {
            $upd = $conexao->prepare("UPDATE solicitacoes SET status = 'concluido' WHERE id = ? AND id_promotor = ? AND status IN ('aceito', 'aceita')");
            $upd->bind_param("ii", $id_concluir, $id_promotor_logado);
            $upd->execute();
            header("Location: servicos.php" . ($upd->affected_rows > 0 ? "?concluido=1" : "?id=" . $id_concluir), true, 303);
            exit;
        }

        if ($id_concluir > 0) {
            header("Location: servicos.php?id=" . $id_concluir . "&erro=antes_da_festa");
            exit;
        }
    }
    header("Location: servicos.php");
    exit;
}

require_once __DIR__ . "/../Includes/buscar-detalhe-solicitacao.php";
$id_solicitacao = max(0, (int) ($_GET["id"] ?? 0));
$detalhe_servico = $id_solicitacao > 0;
if ($detalhe_servico) {
    $dados = buscar_detalhe_solicitacao($conexao, $id_solicitacao, $id_promotor_logado);
    $status_servico = strtolower(trim((string) ($dados["status"] ?? "")));
    if (!in_array($status_servico, ["aceito", "aceita", "concluido", "concluida"], true)) {
        header("Location: servicos.php");
        exit;
    }
    $titulo_pagina = "Detalhes do serviço";
    if (in_array($status_servico, ["aceito", "aceita"], true) && intervalo_dias_evento((string) $dados["data_festa"]) === null) {
        $aviso_agenda = "Este serviço tem uma data inválida e não bloqueia a agenda. Confira o período registrado.";
    }
}

$por_pagina = 3;
$pagina = max(1, (int) ($_GET["pagina"] ?? 1));
$total = 0;
$servicos = [];
$total_paginas = 1;

if (!$detalhe_servico) {
    $stmt_contagem = $conexao->prepare("SELECT COUNT(*) AS total FROM solicitacoes WHERE id_promotor = ? AND status IN ('aceito', 'aceita')");
    $stmt_contagem->bind_param("i", $id_promotor_logado);
    $stmt_contagem->execute();
    $contagem = $stmt_contagem->get_result();
    $total = (int) $contagem->fetch_assoc()["total"];
    $total_paginas = max(1, (int) ceil($total / $por_pagina));
    if ($pagina > $total_paginas) {
        $pagina = $total_paginas;
    }
    $offset = ($pagina - 1) * $por_pagina;
    $consulta = $conexao->prepare(
        "SELECT s.*, c.nome AS conta_cliente, c.foto AS foto_cliente
         FROM solicitacoes s
         LEFT JOIN cliente c ON c.id = s.id_cliente
         WHERE s.id_promotor = ? AND s.status IN ('aceito', 'aceita')
         ORDER BY s.recebida DESC, s.id DESC
         LIMIT ? OFFSET ?"
    );
    $consulta->bind_param("iii", $id_promotor_logado, $por_pagina, $offset);
    $consulta->execute();
    $servicos = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?> | Nathus</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?= filemtime(__DIR__ . "/../Includes/css/global.css") ?>">
    <link rel="stylesheet" href="../Includes/css/layout.css?v=<?= filemtime(__DIR__ . "/../Includes/css/layout.css") ?>">
    <link rel="stylesheet" href="../Includes/css/icones-solicitacao.css?v=<?= filemtime(__DIR__ . "/../Includes/css/icones-solicitacao.css") ?>">
    <?php if ($detalhe_servico): ?>
        <link rel="stylesheet" href="../Includes/css/detalhe-solicitacao.css?v=<?= filemtime(__DIR__ . "/../Includes/css/detalhe-solicitacao.css") ?>">
        <link rel="stylesheet" href="../Includes/css/botao-voltar.css?v=<?= filemtime(__DIR__ . "/../Includes/css/botao-voltar.css") ?>">
    <?php else: ?>
        <link rel="stylesheet" href="../Includes/css/card-solicitacao.css?v=<?= filemtime(__DIR__ . "/../Includes/css/card-solicitacao.css") ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="CSS/servicos.css?v=<?= filemtime(__DIR__ . "/CSS/servicos.css") ?>">
</head>
<body class="pagina-servicos">
<div class="painel-container">
    <?php include __DIR__ . "/../Includes/sidebar.php"; ?>
    <main class="conteudo-principal">
        <?php include __DIR__ . "/../Includes/header.php"; ?>
        <?php if ($detalhe_servico): ?>
            <?php include __DIR__ . "/../Includes/detalhe-solicitacao.php"; ?>
        <?php else: ?>
            <div class="solicitacoes-pagina">
                <?php if (isset($_GET["aceito"])): ?>
                    <p class="servicos-sucesso" role="status">Solicitação aceita com sucesso.</p>
                <?php elseif (isset($_GET["concluido"])): ?>
                    <p class="servicos-sucesso" role="status">Serviço concluído com sucesso.</p>
                <?php endif; ?>
                <section aria-labelledby="servicos-aceitos">
                    <header class="solicitacoes-cabecalho">
                        <h2 id="servicos-aceitos">Serviços aceitos</h2>
                    </header>
                    <div class="lista-cards-solicitacao">
                        <?php if (!$servicos): ?>
                            <p class="solicitacoes-vazio">Nenhum serviço aceito no momento.</p>
                        <?php else: ?>
                            <?php foreach ($servicos as $solicitacao): ?>
                                <?php include __DIR__ . "/../Includes/card-solicitacao.php"; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($total_paginas > 1): ?>
                        <nav class="servicos-paginacao" aria-label="Paginação de serviços">
                            <?php if ($pagina > 1): ?>
                                <a href="?pagina=<?= $pagina - 1 ?>">Anterior</a>
                            <?php endif; ?>
                            <span>Página <?= $pagina ?> de <?= $total_paginas ?></span>
                            <?php if ($pagina < $total_paginas): ?>
                                <a href="?pagina=<?= $pagina + 1 ?>">Próxima</a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </section>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
