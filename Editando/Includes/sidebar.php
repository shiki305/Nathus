<?php

$base_url = $base_url ?? "../";

$pagina_atual = $pagina_atual ?? "";

$itens_menu = [
    ["chave" => "inicio",       "label" => "Início",           "link" => "home/homeAnt.php",               "icone" => "M3 11 12 4l9 7M5 10v10h14V10M9 20v-6h6v6"],
    ["chave" => "dashboard",    "label" => "Dashboard",        "link" => "Dashboard/dashboard.php",       "icone" => "M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"],
    ["chave" => "perfil",       "label" => "Meu Perfil",       "link" => "Perfil/perfil.php",             "icone" => "M16 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21a8 8 0 0 1 16 0"],
    ["chave" => "solicitacoes", "label" => "Solicitações",     "link" => "Solicitacoes/solicitacoes.php", "icone" => "M15 21H5V6h14v7M8 3v5m8-5v5M5 10h14M8 14h4m-4 3h3m10 0a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm-4-2v2l1 1"],
    ["chave" => "servicos",     "label" => "Meus Serviços",    "link" => "Servicos/servicos.php",         "icone" => "M6 3h8l4 4v14H6zM14 3v5h4M9 12h6m-6 4h6"],
    ["chave" => "assinatura",   "label" => "Minha Assinatura", "link" => "Assinatura/assinatura.php",     "icone" => "M3 6h18v12H3zM3 10h18M7 14h4"],
    ["chave" => "relatorios",   "label" => "Relatórios",       "link" => "Relatorios/relatorios.php",     "icone" => "M3 20h18M7 20V11m5 9V4m5 16v-7"],
];

$solicitacoes_pendentes_sidebar = 0;
if (isset($conexao, $_SESSION["usuario_id"])) {
    require_once __DIR__ . "/promotor.php";
    $id_promotor_sidebar = obterPromotorExistente($conexao, (int) $_SESSION["usuario_id"]);
    if ($id_promotor_sidebar !== null) {
        garantir_colunas_solicitacoes($conexao);
        $stmt_sidebar = $conexao->prepare(
            "SELECT COUNT(*) AS total FROM solicitacoes
             WHERE id_promotor = ? AND (status = 'pendente' OR status IS NULL OR status = '')"
        );
        $stmt_sidebar->bind_param("i", $id_promotor_sidebar);
        $stmt_sidebar->execute();
        $solicitacoes_pendentes_sidebar = (int) $stmt_sidebar->get_result()->fetch_assoc()["total"];
        $stmt_sidebar->close();
    }
}
?>
<aside class="barra-lateral">
    <div class="logo-lateral">
        <img src="<?= htmlspecialchars($base_url) ?>assets/Logo.png" alt="Nathus">
    </div>

    <nav class="navegacao-lateral" aria-label="Ações do painel">
        <span class="rotulo-lateral" aria-hidden="true">Ações</span>
        <?php foreach ($itens_menu as $item): ?>
            <?php
            $ativo = $pagina_atual === $item["chave"];
            $mostrar_selo = $item["chave"] === "solicitacoes" && $solicitacoes_pendentes_sidebar > 0;
            $rotulo_item = $item["label"] . ($mostrar_selo ? ", $solicitacoes_pendentes_sidebar " . ($solicitacoes_pendentes_sidebar === 1 ? "pendente" : "pendentes") : "");
            ?>
            <a href="<?= htmlspecialchars($base_url . $item["link"]) ?>" class="item-navegacao<?= $ativo ? ' ativo' : '' ?>"
                aria-label="<?= htmlspecialchars($rotulo_item) ?>"<?= $ativo ? ' aria-current="page"' : '' ?>>
                <span class="icone-navegacao">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= $item["icone"] ?>"/></svg>
                </span>
                <span class="texto-navegacao"><?= htmlspecialchars($item["label"]) ?></span>
                <?php if ($mostrar_selo): ?>
                    <span class="selo-lateral" aria-hidden="true"><?= $solicitacoes_pendentes_sidebar ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <a href="<?= htmlspecialchars($base_url) ?>Includes/logout.php" class="sair item-navegacao" aria-label="Sair">
        <span class="icone-navegacao">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 4h6a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-6M4 12h12m-5-5 5 5-5 5"/></svg>
        </span>
        <span class="texto-navegacao">Sair</span>
    </a>
</aside>
