<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");
include_once("../Includes/imagens.php");

if(!isset($_SESSION["usuario_id"])){
    header("Location: ../Login/login.php");
    exit;
}

// Só aceita envio via formulário (POST).
if($_SERVER["REQUEST_METHOD"] !== "POST"){
    header("Location: perfil.php");
    exit;
}

if(empty($_FILES["imagem"]["name"])){
    header("Location: perfil.php");
    exit;
}

$id_promotor = exigirPromotor($conexao);
$id_foto = (int)($_POST["id_foto"] ?? 0);

$destino = __DIR__ . "/../uploads/galeria/";
$nome_arquivo = otimizar_e_salvar_imagem($_FILES["imagem"], $destino, "galeria", 1400, 1400, 80);

if($nome_arquivo){
    // Guarda também a imagem original, para que futuras edições recomecem da foto-base.
    if(!empty($_FILES["imagem_original"]["tmp_name"]) && is_uploaded_file($_FILES["imagem_original"]["tmp_name"])){
        $baseDestino = $destino . $nome_arquivo . ".base.jpg";
        @copy($_FILES["imagem_original"]["tmp_name"], $baseDestino);
    } elseif($id_foto > 0 && !empty($_FILES["imagem"]["tmp_name"])) {
        // Fallback: em instalações antigas sem original guardado, mantém a imagem recebida como base.
        $baseDestino = $destino . $nome_arquivo . ".base.jpg";
        @copy($_FILES["imagem"]["tmp_name"], $baseDestino);
    }
    if($id_foto > 0){
        // Só permite editar uma foto pertencente ao promotor logado.
        $stmtAntigo = $conexao->prepare("SELECT arquivo FROM promotor_galeria WHERE id = ? AND id_promotor = ?");
        $stmtAntigo->bind_param("ii", $id_foto, $id_promotor);
        $stmtAntigo->execute();
        $fotoAntiga = $stmtAntigo->get_result()->fetch_assoc();
        if($fotoAntiga){
            $stmtUp = $conexao->prepare("UPDATE promotor_galeria SET arquivo = ? WHERE id = ? AND id_promotor = ?");
            $stmtUp->bind_param("sii", $nome_arquivo, $id_foto, $id_promotor);
            $stmtUp->execute();
            apagar_upload_antigo("galeria", $fotoAntiga["arquivo"] ?? null);
            $baseAntiga = $destino . ($fotoAntiga["arquivo"] ?? "") . ".base.jpg";
            if(is_file($baseAntiga)) @unlink($baseAntiga);
        } else {
            apagar_upload_antigo("galeria", $nome_arquivo);
        }
    } else {
        $stmtIn = $conexao->prepare("INSERT INTO promotor_galeria (id_promotor, arquivo) VALUES (?, ?)");
        $stmtIn->bind_param("is", $id_promotor, $nome_arquivo);
        $stmtIn->execute();
    }
}

header("Location: perfil.php?sucesso=1");
exit;
