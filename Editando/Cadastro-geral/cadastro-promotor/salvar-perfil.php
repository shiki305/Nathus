<?php
session_start();
include_once(__DIR__ . "/../../Includes/conexao.php");
include_once(__DIR__ . "/../../Includes/promotor.php");
include_once(__DIR__ . "/../../Includes/imagens.php");
include_once(__DIR__ . "/../../Includes/perfil_foto.php");

if(!isset($_SESSION["usuario_id"])){
    header("Location: ../../../Login/login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] != "POST"){
    header("Location: e7/e7.php");
    exit;
}

$descricao = $_POST["descricao"] ?? "";
if (!descricao_promotor_valida($descricao)) {
    header("Location: e7/e7.php?erro=descricao", true, 303);
    exit;
}
$frase = trim((string) ($_POST["frase_destaque"] ?? ""));
$frase = mb_substr($frase, 0, 150);
$foto_perfil = null;

if(isset($_FILES["foto_perfil"]) && $_FILES["foto_perfil"]["error"] == 0){
    $pasta_upload = __DIR__ . "/../../uploads/perfil-promotor/";
    $foto_perfil = otimizar_e_salvar_imagem($_FILES["foto_perfil"], $pasta_upload, "perfil", 600, 600, 82);
}

// O dono do perfil SEMPRE deriva da sessão (id do cliente logado).
$id_promotor = resolverPromotorId($conexao, (int) $_SESSION["usuario_id"]);
if($id_promotor === null){
    header("Location: ../../Login/login.php");
    exit;
}

// Ao trocar a foto, apaga a anterior do disco para não deixar órfãos.
if($foto_perfil != null){
    $stmtAntiga = $conexao->prepare("SELECT foto_perfil FROM promotor WHERE id = ? LIMIT 1");
    $stmtAntiga->bind_param("i", $id_promotor);
    $stmtAntiga->execute();
    $antiga = $stmtAntiga->get_result()->fetch_assoc();
    apagar_upload_antigo("perfil", $antiga["foto_perfil"] ?? null);
}

if($foto_perfil != null){
    $sql = "UPDATE promotor SET frase_destaque = ?, descricao = ?, foto_perfil = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssi", $frase, $descricao, $foto_perfil, $id_promotor);
}
else{
    $sql = "UPDATE promotor SET frase_destaque = ?, descricao = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ssi", $frase, $descricao, $id_promotor);
}

$stmt->execute();

limpar_cache_foto_perfil();
header("Location: e8/e8.php");
exit;
?>
