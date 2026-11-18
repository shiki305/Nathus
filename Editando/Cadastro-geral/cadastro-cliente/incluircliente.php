<?php
session_start();
include __DIR__ . "/../../Includes/conexao.php";
include_once __DIR__ . "/../../Includes/promotor.php";
include_once __DIR__ . "/../../Includes/imagens.php";
require_once __DIR__ . "/../../Includes/validar-contato.php";

// Página de tratamento do formulário da etapa 1 do cadastro de cliente.
// Recebe POST, grava no banco e redireciona pra etapa 2 (confirmação).

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nome"])){

    $nome     = trim($_POST["nome"]);
    $email    = trim($_POST["email"]);
    $senha    = trim($_POST["senha"]);
    $telefone = trim((string) ($_POST["telefone"] ?? ""));
    $cpf      = trim((string) ($_POST["cpf"] ?? ""));

    if (!telefone_brasileiro_valido($telefone) || !cpf_formato_valido($cpf)) {
        $_SESSION["cadastro"] = compact("nome", "email", "telefone", "cpf");
        header("Location: etapa1.php?erro=contato");
        exit;
    }

    // E-mail já usado (cliente ou promotor) — não cria conta misturando dados.
    $busca = $conexao->prepare("SELECT id FROM cliente WHERE email = ? LIMIT 1");
    $busca->bind_param("s", $email);
    $busca->execute();
    if($busca->get_result()->fetch_assoc()){
        $_SESSION["cadastro"] = [
            "nome"     => $nome,
            "email"    => $email,
            "telefone" => $telefone,
            "cpf"      => $cpf,
        ];
        header("Location: etapa1.php?erro=email");
        exit;
    }

    // Mesma regra do fluxo do promotor: hash no banco, nunca texto puro.
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // usuario a partir do e-mail (mais estável que só o nome).
    $usuario_base = strtolower(preg_replace("/[^a-z0-9]/i", "", strstr($email, "@", true) ?: $nome));
    if($usuario_base === ""){
        $usuario_base = "user";
    }
    $usuario = substr($usuario_base, 0, 24);

    $foto = null;
    $erro_upload = $_FILES["foto"]["error"] ?? UPLOAD_ERR_NO_FILE;
    if($erro_upload !== UPLOAD_ERR_NO_FILE){
        $pasta_foto = __DIR__ . "/../../uploads/perfil-cliente/";
        $foto = $erro_upload === UPLOAD_ERR_OK
            ? otimizar_e_salvar_imagem($_FILES["foto"], $pasta_foto, "cliente", 600, 600, 82)
            : false;
        if(!$foto || !is_file($pasta_foto . $foto) || !filesize($pasta_foto . $foto)){
            apagar_upload_antigo("perfil-cliente", $foto);
            $_SESSION["cadastro"] = compact("nome", "email", "telefone", "cpf");
            header("Location: etapa1.php?erro=foto");
            exit;
        }
    }

    $sql = "INSERT INTO cliente(usuario, nome, senha, email, telefone, cpf, foto, tipo_conta)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'cliente')";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssssss", $usuario, $nome, $senha_hash, $email, $telefone, $cpf, $foto);

    try {
        $salvou = $stmt->execute();
    } catch (mysqli_sql_exception $erro) {
        $salvou = false;
    }
    if($salvou){
        // Sessão limpa: evita herdar foto/tipo de um promotor testado antes.
        iniciarSessaoUsuario((int) $stmt->insert_id, $nome, "cliente");
        header("Location: etapa2.php");
        exit;
    }

    // Falhou o INSERT — volta pra etapa 1 com o erro.
    apagar_upload_antigo("perfil-cliente", $foto);
    $_SESSION["cadastro"] = [
        "nome"     => $nome,
        "email"    => $email,
        "telefone" => $telefone,
        "cpf"      => $cpf,
    ];
    header("Location: etapa1.php?erro=1");
    exit;
}

// O PHP entrega os campos vazios quando o corpo excede post_max_size.
if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($_POST) && empty($_FILES)
    && (int) ($_SERVER["CONTENT_LENGTH"] ?? 0) > 0) {
    header("Location: etapa1.php?erro=foto", true, 303);
    exit;
}

// Acesso direto sem POST: manda pro começo do fluxo.
header("Location: etapa1.php");
exit;
?>
