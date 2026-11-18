<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "../";
$pasta_uploads = __DIR__ . "/../uploads/";

include_once __DIR__ . "/../Includes/conexao.php";
include_once __DIR__ . "/../Includes/promotor.php";
require_once __DIR__ . "/../Includes/tipos-festa.php";

// Migração automática das colunas novas (id_promotor, status).
garantir_colunas_solicitacoes($conexao);

$rotulos_especialidade = TIPOS_FESTA + [
    "massas"    => ["label" => "Massas",            "icone" => ""],
    "infantil"  => ["label" => "Buffet infantil",   "icone" => ""],
    "drinks"    => ["label" => "Buffet de drinks",  "icone" => ""],
    "pratos"    => ["label" => "Pratos principais", "icone" => ""],
    "geral"     => ["label" => "Buffet geral",      "icone" => ""],
];

// Perfil PÚBLICO sendo visto (não confundir com $_SESSION["promotor_id"]
// da conta logada — são contextos diferentes).
$id_promotor = (int) ($_GET["id"] ?? $_SESSION["id_promotor_visto"] ?? 0);

$promotor = null;
if ($id_promotor > 0) {
    $stmt = $conexao->prepare(
        "SELECT p.*, precos.preco_minimo AS preco_pacotes, c.nome AS nome_cliente, c.telefone AS telefone_promotor
         FROM promotor p
         LEFT JOIN (" . SQL_PRECOS_MINIMOS . ") precos ON precos.id_promotor = p.id
         LEFT JOIN cliente c ON c.id = p.id_cliente
         WHERE p.id = ?
         LIMIT 1"
    );
    $stmt->bind_param("i", $id_promotor);
    $stmt->execute();
    $promotor = $stmt->get_result()->fetch_assoc();
}

// Promotor logado abrindo o PRÓPRIO card: vai pro painel, não pra área do cliente.
// Use ?ver_como_cliente=1 para pré-visualizar o perfil público de propósito.
$eh_dono_perfil = false;
if ($promotor && !empty($_SESSION["usuario_id"])) {
    $meu_promotor = obterPromotorExistente($conexao, (int) $_SESSION["usuario_id"]);
    if ($meu_promotor !== null && $meu_promotor === (int) $promotor["id"]) {
        $eh_dono_perfil = true;
        if (empty($_GET["ver_como_cliente"])) {
            header("Location: ../Perfil/perfil.php");
            exit;
        }
    }
}

if (!$promotor) {
    // Com ?id= explícito e inexistente, não inventa outro promotor:
    // mostra página de "não encontrado" (antes caía no fallback Maria).
    if (isset($_GET["id"])) {
        http_response_code(404);
        ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotor não encontrado | Nathus</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="css/perfil.css?v=<?php echo filemtime(__DIR__ . "/css/perfil.css"); ?>">
</head>
<body>
<main class="pagina" id="pagina">
    <section class="cartao" style="text-align:center; padding:60px 30px;">
        <h1 style="margin-bottom:12px;">Promotor não encontrado</h1>
        <p style="color:#6b7280; margin-bottom:24px;">O perfil que você tentou acessar não existe ou foi removido.</p>
        <a class="botao-solicitar" style="display:inline-flex; text-decoration:none;" href="../home/organizadores.php">Ver organizadores</a>
    </section>
</main>
</body>
</html>
        <?php
        exit;
    }

    // Sem ?id= na URL: mantém o comportamento de demonstração.
    $resultado = $conexao->query(
        "SELECT p.*, precos.preco_minimo AS preco_pacotes, c.nome AS nome_cliente, c.telefone AS telefone_promotor
         FROM promotor p
         LEFT JOIN (" . SQL_PRECOS_MINIMOS . ") precos ON precos.id_promotor = p.id
         LEFT JOIN cliente c ON c.id = p.id_cliente
         ORDER BY p.id ASC
         LIMIT 1"
    );
    $promotor = $resultado ? $resultado->fetch_assoc() : null;
}

if (!$promotor) {
    $promotor = [
        "id" => 0,
        "nome_cliente" => "Maria Lopes Rosa",
        "nome_fantasia" => "Maria Lopes Rosa",
        "foto_perfil" => "",
        "banner" => "",
        "descricao" => "Olá! Sou Maria Lopes Ferreira, organizadora de eventos com 3 anos de experiência na criação de momentos inesquecíveis, transformando sonhos em celebrações únicas e especiais.",
        "frase_destaque" => "Transformando Sonhos em Eventos Inesquecíveis",
        "cidade" => "Rio de Janeiro",
        "estado" => "RJ",
        "nota_media" => 4.7,
        "total_avaliacoes" => 42,
    ];
}

// Só marca o promotor VISTO — nunca altera a conta logada.
$visto_anterior = (int) ($_SESSION["id_promotor_visto"] ?? 0);
$_SESSION["id_promotor_visto"] = (int) $promotor["id"];
$id_promotor = (int) $promotor["id"];

$campo_lista = (string) ($_GET["campo"] ?? "");
if (!in_array($campo_lista, ["organizadores", "buffet", "espaco"], true)) {
    $campo_lista = "organizadores";
    if ($id_promotor > 0) {
        $stmtCampo = $conexao->prepare("SELECT campo FROM promotor_campo WHERE id_promotor = ? LIMIT 1");
        if ($stmtCampo) {
            $stmtCampo->bind_param("i", $id_promotor);
            $stmtCampo->execute();
            $linhaCampo = $stmtCampo->get_result()->fetch_assoc();
            $codigo = function_exists("mb_strtolower")
                ? mb_strtolower(trim((string) ($linhaCampo["campo"] ?? "")), "UTF-8")
                : strtolower(trim((string) ($linhaCampo["campo"] ?? "")));
            if ($codigo === "buffet") {
                $campo_lista = "buffet";
            } elseif (in_array($codigo, ["local", "espaco", "espaço"], true)) {
                $campo_lista = "espaco";
            }
        }
    }
}

// Ao trocar de perfil público, limpa estado de solicitação do perfil anterior
// (evita status/avaliação de um promotor vazar para outro).
if ($visto_anterior > 0 && $visto_anterior !== $id_promotor) {
    unset($_SESSION["id_solicitacao"], $_SESSION["status_solicitacao"]);
}

$nome_promotor = trim($promotor["nome_fantasia"] ?? "") !== ""
    ? $promotor["nome_fantasia"]
    : ($promotor["nome_cliente"] ?? "Promotor");

$foto_perfil = midia_publica($promotor["foto_perfil"] ?? "", ["perfil-promotor"], $base_url);
$tem_foto = $foto_perfil !== "";
if (!$tem_foto) {
    $foto_perfil = $base_url . "assets/user_dashboard.svg";
}

$banner = banner_publico_promotor($promotor["banner"] ?? "", $base_url);

$localizacao = trim(($promotor["cidade"] ?? "") . (!empty($promotor["estado"]) ? ", " . $promotor["estado"] : ""));
if ($localizacao === "") {
    $localizacao = "Localização não informada";
}

$preco_minimo = $promotor["preco_pacotes"] ?? null;
$preco_minimo_fmt = $preco_minimo !== null
    ? "R$ " . number_format((float) $preco_minimo, 2, ",", ".")
    : "A combinar";

$nota_media = number_format((float) ($promotor["nota_media"] ?? 0), 1, ",", ".");
$total_avaliacoes = (int) ($promotor["total_avaliacoes"] ?? 0);

$especialidades = [];
$tipos_festa = [];
$pacotes = [];
$galeria = [];

if ($id_promotor > 0) {
    $stmtEsp = $conexao->prepare("SELECT tipo FROM promotor_especialidade WHERE id_promotor = ?");
    $stmtEsp->bind_param("i", $id_promotor);
    $stmtEsp->execute();
    $resEsp = $stmtEsp->get_result();
    while ($linha = $resEsp->fetch_assoc()) {
        if (isset($rotulos_especialidade[$linha["tipo"]])) {
            $especialidades[] = $rotulos_especialidade[$linha["tipo"]];
        }
        if (isset(TIPOS_FESTA[$linha["tipo"]])) {
            $tipos_festa[$linha["tipo"]] = TIPOS_FESTA[$linha["tipo"]]["label"];
        }
    }

    $stmtPac = $conexao->prepare("SELECT * FROM promotor_pacote WHERE id_promotor = ? ORDER BY ordem, id");
    $stmtPac->bind_param("i", $id_promotor);
    $stmtPac->execute();
    $pacotes = $stmtPac->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmtGal = $conexao->prepare("SELECT * FROM promotor_galeria WHERE id_promotor = ? ORDER BY id DESC");
    $stmtGal->bind_param("i", $id_promotor);
    $stmtGal->execute();
    $galeria = $stmtGal->get_result()->fetch_all(MYSQLI_ASSOC);
}

$capacidade_maxima = (int) ($promotor["capacidade_maxima"] ?? 0);



if (isset($_GET["reset"])) {
    unset($_SESSION["id_solicitacao"], $_SESSION["avaliacao_promotor"], $_SESSION["status_solicitacao"]);
    header("Location: perfil.php?id=" . $id_promotor);
    exit;
}

$id_solicitacao = 0;
$status = "nenhuma";

if (!empty($_SESSION["usuario_id"]) && $id_promotor > 0) {
    $id_cliente_status = (int) $_SESSION["usuario_id"];
    try {
        $stmtSt = $conexao->prepare(
            "SELECT id, status FROM solicitacoes
             WHERE id_promotor = ? AND id_cliente = ?
             ORDER BY id DESC
             LIMIT 1"
        );
        if ($stmtSt) {
            $stmtSt->bind_param("ii", $id_promotor, $id_cliente_status);
            $stmtSt->execute();
            $linhaSt = $stmtSt->get_result()->fetch_assoc();
            if ($linhaSt) {
                $id_solicitacao = (int) $linhaSt["id"];
                $_SESSION["id_solicitacao"] = $id_solicitacao;
                $status_banco = strtolower(trim((string) ($linhaSt["status"] ?? "pendente")));
                if ($status_banco === "aceito" || $status_banco === "aceita") {
                    $status = "aceita";
                } elseif ($status_banco === "recusado" || $status_banco === "recusada") {
                    $status = "recusada";
                } elseif ($status_banco === "concluido" || $status_banco === "concluida") {
                    $status = "concluida";
                } else {
                    $status = "pendente";
                }
                $_SESSION["status_solicitacao"] = $status;
            }
        }
    } catch (Throwable $e) {
        $status = "nenhuma";
    }
}

$ja_avaliou = !empty($_SESSION["avaliacao_promotor"][$id_promotor]);
$id_perfil_url = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$mostrar_avaliar = $id_perfil_url === $id_promotor
    && $id_solicitacao > 0
    && in_array($status, ["pendente", "aceita"], true)
    && !$ja_avaliou;
$enviado = isset($_GET["enviado"]);
$avaliado = isset($_GET["avaliado"]);

$nome_cliente_form = "";
$email_cliente_form = "";
$telefone_cliente_form = "";
if (!empty($_SESSION["usuario_id"])) {
    try {
        $stmtCli = $conexao->prepare("SELECT nome, email, telefone FROM cliente WHERE id = ? LIMIT 1");
        if ($stmtCli) {
            $stmtCli->bind_param("i", $_SESSION["usuario_id"]);
            $stmtCli->execute();
            $cli = $stmtCli->get_result()->fetch_assoc();
            if ($cli) {
                $nome_cliente_form = (string) ($cli["nome"] ?? "");
                $email_cliente_form = (string) ($cli["email"] ?? "");
                $telefone_cliente_form = (string) ($cli["telefone"] ?? "");
            }
        }
    } catch (Throwable $e) {
    }
} elseif (!empty($_SESSION["usuario_nome"])) {
    $nome_cliente_form = (string) $_SESSION["usuario_nome"];
}
