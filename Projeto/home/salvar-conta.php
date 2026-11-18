<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Login/login.php');
    exit;
}

include_once __DIR__ . '/../Includes/conexao.php';
include_once __DIR__ . '/../Includes/promotor.php';

$id_cliente = (int) $_SESSION['usuario_id'];
if (ehPromotor($conexao, $id_cliente)) {
    header('Location: ../Perfil/perfil.php');
    exit;
}

$voltar = (string) ($_POST['voltar'] ?? '');
$campo = $_POST['campo'] ?? 'organizadores';
if (!in_array($campo, ['organizadores', 'buffet', 'espaco'], true)) {
    $campo = 'organizadores';
}
if ($voltar === 'home.php') {
    $destino = 'home.php?campo=' . rawurlencode($campo) . '&';
} elseif (preg_match('#^\.\./Cliente/perfil\.php\?id=(\d+)#', $voltar, $volta_perfil)) {
    $destino = '../Cliente/perfil.php?id=' . $volta_perfil[1] . '&ver_como_cliente=1&';
} elseif (preg_match('#^\.\./Cliente/solicitacao\.php\?pacote=(\d+)&id=(\d+)#', $voltar, $volta_solicitacao)) {
    $destino = '../Cliente/solicitacao.php?pacote=' . $volta_solicitacao[1] . '&id=' . $volta_solicitacao[2] . '&';
} else {
    $destino = 'homeAnt.php?';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $destino . 'editar=1');
    exit;
}

include_once __DIR__ . '/../Includes/imagens.php';
include_once __DIR__ . '/../Includes/perfil_foto.php';
require_once __DIR__ . '/../Includes/validar-contato.php';

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$cpf = trim($_POST['cpf'] ?? '');
$senha = (string) ($_POST['senha'] ?? '');
$senha2 = (string) ($_POST['senha2'] ?? '');

$dados_ok = $nome !== ''
    && mb_strlen($nome) >= 3
    && filter_var($email, FILTER_VALIDATE_EMAIL)
    && telefone_brasileiro_valido($telefone)
    && ($cpf === '' || cpf_formato_valido($cpf));

if (!$dados_ok) {
    header('Location: ' . $destino . 'editar=1&erro=dados');
    exit;
}

if ($senha !== '' || $senha2 !== '') {
    if ($senha !== $senha2) {
        header('Location: ' . $destino . 'editar=1&erro=senha');
        exit;
    }
}

$busca = $conexao->prepare('SELECT id FROM cliente WHERE email = ? AND id <> ? LIMIT 1');
$busca->bind_param('si', $email, $id_cliente);
$busca->execute();
if ($busca->get_result()->fetch_assoc()) {
    header('Location: ' . $destino . 'editar=1&erro=email');
    exit;
}

if (mb_strlen($nome) > 80) {
    $nome = mb_substr($nome, 0, 80);
}
if (mb_strlen($telefone) > 20) {
    $telefone = mb_substr($telefone, 0, 20);
}
if (mb_strlen($cpf) > 14) {
    $cpf = mb_substr($cpf, 0, 14);
}

if ($senha !== '') {
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conexao->prepare('UPDATE cliente SET nome = ?, email = ?, telefone = ?, cpf = ?, senha = ? WHERE id = ?');
    $stmt->bind_param('sssssi', $nome, $email, $telefone, $cpf, $senha_hash, $id_cliente);
} else {
    $stmt = $conexao->prepare('UPDATE cliente SET nome = ?, email = ?, telefone = ?, cpf = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $nome, $email, $telefone, $cpf, $id_cliente);
}

$nova_foto = null;
$erro_upload = $_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE;
$alterar_foto = ($_POST['remover_foto'] ?? '') === '1';
if ($erro_upload !== UPLOAD_ERR_NO_FILE) {
    if ($erro_upload === UPLOAD_ERR_OK) {
        $nova_foto = otimizar_e_salvar_imagem($_FILES['foto'], __DIR__ . '/../uploads/perfil-cliente/', 'cliente', 600, 600, 82);
    }
    if (!$nova_foto) {
        header('Location: ' . $destino . 'editar=1&erro=foto');
        exit;
    }
    $alterar_foto = true;
}

$foto_antiga = null;
$conexao->begin_transaction();
try {
    $stmt->execute();
    if ($alterar_foto) {
        $stmtFoto = $conexao->prepare('SELECT foto FROM cliente WHERE id = ? FOR UPDATE');
        $stmtFoto->bind_param('i', $id_cliente);
        $stmtFoto->execute();
        $foto_antiga = $stmtFoto->get_result()->fetch_assoc()['foto'] ?? null;
        $stmtUpFoto = $conexao->prepare('UPDATE cliente SET foto = ? WHERE id = ?');
        $stmtUpFoto->bind_param('si', $nova_foto, $id_cliente);
        $stmtUpFoto->execute();
    }
    $conexao->commit();
} catch (mysqli_sql_exception $e) {
    $conexao->rollback();
    apagar_upload_antigo('perfil-cliente', $nova_foto);
    header('Location: ' . $destino . 'editar=1&erro=salvar');
    exit;
}

$_SESSION['usuario_nome'] = $nome;
$_SESSION['usuario_email'] = $email;
if ($alterar_foto) {
    limpar_cache_foto_perfil();
    apagar_upload_antigo('perfil-cliente', $foto_antiga);
}

header('Location: ' . $destino . 'editar=1&ok=1');
exit;
