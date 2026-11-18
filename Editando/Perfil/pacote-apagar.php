<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/imagens.php");

if(!isset($_SESSION["usuario_id"])){
    header("Location: ../Login/login.php");
    exit;
}

// Só aceita POST (o botão Excluir envia formulário próprio).
if($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST["id_pacote"])){
    header("Location: perfil.php");
    exit;
}

$id_pacote = (int)$_POST["id_pacote"];

// Busca o pacote garantindo que pertence ao promotor logado.
$stmt = $conexao->prepare(
    "SELECT id, imagem
       FROM promotor_pacote
      WHERE id = ?
        AND id_promotor = (SELECT id FROM promotor WHERE id_cliente = ? LIMIT 1)
      LIMIT 1"
);
$stmt->bind_param("ii", $id_pacote, $_SESSION["usuario_id"]);
$stmt->execute();
$pacote = $stmt->get_result()->fetch_assoc();

if($pacote){
    // 1. Apaga a imagem do disco (se existir).
    apagar_upload_antigo("pacotes", $pacote["imagem"]);

    // 2. Apaga o registro do banco.
    $stmtDel = $conexao->prepare("DELETE FROM promotor_pacote WHERE id = ?");
    $stmtDel->bind_param("i", $id_pacote);
    $stmtDel->execute();
}

header("Location: perfil.php?excluido=1");
exit;
