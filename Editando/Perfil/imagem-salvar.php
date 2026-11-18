<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");
include_once("../Includes/imagens.php");
include_once("../Includes/perfil_foto.php");

if(!isset($_SESSION["usuario_id"])){
    header("Location: ../Login/login.php");
    exit;
}

// Só aceita envio via formulário (POST).
if($_SERVER["REQUEST_METHOD"] !== "POST"){
    header("Location: perfil.php");
    exit;
}

$tipo = $_POST["tipo"] ?? "";
$colunas_validas = [
    "banner" => "banner",
    "foto"   => "foto_perfil",
];

if(!isset($colunas_validas[$tipo]) || empty($_FILES["imagem"]["name"])){
    header("Location: perfil.php");
    exit;
}

$coluna = $colunas_validas[$tipo];
$pasta  = $tipo === "banner" ? "banner" : "perfil-promotor";

$id_promotor = exigirPromotor($conexao);

$stmtAntigo = $conexao->prepare("SELECT {$coluna} FROM promotor WHERE id = ?");
$stmtAntigo->bind_param("i", $id_promotor);
$stmtAntigo->execute();
$linha = $stmtAntigo->get_result()->fetch_assoc();
$arquivo_antigo = $linha[$coluna] ?? null;

if($tipo === "banner"){
    $dimensoes = [1600, 600, 80];
}
else{
    $dimensoes = [600, 600, 82];
}

$destino = __DIR__ . "/../uploads/" . $pasta . "/";
$nome_arquivo = otimizar_e_salvar_imagem($_FILES["imagem"], $destino, $tipo, $dimensoes[0], $dimensoes[1], $dimensoes[2]);

if($nome_arquivo){
    apagar_upload_antigo($pasta, $arquivo_antigo);

    $stmtUp = $conexao->prepare("UPDATE promotor SET {$coluna} = ? WHERE id = ?");
    $stmtUp->bind_param("si", $nome_arquivo, $id_promotor);
    $stmtUp->execute();
}

limpar_cache_foto_perfil();
header("Location: perfil.php?sucesso=1");
exit;
