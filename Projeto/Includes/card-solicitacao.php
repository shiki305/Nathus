<?php
require_once __DIR__ . "/perfil_foto.php";
require_once __DIR__ . "/periodo-evento.php";
require_once __DIR__ . "/promotor.php";
// O card se adapta à página que o inclui. A caixa de entrada (Solicitações)
// usa o desenho completo, com a foto do pacote; o Dashboard mostra só o
// essencial e leva para a leitura completa em Solicitações; a agenda
// (Meus Serviços) usa o mesmo corpo com o bloco de data.
$layout_card = $pagina_atual ?? "";
$card_entrada = $layout_card === "solicitacoes";
$card_servico = $layout_card === "servicos";
$card_simples = $layout_card === "dashboard";
$rota_detalhe = $card_servico ? "../Servicos/servicos.php" : "../Solicitacoes/solicitacoes_detalhes.php";
$rota_completa = "../Solicitacoes/solicitacoes.php";

$evento = trim((string) $solicitacao["nome_evento"]);
$pacote = trim((string) $solicitacao["pacote_selecionado"]);
$preco = "";
if (preg_match('/^(.+?)\s+(R\$\s*[\d.]+,\d{2}|Preço sob consulta)$/suD', $pacote, $partes_pacote)) {
    [, $pacote, $preco] = $partes_pacote;
}
$titulo = $pacote ?: ($evento ?: "Solicitação de serviço");
$nome = trim((string) $solicitacao["nome_cliente"]) ?: (trim((string) $solicitacao["conta_cliente"]) ?: "Cliente");
$mensagem = trim((string) $solicitacao["observacoes"]);
$registro = strtotime((string) ($card_servico ? ($solicitacao["respondida_em"] ?? "") : $solicitacao["recebida"]));
$convidados = (int) $solicitacao["numero_convidados"];

$data = trim((string) $solicitacao["data_festa"]);
$datas = datas_periodo_evento($data);
$inicio = $datas[0] ?? $data;
if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:\s.*)?$/D', $inicio, $partes_data)) {
    $inicio = "$partes_data[3]/$partes_data[2]/$partes_data[1]";
}
$data_iso = data_evento_iso($inicio);
$meses = ["JANEIRO", "FEVEREIRO", "MARÇO", "ABRIL", "MAIO", "JUNHO", "JULHO", "AGOSTO", "SETEMBRO", "OUTUBRO", "NOVEMBRO", "DEZEMBRO"];
$dia = $data_iso === null ? "" : substr($inicio, 0, 2);
$mes = $data_iso === null ? "" : $meses[(int) substr($inicio, 3, 2) - 1];
if ($datas && substr($datas[0], 3) === substr($datas[1], 3)) {
    $dia .= "–" . substr($datas[1], 0, 2);
}

// Caixa de entrada: como o card não tem bloco de data, a data do evento vai na
// linha do selo, ao lado dos convidados. O horário acompanha no desktop; no
// celular ele sai, porque ali caberia uma linha inteira só para ele (e o
// horário está no detalhe).
$periodo_dia = "";
$periodo_horas = "";
if ($card_entrada) {
    $horario = trim((string) $solicitacao["horario"]);
    $horas = preg_split('/\s*[-–]\s*/u', $horario, 2) ?: [];
    $horas_validas = count($horas) === 2 && horario_evento_valido($horas[0]) && horario_evento_valido($horas[1]);
    if ($datas) {
        $periodo_dia = $data;
        $periodo_horas = $horas_validas ? " · $horas[0] às $horas[1]" : ($horario !== "" ? " · " . $horario : "");
    } elseif ($data_iso !== null) {
        $periodo_dia = $inicio;
        $periodo_horas = $horas_validas ? " · $horas[0] às $horas[1]" : ($horario !== "" ? " · " . $horario : "");
    } elseif ($data !== "") {
        $periodo_dia = $data;
        $periodo_horas = $horario !== "" ? " · " . $horario : "";
    }
}

// Na agenda, a data é o centro do card: mostra quanto falta para o evento.
$dias_para_evento = null;
if ($card_servico && $data_iso !== null) {
    $hoje = new DateTimeImmutable("today");
    $dia_evento = new DateTimeImmutable($data_iso);
    if ($dia_evento >= $hoje) {
        $dias_para_evento = (int) $hoje->diff($dia_evento)->format("%a");
    }
}
$rotulo_dias = "";
if ($dias_para_evento === 0) {
    $rotulo_dias = "é hoje";
} elseif ($dias_para_evento === 1) {
    $rotulo_dias = "amanhã";
} elseif ($dias_para_evento !== null) {
    $rotulo_dias = "faltam $dias_para_evento dias";
}

$rotulo_acao = $card_servico ? "Ver serviço" : "Ver e responder";
$rotulo_registro = $card_servico ? "Confirmado" : "Recebida";

// Caixa de entrada e Dashboard: a foto do pacote ajuda a reconhecer o pedido.
$foto_pacote = ($card_entrada || $card_simples) ? midia_publica($solicitacao["pacote_imagem"] ?? "", ["pacotes"], $base_url) : "";

$classes_card = "card-pendencia";
if ($card_servico) $classes_card .= " card-pendencia--servico";
if ($card_entrada) $classes_card .= " card-pendencia--entrada";
if ($card_simples) $classes_card .= " card-pendencia--simples";
if ($foto_pacote !== "") $classes_card .= " card-pendencia--com-foto";
?>
<?php if ($card_simples): ?>
<a class="<?= $classes_card ?>" href="<?= $rota_completa ?>" aria-label="<?= htmlspecialchars("Ver solicitação: $titulo, de $nome") ?>">
<?php else: ?>
<article class="<?= $classes_card ?>">
<?php endif; ?>
    <?php if ($foto_pacote !== ""): ?>
        <div class="card-pendencia-foto">
            <img src="<?= htmlspecialchars($foto_pacote) ?>" alt="" width="160" height="120" loading="lazy" decoding="async">
        </div>
    <?php endif; ?>
    <?php if (!$card_simples): ?>
    <div class="card-pendencia-data">
        <?php if ($data_iso !== null): ?>
            <span title="<?= htmlspecialchars($datas ? $data : $inicio) ?>">
                <strong><?= htmlspecialchars($dia) ?></strong><small><?= htmlspecialchars($mes) ?></small>
            </span>
            <?php if ($rotulo_dias !== ""): ?>
                <small class="card-pendencia-dias"><?= htmlspecialchars($rotulo_dias) ?></small>
            <?php endif; ?>
        <?php else: ?>
            <small><?= $data === "" ? "Sem data" : "Data do evento" ?></small>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($card_simples): ?>
    <div class="card-pendencia-corpo">
        <h3><?= htmlspecialchars($titulo) ?></h3>
        <div class="card-pendencia-simples-linha">
            <div class="card-pendencia-cliente">
                <?php renderizar_avatar_cliente($solicitacao["foto_cliente"], $base_url); ?>
                <span><?= htmlspecialchars($nome) ?></span>
            </div>
            <?php if ($registro !== false): ?>
                <span class="card-pendencia-registro">Recebida em <time datetime="<?= date('Y-m-d\TH:i:s', $registro) ?>"><?= date('d/m/Y \à\s H:i', $registro) ?></time></span>
            <?php endif; ?>
        </div>
    </div>
    <span class="card-pendencia-seta" aria-hidden="true"></span>
    <?php else: ?>
    <?php
    ob_start();
    ?>
    <div class="card-pendencia-acoes">
        <?php if ($preco !== ""): ?>
            <div class="card-pendencia-valor"><span>Valor no envio</span><strong><?= htmlspecialchars($preco) ?></strong></div>
        <?php endif; ?>
        <a class="card-pendencia-detalhes" href="<?= $rota_detalhe ?>?id=<?= (int) $solicitacao["id"] ?>"
           aria-label="<?= htmlspecialchars("$rotulo_acao: $titulo, de $nome") ?>">
            <?= htmlspecialchars($rotulo_acao) ?>
        </a>
    </div>
    <?php
    $acoes_card = ob_get_clean();
    ?>

    <div class="card-pendencia-corpo">
        <div class="card-pendencia-etiqueta">
            <span class="card-pendencia-selo card-pendencia-selo--<?= $card_servico ? "confirmado" : "pendente" ?>"><?= $card_servico ? "Confirmado" : "Aguardando resposta" ?></span>
            <?php if ($card_entrada && $periodo_dia !== ""): ?>
                <span class="label--data"><?= htmlspecialchars($periodo_dia) ?><span class="card-pendencia-data-horas"><?= htmlspecialchars($periodo_horas) ?></span></span>
            <?php endif; ?>
            <?php if ($convidados > 0): ?>
                <span class="label--convidados"><?= number_format($convidados, 0, ",", ".") ?> <?= $convidados === 1 ? "convidado" : "convidados" ?></span>
            <?php endif; ?>
            <?php if ($registro !== false): ?>
                <span class="card-pendencia-registro"><?= $rotulo_registro ?> em <time datetime="<?= date('Y-m-d\TH:i:s', $registro) ?>"><?= date('d/m/Y \à\s H:i', $registro) ?></time></span>
            <?php endif; ?>
        </div>
        <h3><?= htmlspecialchars($titulo) ?></h3>
        <div class="card-pendencia-rodape">
            <div class="card-pendencia-rodape-cliente">
                <div class="card-pendencia-cliente">
                    <?php renderizar_avatar_cliente($solicitacao["foto_cliente"], $base_url); ?>
                    <span><?= htmlspecialchars($nome) ?></span>
                </div>
                <?php if ($card_servico && $mensagem !== ""): ?>
                    <p class="card-pendencia-mensagem" title="<?= htmlspecialchars($mensagem) ?>"><strong class="label--mensagem">Mensagem:</strong> <?= htmlspecialchars($mensagem) ?></p>
                <?php endif; ?>
            </div>
            <?= $acoes_card ?>
        </div>
    </div>
    <?php endif; ?>
<?php if ($card_simples): ?>
</a>
<?php else: ?>
</article>
<?php endif; ?>
