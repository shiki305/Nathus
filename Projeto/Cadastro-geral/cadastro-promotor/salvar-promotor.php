<?php
session_start();
include_once(__DIR__ . "/../../Includes/conexao.php");
include_once(__DIR__ . "/../../Includes/promotor.php");
include_once(__DIR__ . "/../../Includes/perfil_foto.php");
require_once __DIR__ . '/../../Includes/validar-contato.php';

if(!isset($_SESSION["cadastro"])){
    header("Location: e1/e1.php");
    exit;
}

$nome = trim($_SESSION["cadastro"]["nome"] ?? "");
$email = trim($_SESSION["cadastro"]["email"] ?? "");
$senha = $_SESSION["cadastro"]["senha"] ?? "";
$telefone = trim($_SESSION["cadastro"]["telefone"] ?? "");
$cpf = trim($_SESSION["cadastro"]["cpf"] ?? "");
$fantasia = trim($_SESSION["cadastro"]["fantasia"] ?? "");
$cnpj = trim($_SESSION["cadastro"]["cnpj"] ?? "");
$campo = $_SESSION["cadastro"]["campo"] ?? "";
if($campo === "" && isset($_SESSION["cadastro"]["campos"])){
    $tmp = $_SESSION["cadastro"]["campos"];
    $campo = is_array($tmp) ? (string) ($tmp[0] ?? "") : (string) $tmp;
}
$campos_validos = ["organizacao", "buffet", "local"];
if(!in_array($campo, $campos_validos, true)){
    header("Location: e2/e2.php");
    exit;
}
$plano = $_SESSION["cadastro"]["plano"] ?? "";
$metodo = $_SESSION["cadastro"]["metodo"] ?? "";

if($nome == "" || $email == "" || $senha == ""){
    header("Location: e1/e1.php");
    exit;
}

if (!telefone_brasileiro_valido($telefone) || !cpf_formato_valido($cpf)) {
    header("Location: e1/e1.php?erro=contato");
    exit;
}

// E-mail já cadastrado: não sobrescreve cliente existente nem transforma
// cliente em promotor (era a principal fonte de "confusão" entre contas).
$busca_email = $conexao->prepare("SELECT id FROM cliente WHERE email = ? LIMIT 1");
$busca_email->bind_param("s", $email);
$busca_email->execute();
if($busca_email->get_result()->fetch_assoc()){
    header("Location: e1/e1.php?erro=email");
    exit;
}

// usuario único a partir do e-mail (evita colidir só pelo nome).
$usuario_base = strtolower(preg_replace("/[^a-z0-9]/i", "", strstr($email, "@", true) ?: $nome));
if($usuario_base === ""){
    $usuario_base = "user";
}
$usuario = substr($usuario_base, 0, 24);
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

$sql_cliente = "INSERT INTO cliente(usuario, nome, senha, email, telefone, cpf, tipo_conta)
                VALUES (?, ?, ?, ?, ?, ?, 'promotor')";
$stmt_cliente = $conexao->prepare($sql_cliente);
$stmt_cliente->bind_param("ssssss", $usuario, $nome, $senha_hash, $email, $telefone, $cpf);

if(!$stmt_cliente->execute()){
    header("Location: e1/e1.php?erro=1");
    exit;
}

$id_cliente = (int) $stmt_cliente->insert_id;

$sql_promotor = "INSERT INTO promotor(id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano)
                 VALUES (?, ?, ?, ?, ?, 'ativo')";
$stmt_promotor = $conexao->prepare($sql_promotor);
$stmt_promotor->bind_param("issss", $id_cliente, $fantasia, $cnpj, $plano, $metodo);

if(!$stmt_promotor->execute()){
    // Rollback manual: remove o cliente órfão se o promotor falhar.
    $rollback = $conexao->prepare("DELETE FROM cliente WHERE id = ?");
    $rollback->bind_param("i", $id_cliente);
    $rollback->execute();
    header("Location: e1/e1.php?erro=1");
    exit;
}

$id_promotor = (int) $stmt_promotor->insert_id;

$sql_campo = "INSERT INTO promotor_campo(id_promotor, campo) VALUES (?, ?)";
$stmt_campo = $conexao->prepare($sql_campo);
$stmt_campo->bind_param("is", $id_promotor, $campo);
$stmt_campo->execute();

iniciarSessaoUsuario($id_cliente, $nome, "promotor", $id_promotor);
limpar_cache_foto_perfil();

header("Location: e6/e6.php");
exit;
?>
