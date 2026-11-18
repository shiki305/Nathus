<?php
session_start();

if(!isset($_SESSION["cadastro"])){
    $_SESSION["cadastro"] = [];
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    foreach($_POST as $chave => $valor){
        $_SESSION["cadastro"][$chave] = $valor;
    }
    require_once __DIR__ . '/../../../Includes/validar-contato.php';
    if (!telefone_brasileiro_valido(trim((string) ($_POST["telefone"] ?? "")))
        || !cpf_formato_valido(trim((string) ($_POST["cpf"] ?? "")))) {
        header("Location: ../e1/e1.php?erro=contato");
        exit;
    }
    header("Location: e2.php", true, 303);
    exit;
}

$campos_atuacao = [
    ["id" => "organizacao", "titulo" => "Organização", "texto" => "Trabalha com uma equipe"],
    ["id" => "buffet",      "titulo" => "Buffet",      "texto" => "Trabalha com buffet"],
    ["id" => "local",       "titulo" => "Local",       "texto" => "Trabalha como salão de festas"],
];

$titulo = "Campo do promotor";
$css = "css/e2.css";
$voltar = "../e1/e1.php";
include "../topo.php";
?>
            <form class="conteudo-campo" action="../e3/e3.php" method="post">
                <div class="titulo">
                    <h1>Campo do promotor</h1>
                    <p>Selecione o campo em que você atua</p>
                    <hr>
                </div>

                <?php
                    $campo_salvo = $_SESSION["cadastro"]["campo"] ?? "";
                    if($campo_salvo === "" && isset($_SESSION["cadastro"]["campos"])){
                        $tmp = $_SESSION["cadastro"]["campos"];
                        $campo_salvo = is_array($tmp) ? (string) ($tmp[0] ?? "") : (string) $tmp;
                    }
                ?>
                <div class="lista-campos">
                    <?php foreach ($campos_atuacao as $campo): ?>
                    <label class="campo-cartao" for="<?php echo $campo['id']; ?>">
                        <input class="campo-input" type="radio" name="campo" id="<?php echo $campo['id']; ?>" value="<?php echo $campo['id']; ?>" <?php echo $campo_salvo === $campo["id"] ? "checked" : ""; ?> required>
                        <img src="../../../assets/cadastro-<?php echo $campo['id']; ?>.svg" alt="">
                        <div class="campo-texto">
                            <h2><?php echo $campo['titulo']; ?></h2>
                            <p><?php echo $campo['texto']; ?></p>
                        </div>
                        <span class="campo-indicador" aria-hidden="true"></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit">Continuar</button>
            </form>
<?php include "../fim.php"; ?>
