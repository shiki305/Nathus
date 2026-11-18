<?php if ($estado === "aceita"): ?>
    <?php $pode_concluir = festa_ja_aconteceu($data_festa, $horario); ?>
    <div class="sol-detalhe-acoes servico-conclusao">
        <?php if (!$pode_concluir): ?>
            <?php $fim = fim_servico($data_festa, $horario); ?>
            <p class="servico-aviso" id="servico-aviso">
                <?php if ($fim !== null): ?>
                    Você pode concluir o serviço após <?= $fim->format("d/m/Y") ?> às <?= $fim->format("H:i") ?>.
                <?php else: ?>
                    A conclusão fica disponível após o período do evento.
                <?php endif; ?>
            </p>
        <?php endif; ?>
        <button type="button" class="btn-responder btn-responder--aceitar" id="abrir-conclusao"
            <?= $pode_concluir ? "" : 'disabled aria-describedby="servico-aviso"' ?>>Concluir serviço</button>
    </div>

    <?php if ($pode_concluir): ?>
        <dialog class="servico-dialogo" id="confirmar-conclusao" aria-labelledby="titulo-conclusao">
            <h3 id="titulo-conclusao">Concluir serviço</h3>
            <p>Confirme que o serviço foi realizado.</p>
            <div class="servico-dialogo-acoes">
                <form method="dialog">
                    <button type="submit" class="btn-responder" autofocus>Voltar</button>
                </form>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $id_solicitacao ?>">
                    <button type="submit" class="btn-responder btn-responder--aceitar" name="concluir_servico" value="1">Confirmar conclusão</button>
                </form>
            </div>
        </dialog>
        <script>
            document.getElementById("abrir-conclusao").addEventListener("click", () => {
                document.getElementById("confirmar-conclusao").showModal();
            });
        </script>
    <?php endif; ?>
<?php endif; ?>
