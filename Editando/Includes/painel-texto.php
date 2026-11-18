<?php
/**
 * Bloco de apresentação do login/cadastro (título, frase e ação).
 * O botão só é renderizado quando $painel_acao é informado:
 * - login: leva ao cadastro ("Criar conta");
 * - escolha do tipo de usuário (antes do fluxo): leva ao login ("Já tenho conta");
 * - etapas do cadastro: sem botão, apenas o texto.
 */
$painel_acao = $painel_acao ?? '';
$painel_rotulo = $painel_rotulo ?? '';
?>
<div class="painel-texto">
    <h1>Seu evento dos sonhos</h1>
    <p>em um só lugar com a Nathus</p>
    <?php if ($painel_acao !== '' && $painel_rotulo !== ''): ?>
        <a class="painel-texto-acao" href="<?= htmlspecialchars($painel_acao, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($painel_rotulo, ENT_QUOTES, 'UTF-8') ?></a>
    <?php endif; ?>
</div>