<?php
session_start();
include_once __DIR__ . '/../Includes/conexao.php';
include_once __DIR__ . '/../Includes/promotor.php';
require_once __DIR__ . '/../Includes/periodo-evento.php';

$id_cliente = exigirCliente($conexao, 'home/minhas-solicitacoes.php');
garantir_colunas_solicitacoes($conexao);

$base_url = '../';
$vt_inicio = 'homeAnt.php';

function ms_esc($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function ms_status($status) {
    $status = strtolower(trim((string) $status));
    if ($status === 'aceito' || $status === 'aceita') {
        return ['aceita', 'Aceita'];
    }
    if ($status === 'recusado' || $status === 'recusada') {
        return ['recusada', 'Recusada'];
    }
    if ($status === 'cancelado' || $status === 'cancelada') {
        return ['cancelada', 'Cancelada'];
    }
    if ($status === 'concluido' || $status === 'concluida') {
        return ['concluida', 'Concluída'];
    }
    return ['pendente', 'Aguardando resposta'];
}

function ms_prazo($item) {
    [$classe] = ms_status($item['status'] ?? '');
    if (!in_array($classe, ['pendente', 'aceita'], true)) {
        return null;
    }
    $origem = $classe === 'aceita'
        ? (($item['respondida_em'] ?? '') !== '' ? $item['respondida_em'] : ($item['recebida'] ?? ''))
        : ($item['recebida'] ?? '');
    $inicio = strtotime((string) $origem);
    if (!$inicio) {
        return ['aberto' => true, 'limite' => null];
    }
    $limite = $inicio + (2 * 86400);
    return ['aberto' => $limite > time(), 'limite' => $limite];
}

function ms_periodo(string $datas, string $horarios): string {
    $periodo = datas_periodo_evento($datas);
    $horas = preg_split('/\s*[-–]\s*/u', $horarios, 2) ?: [];
    if (count($horas) === 2 && horario_evento_valido($horas[0]) && horario_evento_valido($horas[1])) {
        if ($periodo) {
            return "$periodo[0] às $horas[0] até $periodo[1] às $horas[1]";
        }
        if (data_evento_iso($datas) !== null) {
            return "$datas às $horas[0] até $horas[1]";
        }
    }
    return implode(' · ', array_filter([$datas, $horarios], static fn($valor) => $valor !== '')) ?: 'Não informado';
}

function ms_pacote(string $pacote): array {
    $pacote = preg_replace('/\s+/', ' ', trim($pacote));
    if (preg_match('/^(.+?)\s+(R\$\s*[\d.]+,\d{2}|Preço sob consulta)$/u', $pacote, $partes)) {
        return [$partes[1], $partes[2]];
    }
    return [$pacote, ''];
}

function ms_categoria($campo) {
    $campo = strtolower(trim((string) $campo));
    if ($campo === 'buffet') return 'Buffet';
    if (in_array($campo, ['local', 'espaco', 'espaço'], true)) return 'Espaço para festas';
    if (in_array($campo, ['organizacao', 'organização'], true)) return 'Organizador de eventos';
    return 'Promotor';
}

function ms_data($valor) {
    $valor = trim((string) $valor);
    if ($valor === '') return 'Não informada';
    $ts = strtotime($valor);
    return $ts ? date('d/m/Y \à\s H:i', $ts) : $valor;
}

$aviso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cancelar') {
    $id_cancelar = (int) ($_POST['id'] ?? 0);
    $stmt_cancelar = $conexao->prepare(
        "SELECT id, status, recebida, respondida_em
         FROM solicitacoes
         WHERE id = ? AND id_cliente = ?
         LIMIT 1"
    );
    if ($stmt_cancelar) {
        $stmt_cancelar->bind_param('ii', $id_cancelar, $id_cliente);
        $stmt_cancelar->execute();
        $linha = $stmt_cancelar->get_result()->fetch_assoc();
        $stmt_cancelar->close();
        $prazo = $linha ? ms_prazo($linha) : null;
        if ($linha && $prazo && $prazo['aberto']) {
            $stmt_upd = $conexao->prepare(
                "UPDATE solicitacoes SET status = 'cancelada', resposta_pendente = NULL
                 WHERE id = ? AND id_cliente = ? AND status <=> ? AND respondida_em <=> ?"
            );
            $stmt_upd->bind_param('iiss', $id_cancelar, $id_cliente, $linha['status'], $linha['respondida_em']);
            $stmt_upd->execute();
            $aviso = $stmt_upd->affected_rows === 1 ? 'cancelada' : 'status-alterado';
            $stmt_upd->close();
        } else {
            $aviso = 'prazo';
        }
    }
}

$solicitacoes = [];
$resposta_aberta = (int) ($_GET['resposta'] ?? 0);
$stmt = $conexao->prepare(
    "SELECT s.id, s.id_promotor, s.nome_evento, s.data_festa, s.horario, s.local_festa, s.observacoes,
            s.numero_convidados, s.pacote_selecionado, s.recebida, s.respondida_em, s.status, s.resposta_pendente,
            pacote.imagem AS pacote_imagem, COALESCE(s.pacote_itens_envio, pacote.itens) AS pacote_itens,
            p.nome_fantasia, p.foto_perfil, c.nome AS nome_promotor,
            (SELECT pc.campo FROM promotor_campo pc WHERE pc.id_promotor = s.id_promotor ORDER BY pc.id LIMIT 1) AS campo
     FROM solicitacoes s
     LEFT JOIN promotor p ON p.id = s.id_promotor
     LEFT JOIN cliente c ON c.id = p.id_cliente
     LEFT JOIN promotor_pacote pacote ON pacote.id_promotor = s.id_promotor
        AND pacote.id = COALESCE(s.pacote_id, (
            SELECT anterior.id FROM promotor_pacote anterior
            WHERE anterior.id_promotor = s.id_promotor
              AND anterior.titulo IN (TRIM(SUBSTRING_INDEX(s.pacote_selecionado, CHAR(10), 1)),
                                     TRIM(SUBSTRING_INDEX(s.pacote_selecionado, ' R$', 1)))
            ORDER BY CHAR_LENGTH(anterior.titulo) DESC, anterior.id LIMIT 1
        ))
     WHERE s.id_cliente = ?
     ORDER BY s.id DESC"
);
if ($stmt) {
    $stmt->bind_param('i', $id_cliente);
    $stmt->execute();
    $solicitacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
foreach ($solicitacoes as $solicitacao) {
    if ((int) $solicitacao['id'] !== $resposta_aberta || $solicitacao['resposta_pendente'] === null) continue;
    $stmtLida = $conexao->prepare(
        'UPDATE solicitacoes SET resposta_pendente = NULL
         WHERE id = ? AND id_cliente = ? AND resposta_pendente = ? AND respondida_em <=> ?'
    );
    $stmtLida->bind_param('iiss', $resposta_aberta, $id_cliente, $solicitacao['resposta_pendente'], $solicitacao['respondida_em']);
    $stmtLida->execute();
    $stmtLida->close();
    break;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas solicitações | Nathus</title>
    <link rel="icon" type="image/png" href="../assets/favicon.png?v=2">
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . '/../Includes/css/global.css'); ?>">
    <link rel="stylesheet" href="css/vitrine.css?v=<?php echo filemtime(__DIR__ . '/css/vitrine.css'); ?>">
    <link rel="stylesheet" href="../Includes/css/icones-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/icones-solicitacao.css') ?>">
    <link rel="stylesheet" href="../Includes/css/botao-voltar.css?v=<?= filemtime(__DIR__ . '/../Includes/css/botao-voltar.css') ?>">
</head>
<body class="vt">
    <?php include __DIR__ . '/topo-vitrine.php'; ?>
    <main class="vt-lista sol-pagina">
        <a class="botao-voltar" href="homeAnt.php">Voltar</a>
        <header class="vt-cabeca">
            <div>
                <h1>Minhas solicitações</h1>
                <p>Acompanhe o status dos pedidos que você enviou aos promotores.</p>
            </div>
        </header>

        <?php if ($aviso === 'cancelada'): ?>
            <p class="sol-flash">Solicitação cancelada.</p>
        <?php elseif ($aviso === 'prazo'): ?>
            <p class="sol-flash sol-flash--erro">Não foi possível cancelar. O prazo de 2 dias já encerrou.</p>
        <?php elseif ($aviso === 'status-alterado'): ?>
            <p class="sol-flash sol-flash--erro">O status mudou. Confira o pedido antes de tentar cancelar de novo.</p>
        <?php endif; ?>

        <?php if (!$solicitacoes): ?>
            <section class="sol-vazia">
                <p>Você ainda não enviou nenhuma solicitação.</p>
                <a href="homeAnt.php">Explorar promotores</a>
            </section>
        <?php else: ?>
            <section class="sol-lista">
                <?php foreach ($solicitacoes as $item): ?>
                    <?php
                        [$classe, $rotulo] = ms_status($item['status'] ?? '');
                        $nome = trim((string) ($item['nome_fantasia'] ?? '')) !== '' ? $item['nome_fantasia'] : ($item['nome_promotor'] ?? 'Promotor');
                        $foto = foto_card_promotor($item, $base_url);
                        [$pacote, $preco] = ms_pacote((string) ($item['pacote_selecionado'] ?? ''));
                        $foto_pacote = midia_publica($item['pacote_imagem'] ?? '', ['pacotes'], $base_url);
                        $itens_pacote = array_values(array_filter(
                            array_map('trim', preg_split('/\R/', (string) ($item['pacote_itens'] ?? ''))),
                            static fn($texto) => $texto !== ''
                        ));
                        $evento = trim((string) ($item['nome_evento'] ?? ''));
                        $titulo_pedido = $pacote !== '' ? $pacote : ($evento !== '' ? $evento : 'Solicitação de serviço');
                        $mensagem = trim((string) ($item['observacoes'] ?? ''));
                        $num_convidados = (int) ($item['numero_convidados'] ?? 0);
                        $prazo = ms_prazo($item);
                    ?>
                    <article class="sol-card" id="solicitacao-<?= (int) $item['id'] ?>">
                        <div class="sol-topo">
                            <div class="sol-promotor">
                                <?php if ($foto !== ''): ?>
                                    <img src="<?= ms_esc($foto) ?>" width="44" height="44" loading="lazy" alt="">
                                <?php endif; ?>
                                <div>
                                    <?php if ((int) $item['id_promotor'] > 0): ?>
                                        <h2><a href="../Cliente/perfil.php?id=<?= (int) $item['id_promotor'] ?>&amp;ver_como_cliente=1"><?= ms_esc($nome) ?></a></h2>
                                    <?php else: ?>
                                        <h2><?= ms_esc($nome) ?></h2>
                                    <?php endif; ?>
                                    <span><?= ms_esc(ms_categoria($item['campo'] ?? '')) ?></span>
                                </div>
                            </div>
                            <b class="sol-status sol-status--<?php echo ms_esc($classe); ?>"><?php echo ms_esc($rotulo); ?></b>
                        </div>
                        <div class="sol-resumo">
                            <?php if ($foto_pacote !== ''): ?>
                                <img class="sol-foto-pacote" src="<?= ms_esc($foto_pacote) ?>" width="120" height="80" loading="lazy" alt="">
                            <?php endif; ?>
                            <div class="sol-pacote">
                                <p><strong><?= ms_esc($titulo_pedido) ?></strong></p>
                                <?php if ($evento !== '' && $evento !== $titulo_pedido): ?>
                                    <p class="sol-evento">Tipo de evento · <?= ms_esc($evento) ?></p>
                                <?php endif; ?>
                                <?php if ($itens_pacote): ?>
                                    <ul<?= count($itens_pacote) > 3 ? ' tabindex="0" role="list" aria-label="Itens inclusos no pacote (role para ver todos)"' : '' ?>>
                                        <?php foreach ($itens_pacote as $item_pacote): ?>
                                            <li><?= ms_esc($item_pacote) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            <?php if ($preco !== ''): ?>
                                <p class="sol-preco"><?= ms_esc($preco) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($item['respondida_em'])): ?>
                            <p class="sol-resposta">Respondida em <?= ms_esc(ms_data($item['respondida_em'])) ?></p>
                        <?php endif; ?>
                        <dl class="sol-periodo">
                            <div><dt class="label--data">Data e horário</dt><dd><?= ms_esc(ms_periodo(trim((string) ($item['data_festa'] ?? '')), trim((string) ($item['horario'] ?? '')))) ?></dd></div>
                            <div><dt class="label--convidados">Convidados</dt><dd><?= $num_convidados > 0 ? $num_convidados : 'Não informado' ?></dd></div>
                            <div class="sol-local"><dt>Local</dt><dd><?= ms_esc($item['local_festa'] ?: 'Não informado') ?></dd></div>
                        </dl>
                        <?php if ($mensagem !== ''): ?>
                            <p class="sol-mensagem-card"><?= ms_esc($mensagem) ?></p>
                        <?php endif; ?>
                        <p class="sol-envio">Enviada em <?= ms_esc(ms_data($item['recebida'] ?? '')) ?></p>
                        <?php if ($prazo && $prazo['aberto']): ?>
                            <?php $id_cancelar = (int) $item['id']; ?>
                            <div class="sol-prazo">
                                <p><?= $prazo['limite'] ? 'Pode cancelar até ' . ms_esc(date('d/m/Y \à\s H:i', $prazo['limite'])) . '.' : 'Cancelamento disponível.' ?></p>
                                <button type="button" class="sol-prazo-cancelar"
                                        data-dialogo="cancelar-<?= $id_cancelar ?>" aria-haspopup="dialog">
                                    Cancelar solicitação
                                </button>
                                <dialog class="sol-dialogo" id="cancelar-<?= $id_cancelar ?>"
                                        aria-labelledby="cancelar-titulo-<?= $id_cancelar ?>"
                                        aria-describedby="cancelar-texto-<?= $id_cancelar ?>">
                                    <h3 id="cancelar-titulo-<?= $id_cancelar ?>">Cancelar solicitação?</h3>
                                    <p id="cancelar-texto-<?= $id_cancelar ?>">O pedido para <?= ms_esc($nome) ?> será encerrado e o promotor não poderá mais respondê-lo. Essa ação não pode ser desfeita.</p>
                                    <div class="sol-dialogo-acoes">
                                        <form method="dialog">
                                            <button type="submit" class="sol-dialogo-voltar" autofocus>Voltar</button>
                                        </form>
                                        <form method="post">
                                            <input type="hidden" name="acao" value="cancelar">
                                            <input type="hidden" name="id" value="<?= $id_cancelar ?>">
                                            <button type="submit" class="sol-dialogo-cancelar">Cancelar solicitação</button>
                                        </form>
                                    </div>
                                </dialog>
                            </div>
                        <?php elseif ($classe === 'pendente' || $classe === 'aceita'): ?>
                            <p class="sol-aviso">O prazo de 2 dias para cancelar esta solicitação encerrou.</p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/conta-modal.php'; ?>
    <script>
        document.addEventListener('click', (evento) => {
            const gatilho = evento.target.closest('[data-dialogo]');
            if (!gatilho) return;
            const dialogo = document.getElementById(gatilho.dataset.dialogo);
            if (dialogo && typeof dialogo.showModal === 'function' && !dialogo.open) {
                dialogo.showModal();
            }
        });
    </script>
</body>
</html>
