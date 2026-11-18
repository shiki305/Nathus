<?php
session_start();
include_once("../Includes/conexao.php");
include_once("../Includes/promotor.php");

$erro = "";
$aviso_sessao = $_SESSION["aviso_sessao"] ?? "";
unset($_SESSION["aviso_sessao"]);
$voltar = $_POST["voltar"] ?? $_GET["voltar"] ?? "";

function destinoAposLogin($voltar, $tipo){
    if($tipo === "promotor"){
        return "../Dashboard/dashboard.php";
    }
    $voltar = str_replace("\\", "/", trim((string) $voltar));
    if(preg_match('#^(home/homeAnt\.php|home/minhas-solicitacoes\.php|home/home\.php\?campo=(organizadores|buffet|espaco)(?:&busca=[^&]*)?|Cliente/perfil\.php\?id=\d+(?:&ver_como_cliente=1)?|Cliente/solicitacao\.php\?pacote=\d+&id=\d+)$#', $voltar)){
        return "../" . $voltar;
    }
    return "../home/homeAnt.php";
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    $sql = "SELECT * FROM cliente WHERE email = ? LIMIT 1";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows == 1){
        $cliente = $resultado->fetch_assoc();

        if(password_verify($senha, $cliente["senha"]) || $senha == $cliente["senha"]){
            $id_cliente = (int) $cliente["id"];

            // O tipo vem só da conta. Ter (ou não) linha de promotor não troca o cliente.
            $tipo = obterTipoConta($conexao, $id_cliente);
            $id_promotor = $tipo === "promotor" ? resolverPromotorId($conexao, $id_cliente) : null;

            // Reinicia identidade da sessão (não herda foto/tipo de outra conta).
            iniciarSessaoUsuario($id_cliente, $cliente["nome"], $tipo, $id_promotor);

            // Destino pelo tipo: promotor vai pro painel (dashboard);
            // cliente entra primeiro pela homeAnt.
            header("Location: " . destinoAposLogin($voltar, $tipo));
            exit;
        }
        else{
            $erro = "Senha incorreta.";
        }
    }
    else{
        $erro = "E-mail não encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nathus</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="CSS/login.css?v=<?php echo filemtime(__DIR__ . "/CSS/login.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/painel-texto.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/painel-texto.css"); ?>">
</head>
<body>
    <main class="divisao">
        <section id="esquerda">
                <form class="conteudo-login" action="login.php" method="post">
                    <input type="hidden" name="voltar" value="<?php echo htmlspecialchars($voltar, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="titulo">
                    <h1>Acesse sua conta</h1>
                    <p>Insira seus dados</p>
                    <hr>
                </div>

                <?php if($erro != ""){ ?>
                    <p class="erro-login"><?php echo $erro; ?></p>
                <?php } elseif($aviso_sessao != ""){ ?>
                    <p class="erro-login"><?php echo htmlspecialchars($aviso_sessao); ?></p>
                <?php } ?>

                <div class="campo campo-grande">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="campo campo-grande">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <button type="submit">Continuar</button>
            </form>
        </section>

        <section id="direita" style="background-image: url('../assets/cadastro-direita.png?v=<?php echo filemtime(__DIR__ . '/../assets/cadastro-direita.png'); ?>')">
            <div class="fundo-escuro"></div>
            <?php
                $painel_acao = '../Cadastro-geral/geral.php';
                $painel_rotulo = 'Criar conta';
                include __DIR__ . '/../Includes/painel-texto.php';
            ?>
        </section>
    </main>
</body>
</html>
