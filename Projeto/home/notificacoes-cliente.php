<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit;
}

require_once __DIR__ . '/../Includes/conexao.php';
require_once __DIR__ . '/../Includes/promotor.php';
require_once __DIR__ . '/../Includes/notificacoes-cliente.php';

$id_cliente = (int) $_SESSION['usuario_id'];
if (ehPromotor($conexao, $id_cliente)) {
    http_response_code(403);
    exit;
}
garantir_colunas_solicitacoes($conexao);
session_write_close();

echo json_encode(notificacoes_cliente($conexao, $id_cliente), JSON_INVALID_UTF8_SUBSTITUTE);
