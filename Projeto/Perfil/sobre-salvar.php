<?php
session_start();
require_once __DIR__ . "/../Includes/conexao.php";
require_once __DIR__ . "/../Includes/promotor.php";
require_once __DIR__ . "/../Includes/tipos-festa.php";

$id_promotor = exigirPromotor($conexao);
$token = $_POST["csrf_sobre"] ?? "";
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !is_string($token)
    || empty($_SESSION["csrf_sobre"]) || !hash_equals($_SESSION["csrf_sobre"], $token)) {
    header("Location: perfil.php?erro=sobre_invalido");
    exit;
}

$frase = $_POST["frase_destaque"] ?? "";
$descricao = $_POST["descricao"] ?? "";
if (!is_string($frase)) {
    header("Location: perfil.php?erro=sobre_invalido");
    exit;
}
if (!descricao_promotor_valida($descricao)) {
    header("Location: perfil.php?erro=descricao_invalida", true, 303);
    exit;
}
$frase = mb_substr(trim($frase), 0, 150);
$descricao = trim($descricao);

$conexao->begin_transaction();
$atualizar = $conexao->prepare("UPDATE promotor SET frase_destaque = ?, descricao = ? WHERE id = ?");
$atualizar->bind_param("ssi", $frase, $descricao, $id_promotor);
$atualizar->execute();

$busca = $conexao->prepare("SELECT campo FROM promotor_campo WHERE id_promotor = ? ORDER BY id");
$busca->bind_param("i", $id_promotor);
$busca->execute();
$campos = array_column($busca->get_result()->fetch_all(MYSQLI_ASSOC), "campo");
$tipos = !in_array("local", $campos, true) && ($campos[0] ?? "") === "buffet"
    ? ["massas", "infantil", "drinks", "pratos", "geral"]
    : array_keys(TIPOS_FESTA);
$marcadas = array_filter((array) ($_POST["especialidades"] ?? []), "is_string");

$marcadores = implode(",", array_fill(0, count($tipos), "?"));
$apagar = $conexao->prepare("DELETE FROM promotor_especialidade WHERE id_promotor = ? AND tipo IN ($marcadores)");
$apagar->bind_param("i" . str_repeat("s", count($tipos)), $id_promotor, ...$tipos);
$apagar->execute();

$inserir = $conexao->prepare("INSERT INTO promotor_especialidade (id_promotor, tipo) VALUES (?, ?)");
foreach (array_intersect($tipos, $marcadas) as $tipo) {
    $inserir->bind_param("is", $id_promotor, $tipo);
    $inserir->execute();
}
$conexao->commit();

header("Location: perfil.php?sucesso=1#sobre-mim");
exit;
