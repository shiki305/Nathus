<?php
/**
 * Fonte única de verdade para a foto de perfil do promotor.
 *
 * Ordem de resolução (cache em sessão para não repetir query em toda página):
 *   1. $_SESSION["promotor_foto"]  -> foto salva em uploads/perfil-promotor/
 *   2. fallback                    -> assets/user_dashboard.svg (template)
 *
 * Uso:
 *   include_once(__DIR__ . "/perfil_foto.php");
 *   [$foto_perfil, $tem_foto_perfil] = resolver_foto_perfil($conexao);
 */

/**
 * Resolve a foto de perfil do promotor logado.
 *
 * @param mysqli|null $conexao Conexão ativa (usada apenas se o cache expirou).
 * @return array{0: string, 1: bool} [caminho_web, tem_foto_real]
 */
function resolver_foto_perfil($conexao = null){
    if(!function_exists("midia_publica")){
        include_once __DIR__ . "/promotor.php";
    }
    $base_url = $GLOBALS["base_url"] ?? base_url_projeto();
    $fallback = $base_url . "assets/user_dashboard.svg";

    if(!isset($_SESSION["usuario_id"])){
        return [$fallback, false];
    }

    $id_atual = (int) $_SESSION["usuario_id"];

    if(
        isset($_SESSION["conta_foto_rel"], $_SESSION["conta_foto_uid"])
        && (int) $_SESSION["conta_foto_uid"] === $id_atual
    ){
        $rel = (string) $_SESSION["conta_foto_rel"];
        if($rel !== "" && is_file(__DIR__ . "/../" . $rel)){
            return [$base_url . $rel, true];
        }
        return [$fallback, false];
    }

    if($conexao === null){
        return [$fallback, false];
    }

    $rel = "";
    $eh_promotor = function_exists("obterTipoConta") && obterTipoConta($conexao, $id_atual) === "promotor";

    if($eh_promotor){
        $stmt = $conexao->prepare("SELECT foto_perfil FROM promotor WHERE id_cliente = ? LIMIT 1");
        if($stmt){
            $stmt->bind_param("i", $id_atual);
            $stmt->execute();
            $linha = $stmt->get_result()->fetch_assoc();
            $guardado = str_replace("\\", "/", trim((string) ($linha["foto_perfil"] ?? "")));
            $url = function_exists("midia_publica") ? midia_publica($guardado, ["perfil-promotor"]) : "";
            if($url !== ""){
                $rel = preg_replace('#^(\.\./)+#', '', $url);
            }
        }
    } else {
        $stmt = $conexao->prepare("SELECT foto FROM cliente WHERE id = ? LIMIT 1");
        if($stmt){
            $stmt->bind_param("i", $id_atual);
            $stmt->execute();
            $linha = $stmt->get_result()->fetch_assoc();
            $guardado = str_replace("\\", "/", trim((string) ($linha["foto"] ?? "")));
            $url = function_exists("midia_publica") ? midia_publica($guardado, ["perfil-cliente"]) : "";
            if($url !== ""){
                $rel = preg_replace('#^(\.\./)+#', '', $url);
            }
        }
    }

    $_SESSION["conta_foto_rel"] = $rel;
    $_SESSION["conta_foto_uid"] = $id_atual;
    unset($_SESSION["promotor_foto"], $_SESSION["promotor_foto_uid"]);

    if($rel !== ""){
        return [$base_url . $rel, true];
    }
    return [$fallback, false];
}

/**
 * Chamar SEMPRE que a foto do promotor for criada, trocada ou apagada
 * (salvar-editar-perfil.php, imagem-salvar.php, salvar-perfil.php...).
 */
function limpar_cache_foto_perfil(){
    unset($_SESSION["promotor_foto"], $_SESSION["promotor_foto_uid"], $_SESSION["conta_foto_rel"], $_SESSION["conta_foto_uid"]);
}

function renderizar_avatar_cliente(?string $arquivo, string $base_url): void {
    $foto = midia_publica($arquivo, ["perfil-cliente"], $base_url);
    $tem_foto = $foto !== "";
    if (!$tem_foto) {
        $foto = $base_url . "assets/user_dashboard.svg";
    }
    ?>
    <span class="avatar-cliente-card<?= $tem_foto ? '' : ' avatar-vazio' ?>" aria-hidden="true">
        <img src="<?= htmlspecialchars($foto) ?>" alt="">
    </span>
    <?php
}
