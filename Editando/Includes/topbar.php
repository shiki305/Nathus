<?php
/**
 * Topbar Unificado — Usado por home/ (organizadores, buffet, espaco) e Cliente/ (perfil, solicitacao)
 *
 * Variáveis REQUERIDAS (definir ANTES do include):
 *   $base_url        string  Ex: '../' ou '../../'
 *   $secao_ativa     string  'organizadores' | 'buffet' | 'espaco'
 *   $show_notifications bool   true = mostra sino + dropdown notificações (home com user logado)
 *
 * Variáveis OPCIONAIS:
 *   $show_meu_perfil  bool   Se true, mostra "Meu Perfil" no dropdown (default: $eh_promotor_logado)
 */
if (!isset($base_url)) { $base_url = '../'; }
if (!isset($secao_ativa)) { $secao_ativa = 'organizadores'; }
if (!isset($show_notifications)) { $show_notifications = true; }

$logado = isset($_SESSION['usuario_id']);
$nome_conta = $logado ? (string) ($_SESSION['usuario_nome'] ?? 'Usuário') : '';
$rotulo_conta = 'Visitante';
$tem_foto_topo = false;
$foto_topo = $base_url . 'assets/user_dashboard.svg';
$eh_promotor_logado = false;
$notificacoes = [];

if ($logado) {
    include_once __DIR__ . '/conexao.php';
    include_once __DIR__ . '/promotor.php';
    include_once __DIR__ . '/perfil_foto.php';

    $rotulo_conta = rotuloTipoUsuario($conexao);
    $eh_promotor_logado = ($rotulo_conta === 'Promotor');
    [$foto_topo, $tem_foto_topo] = resolver_foto_perfil($conexao);

    // Notificações: qualquer user logado (promotor OU cliente) SE $show_notifications=true
    if ($show_notifications) {
        if ($eh_promotor_logado) {
            // Promotor: solicitações PARA ele
            $id_promotor_notif = obterPromotorExistente($conexao, (int) $_SESSION['usuario_id']);
            if ($id_promotor_notif !== null) {
                $sql_notif = "SELECT id, nome_evento FROM solicitacoes
                              WHERE id_promotor = ?
                                AND (status = 'pendente' OR status IS NULL OR status = '')
                              ORDER BY id DESC LIMIT 3";
                $stmt_notif = $conexao->prepare($sql_notif);
                if ($stmt_notif) {
                    $stmt_notif->bind_param('i', $id_promotor_notif);
                    $stmt_notif->execute();
                    $result_notif = $stmt_notif->get_result();
                    while ($row = $result_notif->fetch_assoc()) {
                        $notificacoes[] = $row;
                    }
                }
            }
        } else {
            // Cliente: solicitações FEITAS por ele (busca por email da sessão)
            $email_cliente = $_SESSION['usuario_email'] ?? '';
            if ($email_cliente) {
                $sql_notif = "SELECT id, nome_evento FROM solicitacoes
                              WHERE email = ?
                                AND (status = 'pendente' OR status IS NULL OR status = '')
                              ORDER BY id DESC LIMIT 3";
                $stmt_notif = $conexao->prepare($sql_notif);
                if ($stmt_notif) {
                    $stmt_notif->bind_param('s', $email_cliente);
                    $stmt_notif->execute();
                    $result_notif = $stmt_notif->get_result();
                    while ($row = $result_notif->fetch_assoc()) {
                        $notificacoes[] = $row;
                    }
                }
            }
        }
    }
}

$show_meu_perfil = isset($show_meu_perfil) ? $show_meu_perfil : $eh_promotor_logado;

// Itens de navegação
$nav_items = [
    'organizadores' => ['href' => 'home/organizadores.php', 'icon' => 'topo/organizadores.png', 'label' => 'Organizadores'],
    'espaco'        => ['href' => 'home/espaco.php',        'icon' => 'topo/espaco.png',        'label' => 'Espaço'],
    'buffet'        => ['href' => 'home/buffet.php',        'icon' => 'topo/buffet.png',        'label' => 'Buffet'],
];
?>
<header class="topo">
    <a class="topo-logo" href="<?= $base_url ?>home/homeAnt.php" aria-label="Nathus - início">
        <img src="<?= $base_url ?>assets/LLOgo.png" alt="Nathus">
    </a>

    <nav class="topo-nav" aria-label="Navegação principal">
        <?php foreach ($nav_items as $key => $item): ?>
            <a class="topo-link<?= ($secao_ativa ?? '') === $key ? ' topo-link--ativo' : '' ?>"
               href="<?= $base_url ?><?= $item['href'] ?>">
                <img class="topo-icone topo-icone--<?= $key ?>" src="<?= $base_url ?>assets/<?= $item['icon'] ?>" alt="">
                <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="topo-conta">
        <?php if (!$logado): ?>
            <!-- NÃO LOGADO: só botão "Entrar" -->
            <a class="topo-entrar" href="<?= $base_url ?>Login/login.php">Entrar</a>
        <?php else: ?>
            <!-- LOGADO: avatar + nome + rótulo + dropdown -->
            <?php if ($show_notifications): ?>
                <!-- Sino de notificações (qualquer user logado se $show_notifications=true) -->
                <div class="topo-sino-envelope">
                    <button class="topo-sino" id="topo-sino" aria-label="Notificações">
                        <img src="<?= $base_url ?>assets/notificacao.svg" alt="">
                        <?php if (count($notificacoes) > 0): ?>
                            <span class="topo-badge"></span>
                        <?php endif; ?>
                    </button>
                    <div class="topo-notificacoes" id="topo-notificacoes">
                        <div class="topo-notificacoes-cabecalho">
                            <h4>Solicitações Recentes</h4>
                        </div>
                        <div class="topo-notificacoes-lista">
                            <?php if (count($notificacoes) > 0): ?>
                                <?php foreach ($notificacoes as $notif): ?>
                                    <a href="<?= $base_url ?>Solicitacoes/solicitacoes_detalhes.php?id=<?= $notif['id'] ?>" class="topo-notificacao">
                                        <strong><?= htmlspecialchars($notif['nome_evento'] ?? 'Novo Pedido') ?></strong>
                                        <span><?= $eh_promotor_logado ? 'Nova solicitação pendente' : 'Sua solicitação está pendente' ?></span>
                                    </a>
                                <?php endforeach; ?>
                                <a href="<?= $base_url ?>Solicitacoes/solicitacoes.php" class="topo-notificacoes-todas">Ver todas</a>
                            <?php else: ?>
                                <p class="topo-notificacoes-vazio">Nenhuma solicitação pendente.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="topo-perfil" id="topo-perfil">
                <button class="topo-botao" id="topo-botao" type="button" aria-label="Menu da conta">
                    <span class="topo-avatar<?= $tem_foto_topo ? '' : ' topo-avatar--vazio' ?>">
                        <img src="<?= htmlspecialchars($foto_topo, ENT_QUOTES, 'UTF-8') ?>" alt="">
                    </span>
                    <span class="topo-informacao">
                        <strong><?= htmlspecialchars($nome_conta) ?></strong>
                        <small><?= htmlspecialchars($rotulo_conta) ?></small>
                    </span>
                    <img src="<?= $base_url ?>assets/seta_baixo.svg" alt="" class="topo-seta">
                </button>
                <div class="topo-menu" id="topo-menu">
                    <?php if ($show_meu_perfil): ?>
                        <a class="topo-opcao" href="<?= $base_url ?>Perfil/perfil.php">Meu Perfil</a>
                    <?php endif; ?>
                    <a class="topo-opcao" href="<?= $base_url ?>home/homeAnt.php?editar=1">Editar perfil</a>
                    <a class="topo-opcao" href="<?= $base_url ?>Includes/logout.php">Sair</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>