<?php
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiro_nome = explode(' ', trim($nome_usuario))[0];
$base_url = $base_url ?? "../";

$titulo_pagina = $titulo_pagina ?? 'Dashboard';

include_once(__DIR__ . "/perfil_foto.php");
if(isset($_SESSION['usuario_id']) && !isset($conexao)){
    include_once(__DIR__ . "/conexao.php");
}
[$foto_perfil_topo, $tem_foto_topo] = resolver_foto_perfil($conexao ?? null);

// Identidade do topo: promotor mostra o nome fantasia.
// Usa tipo_conta + perfil de promotor (não rebaixa promotor à toa).
$rotulo_tipo_usuario = 'Visitante';
if(isset($conexao) && isset($_SESSION['usuario_id'])){
    include_once(__DIR__ . "/promotor.php");
    $id_promotor_topo = resolverPromotorId($conexao, (int) $_SESSION['usuario_id']);
    if($id_promotor_topo !== null || ehPromotor($conexao, (int) $_SESSION['usuario_id'])){
        $_SESSION['usuario_tipo'] = 'promotor';
        if($id_promotor_topo !== null){
            $_SESSION['promotor_id'] = $id_promotor_topo;
        }
        $rotulo_tipo_usuario = 'Promotor';
    } else {
        $_SESSION['usuario_tipo'] = 'cliente';
        unset($_SESSION['promotor_id']);
        $rotulo_tipo_usuario = 'Cliente';
    }
}

// ============================================================
// Notificações: só as solicitações PENDENTES DO PRÓPRIO promotor
// (antes mostrava solicitações de todos os promotores).
// ============================================================
$notificacoes = [];
if(isset($conexao) && isset($_SESSION['usuario_id'])){
    $id_promotor_notif = resolverPromotorId($conexao, (int) $_SESSION['usuario_id']);
    if($id_promotor_notif !== null){
        $sql_notif = "SELECT id, nome_evento FROM solicitacoes
                      WHERE id_promotor = ?
                        AND (status = 'pendente' OR status IS NULL OR status = '')
                      ORDER BY id DESC LIMIT 3";
        $stmt_notif = $conexao->prepare($sql_notif);
        if($stmt_notif){
            $stmt_notif->bind_param("i", $id_promotor_notif);
            $stmt_notif->execute();
            $result_notif = $stmt_notif->get_result();
            while($row = $result_notif->fetch_assoc()) {
                $notificacoes[] = $row;
            }
        }
    }
}
?>
<header class="topo-painel">
    <div class="titulo-pagina">
        <h1><?php echo htmlspecialchars($titulo_pagina); ?></h1>
    </div>

    <div class="acoes-topo">
        
        <!-- ADICIONADO: Wrapper envolvendo o botão de notificação e o menu -->
        <div class="sino-notificacoes" style="position: relative; display: flex; align-items: center;">
            <button class="botao-icone" id="notificationButton" aria-label="Notificações">
                <img src="<?php echo $base_url; ?>assets/notificacao.svg" alt="">
                <!-- Ponto vermelho só aparece se tiver notificação pendente -->
                <?php if(count($notificacoes) > 0): ?>
                    <span class="badge-notificacao"></span>
                <?php endif; ?>
            </button>

            <!-- ADICIONADO: Janela flutuante de Notificações -->
            <div class="menu-notificacoes" id="notificationMenu">
                <div class="cabecalho-notificacoes">
                    <h4>Solicitações Recentes</h4>
                </div>
                
                <div class="lista-notificacoes">
                    <?php if(count($notificacoes) > 0): ?>
                        <?php foreach($notificacoes as $notif): ?>
                            <a href="<?php echo $base_url; ?>Solicitacoes/solicitacoes_detalhes.php?id=<?php echo $notif['id']; ?>" class="item-notificacao">
                                <strong><?php echo htmlspecialchars($notif['nome_evento'] ?? 'Novo Pedido'); ?></strong>
                                <span>Nova solicitação pendente</span>
                            </a>
                        <?php endforeach; ?>
                        <a href="<?php echo $base_url; ?>Solicitacoes/solicitacoes.php" class="ver-todas-notificacoes">Ver todas</a>
                    <?php else: ?>
                        <p class="notificacao-vazia">Nenhuma solicitação pendente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="conta-perfil">
        <button class="botao-perfil" id="profileButton" aria-label="<?= htmlspecialchars("Menu de perfil de " . $nome_usuario) ?>">
            <span class="avatar-perfil <?php echo $tem_foto_topo ? "" : "avatar-vazio"; ?>">
                <img src="<?php echo htmlspecialchars($foto_perfil_topo); ?>" alt="">
            </span>
            <span class="texto-perfil">                        <strong><?php echo htmlspecialchars($nome_usuario); ?></strong>
                        <small><?php echo htmlspecialchars($rotulo_tipo_usuario); ?></small>
            </span>
            <img src="<?php echo $base_url; ?>assets/seta_baixo.svg" alt="" class="caret-perfil">
        </button>

        <div class="menu-perfil" id="profileMenu">
            <?php if (isset($conexao) && ehPromotor($conexao)): ?>
            <a href="<?php echo $base_url; ?>Perfil/perfil.php">Meu perfil</a>
            <a href="<?php echo $base_url; ?>Dashboard/dashboard.php">Dashboard</a>
            <?php endif; ?>
            <a href="<?php echo $base_url; ?>Includes/logout.php">Sair</a>
        </div>
        </div>
    </div>
</header>
<script src="<?php echo $base_url; ?>Includes/js/header.js"></script>
