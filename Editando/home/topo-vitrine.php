<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$vt_inicio = $vt_inicio ?? 'homeAnt.php';
$vt_asset = $vt_asset ?? '../';
$logado = isset($_SESSION['usuario_id']);
$nome_conta = $logado ? (string) ($_SESSION['usuario_nome'] ?? 'Cliente') : '';
$foto_conta = '../assets/user_dashboard.svg';
$tem_foto_conta = false;
$rotulo_conta = 'Cliente';
$conta_promotor = false;
$vt_notificacoes = [];
$vt_tem_pendentes = false;

if ($logado) {
    $GLOBALS['base_url'] = '../';
    include_once __DIR__ . '/../Includes/conexao.php';
    include_once __DIR__ . '/../Includes/perfil_foto.php';
    include_once __DIR__ . '/../Includes/promotor.php';
    $conta_promotor = ehPromotor($conexao);
    [$foto_conta, $tem_foto_conta] = resolver_foto_perfil($conexao);
    if (function_exists('rotuloTipoUsuario')) {
        $rotulo_conta = rotuloTipoUsuario($conexao);
    }
    garantir_colunas_solicitacoes($conexao);
    $vt_dono = $conta_promotor
        ? obterPromotorExistente($conexao, (int) $_SESSION['usuario_id'])
        : (int) $_SESSION['usuario_id'];
    $vt_destino_notificacoes = $vt_asset . ($conta_promotor ? 'Solicitacoes/solicitacoes.php' : 'home/minhas-solicitacoes.php');
    if (!$conta_promotor) {
        require_once __DIR__ . '/../Includes/notificacoes-cliente.php';
        $vt_notificacoes = notificacoes_cliente($conexao, $vt_dono);
        $vt_tem_pendentes = in_array(true, array_column($vt_notificacoes, 'nova'), true);
    } elseif ($vt_dono !== null) {
        $vt_sql_notificacoes = "SELECT id, nome_evento FROM solicitacoes WHERE id_promotor = ? AND (status = 'pendente' OR status IS NULL OR status = '') ORDER BY id DESC LIMIT 3";
        $vt_stmt = $conexao->prepare($vt_sql_notificacoes);
        $vt_stmt->bind_param('i', $vt_dono);
        $vt_stmt->execute();
        $vt_notificacoes = $vt_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $vt_stmt->close();
        $vt_tem_pendentes = $vt_notificacoes !== [];
    }
}
?>
<header class="vt-topo">
    <a class="vt-logo" href="<?= htmlspecialchars($vt_inicio) ?>" aria-label="Nathus - início">
        <img src="<?= htmlspecialchars($vt_asset) ?>assets/LLOgo.png" alt="Nathus">
    </a>

    <?php if (!empty($vt_abas)): ?>
        <?php $vt_campo = $campo ?? 'organizadores'; ?>
        <nav class="vt-abas" aria-label="Categorias">
            <a class="vt-aba<?= $vt_campo === 'organizadores' ? ' vt-aba--ativa' : '' ?>" href="home.php?campo=organizadores">Organizadores</a>
            <a class="vt-aba<?= $vt_campo === 'buffet' ? ' vt-aba--ativa' : '' ?>" href="home.php?campo=buffet">Buffets</a>
            <a class="vt-aba<?= $vt_campo === 'espaco' ? ' vt-aba--ativa' : '' ?>" href="home.php?campo=espaco">Espaços de festa</a>
        </nav>
    <?php endif; ?>

    <div class="vt-acoes">
        <?php if (!$logado): ?>
            <a class="vt-btn vt-btn--texto" href="<?= htmlspecialchars($vt_asset) ?>Login/login.php">Entrar</a>
            <a class="vt-btn vt-btn--ouro" href="<?= htmlspecialchars($vt_asset) ?>Cadastro-geral/geral.php">Cadastre-se</a>
        <?php else: ?>
            <button class="vt-sino" id="vt-sino" type="button" aria-label="<?= !$conta_promotor && $vt_tem_pendentes ? 'Notificações: novas respostas' : 'Notificações' ?>" aria-expanded="false" aria-controls="vt-notificacoes">
                <img src="<?= htmlspecialchars($vt_asset) ?>assets/notificacao.svg" alt="">
                <span class="vt-sino-badge" aria-hidden="true"<?= $vt_tem_pendentes ? '' : ' hidden' ?>></span>
            </button>
            <section class="vt-notificacoes" id="vt-notificacoes" aria-labelledby="vt-notificacoes-titulo" hidden>
                <h2 id="vt-notificacoes-titulo">Solicitações recentes</h2>
                <div class="vt-notificacoes-itens"<?= $conta_promotor ? '' : ' data-notificacoes-cliente="' . htmlspecialchars($vt_asset . 'home/notificacoes-cliente.php') . '"' ?>>
                <?php if (!$vt_notificacoes): ?>
                    <p class="vt-notificacoes-vazio"><?= $conta_promotor ? 'Nenhuma solicitação pendente.' : 'Você ainda não enviou solicitações.' ?></p>
                <?php else: ?>
                    <?php foreach ($vt_notificacoes as $vt_notificacao): ?>
                        <?php
                        $vt_id_notificacao = (int) $vt_notificacao['id'];
                        $vt_link_notificacao = $conta_promotor
                            ? $vt_asset . 'Solicitacoes/solicitacoes_detalhes.php?id=' . $vt_id_notificacao
                            : $vt_destino_notificacoes . '?resposta=' . $vt_id_notificacao . '#solicitacao-' . $vt_id_notificacao;
                        ?>
                        <a class="vt-notificacao" href="<?= htmlspecialchars($vt_link_notificacao) ?>">
                            <strong><?= htmlspecialchars($vt_notificacao['nome_evento'] ?? 'Solicitação de evento') ?></strong>
                            <span><?= $conta_promotor ? 'Nova solicitação pendente' : htmlspecialchars(($vt_notificacao['nova'] ? 'Nova resposta · ' : '') . $vt_notificacao['rotulo']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
                <a class="vt-notificacoes-todas" href="<?= htmlspecialchars($vt_destino_notificacoes) ?>">Ver todas as solicitações</a>
            </section>
            <div class="vt-perfil conta-perfil conta-perfil--vitrine" id="vt-perfil">
                <button class="vt-perfil-botao botao-perfil" type="button" aria-expanded="false" aria-controls="vt-perfil-menu">
                    <span class="avatar-perfil vt-perfil-foto<?= $tem_foto_conta ? '' : ' avatar-vazio' ?>">
                        <img src="<?= htmlspecialchars($foto_conta, ENT_QUOTES, 'UTF-8') ?>" alt="">
                    </span>
                    <span class="vt-perfil-texto texto-perfil">
                        <strong><?= htmlspecialchars($nome_conta) ?></strong>
                        <small><?= htmlspecialchars($rotulo_conta) ?></small>
                    </span>
                    <img class="caret-perfil" src="<?= htmlspecialchars($vt_asset) ?>assets/seta_baixo.svg" alt="">
                </button>
                <div class="vt-perfil-menu menu-perfil" id="vt-perfil-menu">
                    <?php if ($conta_promotor): ?>
                        <a href="<?= htmlspecialchars($vt_asset) ?>Perfil/perfil.php">Meu perfil</a>
                        <a href="<?= htmlspecialchars($vt_asset) ?>Dashboard/dashboard.php">Dashboard</a>
                    <?php else: ?>
                        <button type="button" data-abrir-conta>Editar perfil</button>
                        <a href="<?= htmlspecialchars($vt_asset) ?>home/minhas-solicitacoes.php">Solicitações enviadas</a>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($vt_asset) ?>Includes/logout.php">Sair</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
<script>
(function () {
    var perfil = document.getElementById('vt-perfil');
    if (!perfil) return;
    var botao = perfil.querySelector('.vt-perfil-botao');
    var menu = document.getElementById('vt-perfil-menu');
    var sino = document.getElementById('vt-sino');
    var notificacoes = document.getElementById('vt-notificacoes');
    var itensCliente = notificacoes.querySelector('[data-notificacoes-cliente]');
    if (itensCliente) {
        var indicador = sino.querySelector('.vt-sino-badge');
        var destino = notificacoes.querySelector('.vt-notificacoes-todas').href;
        var consultando = false;
        var ultimaLista;

        async function atualizarNotificacoes() {
            if (document.hidden || consultando) return;
            consultando = true;
            try {
                var resposta = await fetch(itensCliente.dataset.notificacoesCliente, {cache: 'no-store'});
                if (!resposta.ok) return;
                var itens = await resposta.json();
                var temNova = itens.some(function (item) { return item.nova; });
                indicador.hidden = !temNova;
                sino.setAttribute('aria-label', temNova ? 'Notificações, novas respostas' : 'Notificações');

                var lista = JSON.stringify(itens);
                if (lista === ultimaLista || itensCliente.contains(document.activeElement)) return;
                ultimaLista = lista;
                if (!itens.length) {
                    var vazio = document.createElement('p');
                    vazio.className = 'vt-notificacoes-vazio';
                    vazio.textContent = 'Você ainda não enviou solicitações.';
                    itensCliente.replaceChildren(vazio);
                    return;
                }
                var links = itens.map(function (item) {
                    var url = new URL(destino);
                    url.searchParams.set('resposta', item.id);
                    url.hash = 'solicitacao-' + item.id;
                    var link = document.createElement('a');
                    link.className = 'vt-notificacao';
                    link.href = url.href;
                    var titulo = document.createElement('strong');
                    titulo.textContent = item.nome_evento ?? 'Solicitação de evento';
                    var status = document.createElement('span');
                    status.textContent = (item.nova ? 'Nova resposta · ' : '') + item.rotulo;
                    link.append(titulo, status);
                    return link;
                });
                itensCliente.replaceChildren(...links);
            } catch {
                // Mantém os avisos já exibidos se a conexão falhar.
            } finally {
                consultando = false;
            }
        }
        window.setInterval(atualizarNotificacoes, 15000);
        document.addEventListener('visibilitychange', atualizarNotificacoes);
        sino.addEventListener('click', atualizarNotificacoes);
    }
    function fecharNotificacoes() {
        notificacoes.hidden = true;
        sino.setAttribute('aria-expanded', 'false');
    }
    sino.addEventListener('click', function (evento) {
        evento.stopPropagation();
        notificacoes.hidden = !notificacoes.hidden;
        sino.setAttribute('aria-expanded', notificacoes.hidden ? 'false' : 'true');
        perfil.classList.remove('vt-perfil--aberto');
        botao.setAttribute('aria-expanded', 'false');
    });
    notificacoes.addEventListener('click', function (evento) { evento.stopPropagation(); });
    perfil.parentElement.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && !notificacoes.hidden) {
            fecharNotificacoes();
            sino.focus();
        }
    });
    botao.addEventListener('click', function (evento) {
        evento.stopPropagation();
        fecharNotificacoes();
        var aberto = perfil.classList.toggle('vt-perfil--aberto');
        botao.setAttribute('aria-expanded', aberto ? 'true' : 'false');
    });
    document.addEventListener('click', function () {
        fecharNotificacoes();
        perfil.classList.remove('vt-perfil--aberto');
        botao.setAttribute('aria-expanded', 'false');
    });
    menu.addEventListener('click', function (evento) {
        evento.stopPropagation();
    });
    perfil.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            perfil.classList.remove('vt-perfil--aberto');
            botao.setAttribute('aria-expanded', 'false');
            botao.focus();
        }
    });
    var editar = menu.querySelector('[data-abrir-conta]');
    if (editar) {
        editar.addEventListener('click', function () {
            perfil.classList.remove('vt-perfil--aberto');
            botao.setAttribute('aria-expanded', 'false');
            var modal = document.getElementById('vt-conta');
            if (modal && typeof modal.showModal === 'function' && !modal.open) {
                modal.showModal();
            }
        });
    }
})();
</script>
