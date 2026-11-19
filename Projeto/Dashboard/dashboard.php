<?php
require_once __DIR__ . "/../Includes/periodo-evento.php";
require_once __DIR__ . "/../Includes/perfil_foto.php";

session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");
require_once __DIR__ . "/../Includes/solicitacoes-pendentes.php";

// Página exclusiva de PROMOTOR: cliente é levado ao "Explorar".
$id_promotor_logado = exigirPromotor($conexao);

// Migração automática das colunas novas (id_promotor, status).
garantir_colunas_solicitacoes($conexao);

$base_url = "../";
$pagina_atual = "dashboard";

function data_agenda(string $data): ?string {
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:\s.*)?$/D', $data, $partes)) {
        $data = "$partes[3]/$partes[2]/$partes[1]";
    } elseif (preg_match('/^(\d{2}\/\d{2})\/(\d{2})$/D', $data, $partes)) {
        $data = "$partes[1]/20$partes[2]";
    }
    return data_evento_iso($data);
}

function mes_agenda(DateTimeImmutable $data): string {
    $meses = ["jan", "fev", "mar", "abr", "mai", "jun", "jul", "ago", "set", "out", "nov", "dez"];
    return $meses[(int) $data->format("n") - 1];
}

function contar_solicitacoes($conexao, $sql) {
    $resultado = $conexao->query($sql);
    if (!$resultado) {
        return 0;
    }
    return (int) $resultado->fetch_assoc()["total"];
}

$filtro_dono = $id_promotor_logado !== null
    ? " WHERE id_promotor = " . $id_promotor_logado
    : " WHERE 0"; // sem perfil de promotor: não vê solicitações

$total_recebidas = contar_solicitacoes($conexao, "SELECT COUNT(*) AS total FROM solicitacoes" . $filtro_dono);
$total_aceitas = contar_solicitacoes($conexao, "SELECT COUNT(*) AS total FROM solicitacoes" . $filtro_dono . " AND status IN ('aceito', 'aceita', 'concluido', 'concluida')");

$nota_dashboard = 0.0;
$stmtNota = $conexao->prepare("SELECT nota_media FROM promotor WHERE id = ? LIMIT 1");
if ($stmtNota) {
    $stmtNota->bind_param("i", $id_promotor_logado);
    $stmtNota->execute();
    $linhaNota = $stmtNota->get_result()->fetch_assoc();
    $nota_dashboard = (float) ($linhaNota["nota_media"] ?? 0);
}

$ano_grafico = (int) date("Y");
$por_mes = array_fill(1, 12, 0);
$stmtMes = $conexao->prepare(
    "SELECT MONTH(recebida) AS mes, COUNT(*) AS total
     FROM solicitacoes
     WHERE id_promotor = ? AND recebida IS NOT NULL AND YEAR(recebida) = ?
     GROUP BY MONTH(recebida)"
);
if ($stmtMes) {
    $stmtMes->bind_param("ii", $id_promotor_logado, $ano_grafico);
    $stmtMes->execute();
    $resMes = $stmtMes->get_result();
    while ($linhaMes = $resMes->fetch_assoc()) {
        $mes = (int) $linhaMes["mes"];
        if ($mes >= 1 && $mes <= 12) {
            $por_mes[$mes] = (int) $linhaMes["total"];
        }
    }
}
$pico_grafico = max($por_mes);
$nomes_meses = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];
$barras = [];
foreach ($nomes_meses as $indice => $nome_mes) {
    $quantidade = $por_mes[$indice + 1];
    $barras[] = [
        "mes" => $nome_mes,
        "quantidade" => $quantidade,
        "altura" => $quantidade === 0 || $pico_grafico === 0 ? 0 : max(8, (int) round(($quantidade / $pico_grafico) * 100)),
    ];
}

$proximos = [];
$total_eventos_futuros = 0;
$consulta_proximos = $conexao->prepare(
    "SELECT s.id, s.data_festa, s.horario, s.nome_evento, s.nome_cliente,
            s.pacote_selecionado, s.local_festa, c.foto AS foto_cliente
     FROM solicitacoes s
     LEFT JOIN cliente c ON c.id = s.id_cliente
     WHERE s.id_promotor = ? AND s.status IN ('aceito', 'aceita')
     ORDER BY s.recebida DESC, s.id DESC"
);
$consulta_proximos->bind_param("i", $id_promotor_logado);
$consulta_proximos->execute();
$servicos_agenda = $consulta_proximos->get_result();
$agora = new DateTimeImmutable("now", new DateTimeZone("America/Sao_Paulo"));
while ($servico = $servicos_agenda->fetch_assoc()) {
    $datas = trim((string) $servico["data_festa"]);
    [$data_inicio, $data_fim] = datas_periodo_evento($datas) ?: [$datas, $datas];
    $inicio_iso = data_agenda($data_inicio);
    $fim_iso = data_agenda($data_fim);
    if ($inicio_iso === null || $fim_iso === null || $fim_iso < $inicio_iso) continue;

    $horas = preg_split('/\s*[-–]\s*/u', trim((string) $servico["horario"]), 2) ?: [];
    $hora_inicio = horario_evento_valido($horas[0] ?? "") ? $horas[0] : "";
    $hora_fim = horario_evento_valido($horas[1] ?? "") ? $horas[1] : "";
    $inicio = new DateTimeImmutable($inicio_iso . " " . ($hora_inicio ?: "00:00"), $agora->getTimezone());
    $fim = new DateTimeImmutable($fim_iso . " " . ($hora_fim ?: "23:59:59"), $agora->getTimezone());
    if ($inicio_iso === $fim_iso && $hora_inicio !== "" && $hora_fim !== "" && $hora_fim < $hora_inicio) {
        $fim = $fim->modify("+1 day");
    }
    if ($fim <= $agora) continue;

    if ($inicio > $agora) {
        $total_eventos_futuros++;
    }
    if (count($proximos) < 3) {
        $proximos[] = $servico + compact("inicio", "fim", "hora_inicio", "hora_fim");
    }
}

$stats = [
    ["label" => "Solicitações recebidas", "value" => (string) $total_recebidas, "icone" => "relogio_pendentes_dashboard.svg", "cor" => "azul"],
    ["label" => "Solicitações aceitas", "value" => (string) $total_aceitas, "icone" => "solic_aceitas_dashboard.svg", "cor" => "verde"],
    ["label" => "Próximos eventos", "value" => (string) $total_eventos_futuros, "icone" => "marcados_dashboard.svg", "cor" => "roxo"],
    ["label" => "Avaliações médias", "value" => number_format($nota_dashboard, 1, ",", "."), "icone" => "avaliacao_dashboard.svg", "cor" => "laranja"],
];

$solicitacoes = solicitacoes_pendentes($conexao, $id_promotor_logado, 3);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nathus — Dashboard</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/layout.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/layout.css"); ?>">
    <link rel="stylesheet" href="./css/dashboard.css?v=<?php echo filemtime(__DIR__ . "/css/dashboard.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/card-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/card-solicitacao.css') ?>">
    <link rel="stylesheet" href="../Includes/css/icones-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/icones-solicitacao.css') ?>">
</head>
<body>
    <div class="painel-container">
        <?php include __DIR__ . '/../Includes/sidebar.php'; ?>

        <main class="conteudo-principal">
            <?php include __DIR__ . '/../Includes/header.php'; ?>

            <div class="painel-conteudo">

                <section class="linha-status">
                    <?php foreach ($stats as $stat): ?>
                        <article class="card-status">
                            <div class="status-icone status-<?php echo $stat["cor"]; ?>">
                                <img src="../assets/<?php echo $stat["icone"]; ?>" alt="">
                            </div>
                            <div class="status-texto">
                                <span class="status-rotulo"><?php echo $stat["label"]; ?></span>
                                <strong class="status-numero"><?php echo $stat["value"]; ?></strong>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="linha-meio">

                    <div class="painel estatisticas-painel">
                        <header class="painel-cabecalho">
                            <h2>Solicitações em <?php echo $ano_grafico; ?></h2>
                        </header>
                        <div class="painel-corpo">
                            <?php if ($pico_grafico === 0): ?>
                                <p class="lista-vazia">Nenhuma solicitação recebida em <?php echo $ano_grafico; ?>.</p>
                            <?php endif; ?>
                            <div class="grafico">
                                <?php foreach ($barras as $barra): ?>
                                    <?php
                                    $quantidade = $barra["quantidade"];
                                    $contagem_mes = $quantidade . ($quantidade === 1 ? " solicitação" : " solicitações");
                                    ?>
                                    <div class="grafico-coluna" role="img" aria-label="<?= htmlspecialchars($barra["mes"] . ", " . $contagem_mes) ?>">
                                        <?php if ($quantidade > 0): ?>
                                            <div class="grafico-barra" style="height: <?= (int) $barra["altura"] ?>%" title="<?= htmlspecialchars($contagem_mes) ?>">
                                                <span class="grafico-valor" aria-hidden="true"><?= $quantidade ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="grafico-meses">
                                <?php foreach ($barras as $barra): ?>
                                    <span><?php echo $barra["mes"]; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="painel proximos-painel">
                        <header class="painel-cabecalho">
                            <h2>Próximos Serviços</h2>
                            <a href="../Servicos/servicos.php" class="ver-todas" aria-label="Ver todos os serviços">Ver todos</a>
                        </header>
                        <div class="proximos-lista">
                            <?php if (!$proximos): ?>
                                <p class="lista-vazia">Nenhum serviço agendado.</p>
                            <?php else: ?>
                                <?php foreach ($proximos as $p): ?>
                                    <?php
                                        $inicio = $p["inicio"];
                                        $fim = $p["fim"];
                                        $varios_dias = $inicio->format("Y-m-d") !== $fim->format("Y-m-d");
                                        $mesmo_mes = $inicio->format("Y-m") === $fim->format("Y-m");
                                        $dia_selo = $inicio->format("j");
                                        if ($varios_dias && $mesmo_mes) $dia_selo .= "–" . $fim->format("j");

                                        $pacote = preg_split('/\R/', trim((string) $p["pacote_selecionado"]), 2)[0];
                                        $pacote = preg_replace('/\s+(?:R\$\s*[\d.,]+|Preço sob consulta)$/iu', '', $pacote);
                                        $nome_servico = $pacote ?: (trim((string) $p["nome_evento"]) ?: "Serviço");
                                        $cliente = trim((string) $p["nome_cliente"]) ?: "Cliente";
                                    ?>
                                    <a class="proximo-item" href="../Servicos/servicos.php?id=<?php echo (int) $p["id"]; ?>">
                                        <span class="proximo-data" aria-hidden="true">
                                            <strong><?= htmlspecialchars($dia_selo) ?></strong>
                                            <span><?= htmlspecialchars(mes_agenda($inicio)) ?></span>
                                        </span>
                                        <div class="proximo-dados">
                                            <strong><?php echo htmlspecialchars($nome_servico); ?></strong>
                                            <span class="proximo-cliente">
                                                <?php renderizar_avatar_cliente($p["foto_cliente"], $base_url); ?>
                                                <span><?= htmlspecialchars($cliente) ?></span>
                                            </span>
                                        </div>
                                        <span class="proximo-seta"><img src="../assets/seta_solicitacoes.svg" alt=""></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                </section>

                <section class="painel solicitacoes-painel">
                    <header class="painel-cabecalho">
                        <div class="painel-titulo-pendencias">
                            <h2>Solicitações pendentes</h2>
                            <span class="solicitacoes-contagem"><?= $solicitacoes_pendentes_sidebar ?></span>
                        </div>
                        <a href="../Solicitacoes/solicitacoes.php" class="ver-todas" aria-label="Ver todas as solicitações pendentes">Ver todas</a>
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
