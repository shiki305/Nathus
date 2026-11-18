<?php
function mostrar_data_festa($valor) {
    $valor = trim((string) $valor);
    if (datas_periodo_evento($valor)) return $valor;
    if ($valor === "") {
        return "";
    }
    if (preg_match("/(\d{2}\/\d{2}(?:\/\d{2,4})?)/", $valor, $m)) {
        return $m[1];
    }
    if (preg_match("/^(\d{4})-(\d{2})-(\d{2})/", $valor, $m)) {
        return $m[3] . "/" . $m[2] . "/" . $m[1];
    }
    $ts = strtotime(str_replace("/", "-", $valor));
    return $ts ? date("d/m/Y", $ts) : $valor;
}

$data_festa = trim((string) $dados["data_festa"]);
$horario = trim((string) $dados["horario"]);
$datas = datas_periodo_evento($data_festa);
$data_formatada = mostrar_data_festa($data_festa);
$data_iso = data_evento_iso($data_formatada);
$horas = preg_split('/\s*[-–]\s*/u', $horario, 2) ?: [];
$horas_validas = count($horas) === 2 && horario_evento_valido($horas[0]) && horario_evento_valido($horas[1]);
$periodo = $datas;
if (!$periodo && $horas_validas && $horas[0] <= $horas[1] && $data_iso !== null
    && in_array($data_festa, [$data_formatada, $data_iso], true)) {
    $periodo = [$data_formatada, $data_formatada];
}

$nome = trim((string) $dados["nome_cliente"]) ?: (trim((string) $dados["conta_cliente"]) ?: "Cliente");
$email = trim((string) $dados["email"]);
$telefone = trim((string) $dados["telefone"]);
$numero_tel = preg_replace('/[^0-9+]/', '', $telefone);
$convidados = (int) $dados["numero_convidados"];
$local = trim((string) $dados["local_festa"]);
$mensagem = trim((string) $dados["observacoes"]);
$evento = trim((string) $dados["nome_evento"]);

$pacote = trim((string) $dados["pacote_selecionado"]);
$preco = "";
if (preg_match('/^(.+?)\s+(R\$\s*[\d.]+,\d{2}|Preço sob consulta)$/suD', $pacote, $partes_pacote)) {
    [, $pacote, $preco] = $partes_pacote;
}
$base_url = "../";
$foto_pacote = midia_publica($dados["foto_pacote"], ["pacotes"], $base_url);
$itens_envio = $dados["pacote_itens_envio"];
$itens_pacote = array_filter(
    array_map("trim", preg_split('/\R/', (string) ($itens_envio ?? $dados["pacote_itens_atuais"]))),
    static fn($item) => $item !== ""
);
$titulo = $pacote ?: ($evento ?: ($detalhe_servico ? "Serviço" : "Solicitação de serviço"));
$registro = strtotime((string) ($detalhe_servico ? ($dados["respondida_em"] ?? "") : $dados["recebida"]));

[$estado, $rotulo_status] = match (strtolower(trim((string) $dados["status"]))) {
    "", "pendente" => ["pendente", "Pendente"],
    "aceito", "aceita" => ["aceita", $detalhe_servico ? "Aceito" : "Aceita"],
    "recusado", "recusada" => ["recusada", "Recusada"],
    "cancelado", "cancelada" => ["encerrada", "Cancelada"],
    "concluido", "concluida" => ["encerrada", $detalhe_servico ? "Concluído" : "Concluída"],
    default => ["encerrada", "Status indisponível"],
};
?>
<div class="sol-detalhe">
    <a class="sol-detalhe-voltar botao-voltar" href="<?= $detalhe_servico ? 'servicos.php' : 'solicitacoes.php' ?>"><?= $detalhe_servico ? 'Voltar para Meus Serviços' : 'Voltar para solicitações' ?></a>

    <header class="sol-detalhe-topo">
        <div>
            <h2><?= htmlspecialchars($titulo) ?></h2>
            <p><?= $detalhe_servico ? 'Serviço' : 'Solicitação' ?> #<?= $id_solicitacao ?><?php if ($registro !== false): ?> · <?= $detalhe_servico ? 'Aceito' : 'Recebida' ?> em <?= date('d/m/Y \à\s H:i', $registro) ?><?php endif; ?></p>
        </div>
        <span class="sol-detalhe-status sol-detalhe-status--<?= $estado ?>"><?= $rotulo_status ?></span>
    </header>

    <?php if (!empty($aviso_agenda)): ?>
        <p class="sol-detalhe-aviso" role="alert"><?= htmlspecialchars($aviso_agenda) ?></p>
    <?php endif; ?>

    <div class="sol-detalhe-grade<?= $pacote === "" ? ' sol-detalhe-grade--unica' : '' ?>">
        <div class="sol-detalhe-coluna">
            <section class="sol-detalhe-card">
                <h3>Dados de contato</h3>
                <div class="sol-detalhe-contato">
                    <?php renderizar_avatar_cliente($dados["foto_cliente"], $base_url); ?>
                    <div>
                        <strong><?= htmlspecialchars($nome) ?></strong>
                        <div class="sol-detalhe-contato-links">
                            <?php if (filter_var($email, FILTER_VALIDATE_EMAIL)): ?>
                                <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
                            <?php elseif ($email !== ""): ?>
                                <span><?= htmlspecialchars($email) ?></span>
                            <?php endif; ?>
                            <?php if ($numero_tel !== "" && $numero_tel !== "+"): ?>
                                <a href="tel:<?= htmlspecialchars($numero_tel) ?>"><?= htmlspecialchars($telefone) ?></a>
                            <?php elseif ($telefone !== ""): ?>
                                <span><?= htmlspecialchars($telefone) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="sol-detalhe-card sol-detalhe-card--evento">
                <h3>Dados do evento</h3>
                <dl class="sol-detalhe-dados">
                    <?php if ($periodo && $horas_validas): ?>
                        <div>
                            <dt class="label--data">Início</dt>
                            <dd><?= htmlspecialchars("$periodo[0] às $horas[0]") ?></dd>
                        </div>
                        <div>
                            <dt class="label--data">Término</dt>
                            <dd><?= htmlspecialchars("$periodo[1] às $horas[1]") ?></dd>
                        </div>
                    <?php else: ?>
                        <div>
                            <dt class="label--data"><?= $datas ? 'Datas do evento' : 'Data de início' ?></dt>
                            <dd><?= htmlspecialchars($data_formatada ?: "Não informada") ?></dd>
                        </div>
                        <div>
                            <dt class="label--horario">Horário</dt>
                            <dd><?= htmlspecialchars($horario ?: "Não informado") ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if ($evento !== "" && $evento !== $titulo): ?>
                        <div><dt>Tipo de festa</dt><dd><?= htmlspecialchars($evento) ?></dd></div>
                    <?php endif; ?>
                    <div>
                        <dt class="label--convidados">Número de convidados</dt>
                        <dd><?= $convidados > 0 ? number_format($convidados, 0, ",", ".") : "Não informado" ?></dd>
                    </div>
                    <div class="sol-detalhe-linha">
                        <dt class="label--local">Local da festa</dt>
                        <dd><?= htmlspecialchars($local ?: "Não informado") ?></dd>
                    </div>
                </dl>
                <?php if ($mensagem !== ""): ?>
                    <div class="sol-detalhe-bloco-mensagem">
                        <h4 class="label--mensagem">Mensagem do cliente</h4>
                        <p class="sol-detalhe-mensagem"><?= htmlspecialchars($mensagem) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($detalhe_servico): ?>
                    <?php include __DIR__ . "/../Servicos/acoes-detalhe.php"; ?>
                <?php elseif (in_array($estado, ["pendente", "aceita", "recusada"], true)): ?>
                    <form method="post" class="sol-detalhe-acoes">
                        <?php if ($estado === "pendente"): ?>
                            <button class="btn-responder btn-responder--recusar" type="submit" name="acao" value="recusar">Recusar</button>
                            <button class="btn-responder btn-responder--aceitar" type="submit" name="acao" value="aceitar">Aceitar solicitação</button>
                        <?php else: ?>
                            <button class="btn-responder" type="submit" name="acao" value="reverter">Voltar para pendente</button>
                            <?php if ($estado === "aceita"): ?>
                                <a class="btn-responder btn-responder--aceitar" href="../Servicos/servicos.php?id=<?= $id_solicitacao ?>">Ver em Meus Serviços</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </section>
        </div>
        <?php if ($pacote !== ""): ?>
            <section class="sol-detalhe-card sol-detalhe-card--pacote">
                <h3>Pacote selecionado</h3>
                <?php if ($foto_pacote !== ""): ?>
                    <img class="sol-detalhe-pacote-foto" src="<?= htmlspecialchars($foto_pacote) ?>" alt="" width="480" height="270">
                <?php endif; ?>
                <p class="sol-detalhe-pacote-nome"><?= htmlspecialchars($pacote) ?></p>
                <?php if ($itens_pacote): ?>
                    <h4 class="sol-detalhe-itens-titulo"><?= $itens_envio === null ? "Itens atuais do pacote" : "Itens no envio" ?></h4>
                    <ul class="sol-detalhe-itens">
                        <?php foreach ($itens_pacote as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($preco !== ""): ?>
                    <dl class="sol-detalhe-dados sol-detalhe-valor">
                        <div><dt>Valor no envio</dt><dd><?= htmlspecialchars($preco) ?></dd></div>
                    </dl>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>

</div>
