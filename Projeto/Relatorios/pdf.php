<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");
include_once(__DIR__ . "/dados.php");
include_once(__DIR__ . "/PdfSimples.php");

$id_promotor = exigirPromotor($conexao);
garantir_colunas_solicitacoes($conexao);

$dados = relatorio_dados($conexao, $id_promotor, $_GET["ano"] ?? date("Y"));

$promotor = "";
$stmtNome = $conexao->prepare(
    "SELECT c.nome, p.nome_fantasia
     FROM promotor p
     INNER JOIN cliente c ON c.id = p.id_cliente
     WHERE p.id = ?
     LIMIT 1"
);
if ($stmtNome) {
    $stmtNome->bind_param("i", $id_promotor);
    $stmtNome->execute();
    $linhaNome = $stmtNome->get_result()->fetch_assoc() ?: [];
    $fantasia = trim((string) ($linhaNome["nome_fantasia"] ?? ""));
    $promotor = $fantasia !== "" ? $fantasia : trim((string) ($linhaNome["nome"] ?? ""));
}

$pdf = relatorio_gerar_pdf($dados, $promotor);
$arquivo = "relatorio-nathus-" . (int) $dados["ano"] . ".pdf";

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"" . $arquivo . "\"");
header("Content-Length: " . strlen($pdf));
header("Cache-Control: private, max-age=0, must-revalidate");
echo $pdf;
exit;
