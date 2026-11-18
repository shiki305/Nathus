<?php
require_once __DIR__ . '/validar-contato.php';

$telefone_perfil = trim((string) $telefone_perfil);
if (!telefone_brasileiro_valido($telefone_perfil)) {
    return;
}

$numero = preg_replace('/\D/', '', $telefone_perfil);
$numero_local = strlen($numero) > 11 ? substr($numero, 2) : $numero;
$telefone_formatado = '(' . substr($numero_local, 0, 2) . ') '
    . substr($numero_local, 2, -4) . '-' . substr($numero_local, -4);
?>
<a class="meta-telefone" href="tel:+55<?= $numero_local ?>" aria-label="Ligar para <?= htmlspecialchars($telefone_formatado) ?>">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="#ffc107" aria-hidden="true">
        <path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.46.57 3.58a1 1 0 0 1-.25 1.01z"/>
    </svg>
    <?= htmlspecialchars($telefone_formatado) ?>
</a>
