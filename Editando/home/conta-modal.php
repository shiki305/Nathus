<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    return;
}

include_once __DIR__ . '/../Includes/conexao.php';
include_once __DIR__ . '/../Includes/promotor.php';

$id_cliente = (int) $_SESSION['usuario_id'];
if (ehPromotor($conexao, $id_cliente)) {
    return;
}

$stmt = $conexao->prepare('SELECT nome, email, telefone, cpf, foto FROM cliente WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id_cliente);
$stmt->execute();
$conta = $stmt->get_result()->fetch_assoc();
if (!$conta) {
    return;
}

if (!isset($vt_voltar)) {
    $vt_voltar = (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'home.php') ? 'home.php' : 'homeAnt.php';
}
$vt_acao = $vt_acao ?? 'salvar-conta.php';
$conta_asset = $vt_asset ?? '../';
$avatar_padrao = $conta_asset . 'assets/user_dashboard.svg';
$foto_cliente = midia_publica($conta['foto'] ?? '', ['perfil-cliente'], $conta_asset);
$vt_campo = $campo ?? 'organizadores';
if (!in_array($vt_campo, ['organizadores', 'buffet', 'espaco'], true)) {
    $vt_campo = 'organizadores';
}

$aviso = '';
$erro = '';
if (($_GET['ok'] ?? '') === '1') {
    $aviso = 'Perfil atualizado.';
} elseif (($_GET['erro'] ?? '') === 'email') {
    $erro = 'Este e-mail já está em outra conta.';
} elseif (($_GET['erro'] ?? '') === 'senha') {
    $erro = 'A nova senha e a confirmação precisam ser iguais.';
} elseif (($_GET['erro'] ?? '') === 'dados') {
    $erro = 'Confira nome, e-mail, telefone e CPF antes de salvar.';
} elseif (($_GET['erro'] ?? '') === 'foto') {
    $erro = 'Não foi possível enviar a foto. Escolha uma imagem JPG, PNG ou WebP e tente novamente.';
} elseif (($_GET['erro'] ?? '') === 'salvar') {
    $erro = 'Não foi possível salvar o perfil. Tente novamente.';
}
$abrir = (($_GET['editar'] ?? '') === '1') || $aviso !== '' || $erro !== '';
?>
<dialog class="vt-modal" id="vt-conta" aria-labelledby="vt-conta-titulo">
    <div class="vt-modal-caixa">
        <header class="vt-modal-topo">
            <h2 id="vt-conta-titulo">Editar perfil</h2>
            <button class="vt-modal-fechar" type="button" data-fechar-conta aria-label="Fechar">
                <img src="<?= htmlspecialchars($conta_asset . 'assets/fechar.svg') ?>" alt="">
            </button>
        </header>

        <?php if ($aviso !== ''): ?>
            <p class="vt-modal-aviso"><?= htmlspecialchars($aviso) ?></p>
        <?php endif; ?>
        <?php if ($erro !== ''): ?>
            <p class="vt-modal-erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form class="vt-modal-form" method="post" action="<?= htmlspecialchars($vt_acao) ?>" enctype="multipart/form-data">
            <input type="hidden" name="voltar" value="<?= htmlspecialchars($vt_voltar) ?>">
            <input type="hidden" name="campo" value="<?= htmlspecialchars($vt_campo) ?>">

            <div class="vt-modal-foto">
                <button class="vt-foto-conta<?= $foto_cliente === '' ? ' vt-foto-conta--vazia' : '' ?>" id="conta-foto-botao"
                        type="button" aria-label="<?= $foto_cliente === '' ? 'Adicionar foto' : 'Trocar foto' ?>">
                    <img id="conta-foto-preview" src="<?= htmlspecialchars($foto_cliente ?: $avatar_padrao) ?>"
                         data-foto="<?= htmlspecialchars($foto_cliente) ?>" data-padrao="<?= htmlspecialchars($avatar_padrao) ?>" alt="">
                    <span class="vt-foto-conta-overlay" aria-hidden="true">
                        <img src="<?= htmlspecialchars($conta_asset . 'assets/camera-avatar.svg') ?>" alt="">
                    </span>
                </button>
                <div class="vt-modal-foto-acoes">
                    <span>Foto do perfil</span>
                    <button class="vt-foto-remover" id="conta-foto-remover" type="button"<?= $foto_cliente === '' ? ' hidden' : '' ?>>Remover foto</button>
                    <small id="conta-foto-aviso" role="status"></small>
                </div>
                <input id="conta-foto" name="foto" type="file" accept="image/png, image/jpeg, image/webp" hidden>
                <input id="conta-remover-foto" name="remover_foto" type="hidden" value="">
            </div>

            <label for="conta-nome">Nome completo</label>
            <input id="conta-nome" name="nome" type="text" required maxlength="80" value="<?= htmlspecialchars($conta['nome'] ?? '') ?>">

            <label for="conta-email">E-mail</label>
            <input id="conta-email" name="email" type="email" required maxlength="120" value="<?= htmlspecialchars($conta['email'] ?? '') ?>">

            <div class="vt-modal-linha">
                <div>
                    <label for="conta-telefone">Telefone</label>
                    <input id="conta-telefone" name="telefone" type="tel" data-mascara="telefone" maxlength="19" autocomplete="tel" required placeholder="(XX) XXXXX-XXXX" value="<?= htmlspecialchars($conta['telefone'] ?? '') ?>">
                </div>
                <div>
                    <label for="conta-cpf">CPF</label>
                    <input id="conta-cpf" name="cpf" type="text" data-mascara="cpf" maxlength="14" inputmode="numeric" placeholder="XXX.XXX.XXX-XX" value="<?= htmlspecialchars($conta['cpf'] ?? '') ?>">
                </div>
            </div>

            <label for="conta-senha">Nova senha</label>
            <input id="conta-senha" name="senha" type="password" autocomplete="new-password" placeholder="Deixe em branco para manter">

            <label for="conta-senha2">Confirmar nova senha</label>
            <input id="conta-senha2" name="senha2" type="password" autocomplete="new-password" placeholder="Repita a nova senha">

            <button type="submit">Salvar alterações</button>
        </form>
    </div>
</dialog>
<?php require_once __DIR__ . '/../Includes/mascaras.php'; ?>
<script>
(function () {
    var modal = document.getElementById('vt-conta');
    if (!modal || typeof modal.showModal !== 'function') return;

    function abrir() { if (!modal.open) modal.showModal(); }
    function fechar() { if (modal.open) modal.close(); }

    document.querySelectorAll('[data-abrir-conta]').forEach(function (botao) {
        botao.addEventListener('click', abrir);
    });
    modal.querySelectorAll('[data-fechar-conta]').forEach(function (botao) {
        botao.addEventListener('click', fechar);
    });
    modal.addEventListener('click', function (evento) {
        if (evento.target === modal) fechar();
    });
    var inputFoto = document.getElementById('conta-foto');
    var previewFoto = document.getElementById('conta-foto-preview');
    if (inputFoto) {
        var botaoFoto = document.getElementById('conta-foto-botao');
        var botaoRemover = document.getElementById('conta-foto-remover');
        var removerFoto = document.getElementById('conta-remover-foto');
        var avisoFoto = document.getElementById('conta-foto-aviso');
        var fotoSalva = previewFoto.dataset.foto;
        var urlTemporaria = '';

        function mostrarFoto(url) {
            previewFoto.src = url || previewFoto.dataset.padrao;
            botaoFoto.classList.toggle('vt-foto-conta--vazia', !url);
            botaoFoto.setAttribute('aria-label', url ? 'Trocar foto' : 'Adicionar foto');
            botaoRemover.hidden = !url;
        }

        function limparPreview() {
            if (urlTemporaria) URL.revokeObjectURL(urlTemporaria);
            urlTemporaria = '';
        }

        botaoFoto.addEventListener('click', function () { inputFoto.click(); });
        inputFoto.addEventListener('change', function () {
            var arquivo = inputFoto.files && inputFoto.files[0];
            if (!arquivo) return;
            limparPreview();
            urlTemporaria = URL.createObjectURL(arquivo);
            removerFoto.value = '';
            avisoFoto.textContent = '';
            mostrarFoto(urlTemporaria);
        });
        botaoRemover.addEventListener('click', function () {
            limparPreview();
            inputFoto.value = '';
            removerFoto.value = fotoSalva ? '1' : '';
            avisoFoto.textContent = fotoSalva ? 'A foto será removida ao salvar.' : '';
            mostrarFoto('');
            botaoFoto.focus();
        });
        modal.addEventListener('close', function () {
            limparPreview();
            inputFoto.value = '';
            removerFoto.value = '';
            avisoFoto.textContent = '';
            mostrarFoto(fotoSalva);
        });
    }
    <?php if ($abrir): ?>
    abrir();
    <?php endif; ?>
})();
</script>
