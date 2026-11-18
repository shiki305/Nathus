<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");
include_once("../Includes/imagens.php");
require_once __DIR__ . "/../Includes/endereco-espaco.php";
require_once __DIR__ . "/../Includes/moeda.php";

// Só aceita envio via formulário (POST).
if($_SERVER["REQUEST_METHOD"] !== "POST"){
    header("Location: perfil.php");
    exit;
}

$id_promotor = exigirPromotor($conexao);

$id_pacote = (int)($_POST["id_pacote"] ?? 0);
if($id_pacote <= 0 && promotor_eh_espaco($conexao, $id_promotor)){
    $stmtEndereco = $conexao->prepare("SELECT espaco_logradouro, espaco_numero, espaco_bairro,
        espaco_complemento, cidade, estado, espaco_area_m2 FROM promotor WHERE id = ?");
    $stmtEndereco->bind_param("i", $id_promotor);
    $stmtEndereco->execute();
    $perfil = $stmtEndereco->get_result()->fetch_assoc() ?? [];
    if(endereco_espaco_formatado($perfil) === null || area_espaco_formatada($perfil["espaco_area_m2"] ?? null) === ""){
        header("Location: perfil.php?erro=pacote_endereco#pacotes-servicos");
        exit;
    }
}
$titulo    = trim($_POST["titulo"] ?? "");
$itens     = trim($_POST["itens"] ?? "");
$preco_digitado = $_POST["preco"] ?? "";
$preco = moeda_br_decimal($preco_digitado);

// Mesmos limites da validação do front-end (defesa extra no servidor).
if($titulo === ""){
    header("Location: perfil.php?erro=pacote_titulo");
    exit;
}
if(mb_strlen($titulo) > 80){   $titulo = mb_substr($titulo, 0, 80); }
if(mb_strlen($itens)  > 1000){ $itens  = mb_substr($itens, 0, 1000); }

if($preco === null && (!is_string($preco_digitado) || trim($preco_digitado) !== "")){
    $erroPreco = moeda_br_excede_limite($preco_digitado) ? 'preco_acima_limite' : 'preco_invalido';
    header("Location: perfil.php?erro=" . $erroPreco . "#pacotes-servicos");
    exit;
}
$preco ??= "";

$nome_arquivo = null;
if(!empty($_FILES["imagem"]["name"])){
    $destino = __DIR__ . "/../uploads/pacotes/";
    $nome_arquivo = otimizar_e_salvar_imagem($_FILES["imagem"], $destino, "pacote", 900, 900, 80);

    if($nome_arquivo && $id_pacote > 0){
        $stmtImgAntiga = $conexao->prepare("SELECT imagem FROM promotor_pacote WHERE id = ? AND id_promotor = ?");
        $stmtImgAntiga->bind_param("ii", $id_pacote, $id_promotor);
        $stmtImgAntiga->execute();
        $linhaImg = $stmtImgAntiga->get_result()->fetch_assoc();
        apagar_upload_antigo("pacotes", $linhaImg["imagem"] ?? null);
    }
}

if($id_pacote > 0){
    if($nome_arquivo){
        $sql = "UPDATE promotor_pacote SET titulo=?, itens=?, preco=?, imagem=? WHERE id=? AND id_promotor=?";
        $stmtUp = $conexao->prepare($sql);
        $stmtUp->bind_param("ssssii", $titulo, $itens, $preco, $nome_arquivo, $id_pacote, $id_promotor);
    } else {
        $sql = "UPDATE promotor_pacote SET titulo=?, itens=?, preco=? WHERE id=? AND id_promotor=?";
        $stmtUp = $conexao->prepare($sql);
        $stmtUp->bind_param("sssii", $titulo, $itens, $preco, $id_pacote, $id_promotor);
    }
    $stmtUp->execute();
} else {
    $sql = "INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtIn = $conexao->prepare($sql);
    // Novo pacote nasce no fim da lista (última ordem + 1).
    $stmtMax = $conexao->prepare("SELECT COALESCE(MAX(ordem), 0) + 1 FROM promotor_pacote WHERE id_promotor = ?");
    $stmtMax->bind_param("i", $id_promotor);
    $stmtMax->execute();
    $proxima_ordem = (int)$stmtMax->get_result()->fetch_row()[0];
    $stmtIn->bind_param("issssi", $id_promotor, $titulo, $nome_arquivo, $itens, $preco, $proxima_ordem);
    $stmtIn->execute();
}

header("Location: perfil.php?sucesso=1");
exit;
