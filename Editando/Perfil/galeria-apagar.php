<?php
session_start();
include_once("../Includes/conexao.php");

if(!isset($_SESSION["usuario_id"])){
    header("Location: ../Login/login.php");
    exit;
}

// Só aceita POST (o botão de apagar envia formulário próprio).
if($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST["id_foto"])){
    header("Location: perfil.php");
    exit;
}

$id_foto = (int)$_POST["id_foto"];

// Busca a foto garantindo que pertence ao promotor logado.
$stmt = $conexao->prepare(
    "SELECT g.arquivo
       FROM promotor_galeria g
       INNER JOIN promotor p ON p.id = g.id_promotor
      WHERE g.id = ? AND p.id_cliente = ?
      LIMIT 1"
);
$stmt->bind_param("ii", $id_foto, $_SESSION["usuario_id"]);
$stmt->execute();
$foto = $stmt->get_result()->fetch_assoc();

if($foto){
    // 1. Apaga o arquivo do disco (se ainda existir).
    $caminho = __DIR__ . "/../uploads/galeria/" . $foto["arquivo"];
    if(is_file($caminho)){
        unlink($caminho);
    }

    // 2. Apaga o registro do banco.
    $stmtDel = $conexao->prepare("DELETE FROM promotor_galeria WHERE id = ?");
    $stmtDel->bind_param("i", $id_foto);
    $stmtDel->execute();
}

header("Location: perfil.php?apagado=1");
exit;
