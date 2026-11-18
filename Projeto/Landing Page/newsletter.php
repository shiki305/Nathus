<?php
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "mensagem" => "Método inválido."]);
    exit;
}

include_once __DIR__ . "/../Includes/conexao.php";

$email = strtolower(trim($_POST["email"] ?? ""));

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    http_response_code(422);
    echo json_encode(["ok" => false, "mensagem" => "Informe um e-mail válido."]);
    exit;
}

$conexao->query(
    "CREATE TABLE IF NOT EXISTS newsletter (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_newsletter_email (email)
    )"
);

$stmt = $conexao->prepare("INSERT INTO newsletter (email) VALUES (?)");
$stmt->bind_param("s", $email);

if ($stmt->execute()) {
    echo json_encode(["ok" => true, "mensagem" => "Pronto! Você vai receber as novidades da Nathus."]);
    exit;
}

if ($conexao->errno === 1062) {
    echo json_encode(["ok" => true, "mensagem" => "Este e-mail já está na nossa lista."]);
    exit;
}

http_response_code(500);
echo json_encode(["ok" => false, "mensagem" => "Não foi possível salvar agora."]);
