<?php
require_once __DIR__ . "/perfil_foto.php";
require_once __DIR__ . "/periodo-evento.php";
$resumo_dashboard = ($pagina_atual ?? "") === "dashboard";
$card_servico = ($pagina_atual ?? "") === "servicos";
$rota_detalhe = $card_servico ? "../Servicos/servicos.php" : "../Solicitacoes/solicitacoes_detalhes.php";

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
$meses = ["JAN", "FEV", "MAR", "ABR", "MAI", "JUN", "JUL", "AGO", "SET", "OUT", "NOV", "DEZ"];
$dia = $data_iso === null ? "" : substr($inicio, 0, 2);
$mes = $data_iso === null ? "" : $meses[(int) substr($inicio, 3, 2) - 1];
if ($datas && substr($datas[0], 3) === substr($datas[1], 3)) {
    $dia .= "–" . substr($datas[1], 0, 2);
}

$horario = trim((string) $solicitacao["horario"]);
$horas = preg_split('/\s*[-–]\s*/u', $horario, 2) ?: [];
$horas_validas = count($horas) === 2 && horario_evento_valido($horas[0]) && horario_evento_valido($horas[1]);
$periodo = $horario;
if ($datas) {
    $periodo = $horas_validas
        ? "$datas[0] às $horas[0] até $datas[1] às $horas[1]"
        : $data . ($horario !== "" ? " · " . $horario : "");
} elseif ($data_iso !== null) {
    $periodo = $horas_validas
        ? "$inicio às $horas[0] até $horas[1]"
        : $inicio . ($horario !== "" ? " · " . $horario : "");
} elseif ($data !== "") {
    $periodo = $data . ($horario !== "" ? " · " . $horario : "");
}

$cidade = "";
if (preg_match('~(?:^|,)\s*([^,/]+?)\s*(?:/|,)\s*([A-Z]{2})\s*$~u', (string) $solicitacao["local_festa"], $local)) {
    $cidade = $local[1] . ", " . $local[2];
}
?>
<article class="card-pendencia<?= $resumo_dashboard ? ' card-pendencia--resumo' : '' ?><?= $card_servico ? ' card-pendencia--servico' : '' ?>">
    <div class="card-pendencia-data">
        <?php if ($data_iso !== null): ?>
            <span title="<?= htmlspecialchars($datas ? $data : $inicio) ?>">
                <strong><?= htmlspecialchars($dia) ?></strong><small><?= htmlspecialchars($mes) ?></small>
            </span>
        <?php else: ?>
            <small><?= $data === "" ? "Sem data" : "Data do evento" ?></small>
        <?php endif; ?>
    </div>

    <div class="card-pendencia-corpo">
        <h3><?= htmlspecialchars($titulo) ?></h3>
        <div class="card-pendencia-cliente">
            <?php renderizar_avatar_cliente($solicitacao["foto_cliente"], $base_url); ?>
            <span><?= htmlspecialchars($nome) ?></span>
        </div>
        <?php if ($periodo !== "" || $convidados > 0 || $cidade !== ""): ?>
            <div class="card-pendencia-meta">
                <?php if ($periodo !== ""): ?>
                    <span class="<?= $data !== "" ? 'label--data' : 'label--horario' ?>"><?= htmlspecialchars($periodo) ?></span>
                <?php endif; ?>
                <?php if ($convidados > 0): ?>
                    <span class="label--convidados"><?= number_format($convidados, 0, ",", ".") ?> <?= $convidados === 1 ? "convidado" : "convidados" ?></span>
                <?php endif; ?>
                <?php if ($cidade !== ""): ?>
                    <span class="label--local"><?= htmlspecialchars($cidade) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (!$resumo_dashboard && $mensagem !== ""): ?>
            <p class="card-pendencia-mensagem"><strong class="label--mensagem">Mensagem:</strong> <?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>
    </div>

    <div class="card-pendencia-acoes">
        <?php if ($preco !== ""): ?>
            <div class="card-pendencia-valor"><span>Valor no envio</span><strong><?= htmlspecialchars($preco) ?></strong></div>
        <?php endif; ?>
        <a class="card-pendencia-detalhes" href="<?= $rota_detalhe ?>?id=<?= (int) $solicitacao["id"] ?>"
           aria-label="<?= htmlspecialchars("Ver detalhes de $titulo, de $nome") ?>">
            Ver detalhes
        </a>
        <?php if (!$resumo_dashboard && $registro !== false): ?>
            <small class="card-pendencia-recebida"><?= $card_servico ? "Aceito" : "Recebida" ?> em <time datetime="<?= date('Y-m-d\TH:i:s', $registro) ?>"><?= date('d/m/Y \à\s H:i', $registro) ?></time></small>
        <?php endif; ?>
    </div>
</article>
