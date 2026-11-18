<?php
$itens_pacote = array_values(array_filter(
    array_map('trim', preg_split('/\R/', (string) ($pacote['itens'] ?? '')) ?: []),
    static fn($item) => $item !== ''
));
?>
<div class="pacote-itens">
    <?php if ($itens_pacote): ?>
        <ul class="pacote-itens-lista" tabindex="0" aria-label="Itens do pacote">
            <?php foreach ($itens_pacote as $item): ?>
                <li><?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>