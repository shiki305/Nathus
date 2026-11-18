<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");
include_once("../Includes/imagens.php");
include_once("../Includes/perfil_foto.php");
require_once __DIR__ . '/../Includes/validar-contato.php';
require_once __DIR__ . '/../Includes/endereco-espaco.php';

if($_SERVER["REQUEST_METHOD"] != "POST"){
    header("Location: perfil.php");
    exit;
}

$id_promotor = exigirPromotor($conexao);
$id_cliente = (int) $_SESSION["usuario_id"];

$nome = trim($_POST["nome"] ?? "");
$telefone = trim($_POST["telefone"] ?? "");

// Mesmas regras da validação do front-end (defesa extra no servidor).
if($nome === "" || mb_strlen($nome) < 3 || ($telefone !== "" && !telefone_brasileiro_valido($telefone))){
    header("Location: perfil.php?erro=dados_perfil");
    exit;
}

if(mb_strlen($nome) > 45){ $nome = mb_substr($nome, 0, 45); }
if(mb_strlen($telefone) > 30){ $telefone = mb_substr($telefone, 0, 30); }

$nome_fantasia = trim($_POST["nome_fantasia"] ?? "");

if(mb_strlen($nome_fantasia) > 80){   $nome_fantasia = mb_substr($nome_fantasia, 0, 80); }

$cidade = trim($_POST["cidade"] ?? "");
$estado = strtoupper(trim($_POST["estado"] ?? ""));

if(mb_strlen($cidade) > 60){ $cidade = mb_substr($cidade, 0, 60); }
if(mb_strlen($estado) > 2){  $estado = mb_substr($estado, 0, 2); }

$cidade = $cidade !== "" ? $cidade : null;
$estado = $estado !== "" ? $estado : null;

$campos_validos = ["organizacao", "buffet", "local"];
$campos_enviados = array_filter((array) ($_POST["campos"] ?? []), "is_string");
$campos_selecionados = array_slice(array_intersect($campos_enviados, $campos_validos), 0, 1);
$endereco = [];
$atualizar_area = false;
$area = null;
if(in_array("local", $campos_selecionados, true)){
    $atualizar_area = array_key_exists("espaco_area_m2", $_POST);
    $area_digitada = $_POST["espaco_area_m2"] ?? "";
    if (is_string($area_digitada)) {
        $area_digitada = trim($area_digitada);
    }
    if ($area_digitada !== "") {
        $area = is_string($area_digitada) ? area_espaco_decimal($area_digitada) : null;
        if ($area === null) {
            header("Location: perfil.php?erro=area_invalida");
            exit;
        }
    }
    // Local de festas precisa informar a metragem do espaço.
    if ($area === null) {
        header("Location: perfil.php?erro=area_obrigatoria", true, 303);
        exit;
    }
    foreach(CAMPOS_ENDERECO_ESPACO as $campo => [, $limite]){
        $valor = $_POST[$campo] ?? "";
        if(!is_string($valor) || mb_strlen(trim($valor)) > $limite){
            header("Location: perfil.php?erro=endereco_invalido");
            exit;
        }
        $valor = trim($valor);
        $endereco[$campo] = $valor === "" ? null : $valor;
    }
    if(endereco_espaco_formatado($endereco + ["cidade" => $cidade, "estado" => $estado]) === null){
        header("Location: perfil.php?erro=endereco_invalido");
        exit;
    }
}

$conexao->begin_transaction();
$stmtCliente = $conexao->prepare("UPDATE cliente SET nome = ?, telefone = ? WHERE id = ?");
$stmtCliente->bind_param("ssi", $nome, $telefone, $id_cliente);
$stmtCliente->execute();

$stmtPromotor = $conexao->prepare("UPDATE promotor SET nome_fantasia = ?, cidade = ?, estado = ? WHERE id = ?");
$stmtPromotor->bind_param("sssi", $nome_fantasia, $cidade, $estado, $id_promotor);
$stmtPromotor->execute();

if($endereco){
    $stmtEndereco = $conexao->prepare("UPDATE promotor SET espaco_logradouro = ?, espaco_numero = ?,
        espaco_bairro = ?, espaco_complemento = ? WHERE id = ?");
    $stmtEndereco->bind_param("ssssi", $endereco["espaco_logradouro"], $endereco["espaco_numero"],
        $endereco["espaco_bairro"], $endereco["espaco_complemento"], $id_promotor);
    $stmtEndereco->execute();
}

if ($atualizar_area) {
    $stmtArea = $conexao->prepare("UPDATE promotor SET espaco_area_m2 = ? WHERE id = ?");
    $stmtArea->bind_param("si", $area, $id_promotor);
    $stmtArea->execute();
}

$stmtDelCampo = $conexao->prepare("DELETE FROM promotor_campo WHERE id_promotor = ?");
$stmtDelCampo->bind_param("i", $id_promotor);
$stmtDelCampo->execute();

$stmtInsCampo = $conexao->prepare("INSERT INTO promotor_campo (id_promotor, campo) VALUES (?, ?)");
foreach($campos_selecionados as $campo){
    $stmtInsCampo->bind_param("is", $id_promotor, $campo);
    $stmtInsCampo->execute();
}
$conexao->commit();
$_SESSION["usuario_nome"] = $nome;

$uploads = [
    "banner"      => ["coluna" => "banner",      "pasta" => "banner", "largura" => 1600, "altura" => 600,  "qualidade" => 80],
    "foto_perfil" => ["coluna" => "foto_perfil", "pasta" => "perfil-promotor", "largura" => 600,  "altura" => 600, "qualidade" => 82],
];

$stmtFotos = $conexao->prepare("SELECT banner, foto_perfil FROM promotor WHERE id = ?");
$stmtFotos->bind_param("i", $id_promotor);
$stmtFotos->execute();
$fotos_atuais = $stmtFotos->get_result()->fetch_assoc();

foreach($uploads as $campo => $config){
    $pedido_remover = (string) ($_POST["remover_" . $campo] ?? "") === "1";
    $tem_arquivo_novo = isset($_FILES[$campo]) && $_FILES[$campo]["error"] == 0 && !empty($_FILES[$campo]["name"]);

    if($pedido_remover && !$tem_arquivo_novo){
        apagar_upload_antigo($config["pasta"], $fotos_atuais[$config["coluna"]] ?? null);
        $stmtLimpa = $conexao->prepare("UPDATE promotor SET {$config["coluna"]} = NULL WHERE id = ?");
        if($stmtLimpa){
            $stmtLimpa->bind_param("i", $id_promotor);
            $stmtLimpa->execute();
        }
        continue;
    }

    if($tem_arquivo_novo){
        $destino = __DIR__ . "/../uploads/" . $config["pasta"] . "/";

        $nome_arquivo = otimizar_e_salvar_imagem($_FILES[$campo], $destino, $config["pasta"], $config["largura"], $config["altura"], $config["qualidade"]);

        if($nome_arquivo){
            apagar_upload_antigo($config["pasta"], $fotos_atuais[$config["coluna"]] ?? null);

            $stmtUp = $conexao->prepare("UPDATE promotor SET {$config["coluna"]} = ? WHERE id = ?");
            $stmtUp->bind_param("si", $nome_arquivo, $id_promotor);
            $stmtUp->execute();
        }
    }
}

if(isset($_POST["capacidade_maxima"])){
    $capacidade_txt = trim((string) $_POST["capacidade_maxima"]);
    if($capacidade_txt === ""){
        $stmtCap = $conexao->prepare("UPDATE promotor SET capacidade_maxima = NULL WHERE id = ?");
        if($stmtCap){
            $stmtCap->bind_param("i", $id_promotor);
            $stmtCap->execute();
        }
    } elseif(ctype_digit($capacidade_txt)){
        $capacidade = (int) $capacidade_txt;
        if($capacidade >= 1 && $capacidade <= 100000){
            $stmtCap = $conexao->prepare("UPDATE promotor SET capacidade_maxima = ? WHERE id = ?");
            if($stmtCap){
                $stmtCap->bind_param("ii", $capacidade, $id_promotor);
                $stmtCap->execute();
            }
        }
    }
}

limpar_cache_foto_perfil();
header("Location: perfil.php?sucesso=1");
exit;
