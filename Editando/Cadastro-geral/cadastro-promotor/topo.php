<?php
/**
 * Cabeçalho comum a todas as etapas do cadastro (cadastro-promotor e páginas raiz).
 * Espera: $titulo (title da página), $css (arquivo css da etapa, relativo à página).
 */

// Cache-busting do css da etapa: resolve pelo diretório da página que incluiu este topo.
$css_v = @filemtime(dirname(get_included_files()[0]) . "/" . $css);

// Prefixo relativo até a raiz do projeto — o topo serve páginas em
// profundidades diferentes (cadastro-promotor/eN = 3 níveis, raiz = 2 níveis).
$prefixo = "";
$_p = dirname(get_included_files()[0]);
while (realpath($_p) !== dirname(dirname(__DIR__)) && $_p !== dirname($_p)) {
    $prefixo .= "../";
    $_p = dirname($_p);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <link rel="stylesheet" href="<?php echo $prefixo; ?>Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="<?php echo $prefixo; ?>Includes/css/cadastro-campos.css?v=<?php echo filemtime(__DIR__ . "/../../Includes/css/cadastro-campos.css"); ?>">
    <link rel="stylesheet" href="<?php echo $prefixo; ?>Includes/css/cadastro.css?v=<?php echo filemtime(__DIR__ . "/../../Includes/css/cadastro.css"); ?>">
    <link rel="stylesheet" href="<?php echo $css . ($css_v ? "?v=" . $css_v : ""); ?>">
    <link rel="stylesheet" href="<?= $prefixo ?>Includes/css/botao-voltar.css?v=<?= filemtime(__DIR__ . '/../../Includes/css/botao-voltar.css') ?>">
    <link rel="stylesheet" href="<?= $prefixo ?>Includes/css/painel-texto.css?v=<?= filemtime(__DIR__ . '/../../Includes/css/painel-texto.css') ?>">
</head>
<body>
    <main class="divisao">
        <section id="esquerda">
            <div class="fundo-escuro"></div>
            <?php include __DIR__ . '/../../Includes/painel-texto.php'; ?>
        </section>

        <section id="direita">

        <?php if (!empty($voltar)): ?>
        <a class="voltar botao-voltar" href="<?php echo $voltar; ?>">Voltar</a>
        <?php endif; ?>
