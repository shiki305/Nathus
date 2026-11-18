<?php

const LIMITE_DESCRICAO_PROMOTOR = 1000;

function descricao_promotor_valida($descricao): bool {
    return is_string($descricao) && mb_check_encoding($descricao, "UTF-8")
        && strlen(mb_convert_encoding($descricao, "UTF-16LE", "UTF-8")) <= 2 * LIMITE_DESCRICAO_PROMOTOR;
}

const SQL_PRECOS_MINIMOS = "SELECT id_promotor, MIN(CAST(TRIM(preco) AS DECIMAL(10,2))) AS preco_minimo
    FROM promotor_pacote
    WHERE TRIM(preco) REGEXP '^[0-9]{1,8}([.][0-9]{1,2})?$'
    GROUP BY id_promotor";
/**
 * Identidade de conta (cliente x promotor).
 *
 * Fonte da verdade: coluna cliente.tipo_conta + linha em promotor.
 * Um promotor NÃO pode ser rebaixado a cliente só porque uma consulta
 * falhou ou a sessão estava suja — isso era o bug "virou cliente do nada".
 */

/**
 * Devolve o id do promotor ligado ao cliente, sem criar nada.
 * Retorna null se não houver perfil de promotor.
 */
function obterPromotorExistente($conexao, $id_cliente){
    $id_cliente = (int) $id_cliente;
    if($id_cliente <= 0){
        return null;
    }

    $stmt = $conexao->prepare("SELECT id FROM promotor WHERE id_cliente = ? LIMIT 1");
    if(!$stmt){
        return null;
    }
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    return $linha ? (int) $linha["id"] : null;
}

/**
 * Lê cliente.tipo_conta (cliente|promotor). Default: cliente.
 */
function obterTipoConta($conexao, $id_cliente){
    $id_cliente = (int) $id_cliente;
    if($id_cliente <= 0){
        return "cliente";
    }

    $stmt = $conexao->prepare("SELECT tipo_conta FROM cliente WHERE id = ? LIMIT 1");
    if(!$stmt){
        return "cliente";
    }
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    $tipo = strtolower(trim((string) ($linha["tipo_conta"] ?? "cliente")));
    return $tipo === "promotor" ? "promotor" : "cliente";
}

/**
 * Grava cliente.tipo_conta.
 */
function definirTipoConta($conexao, $id_cliente, $tipo){
    $id_cliente = (int) $id_cliente;
    $tipo = ($tipo === "promotor") ? "promotor" : "cliente";
    if($id_cliente <= 0){
        return;
    }
    $stmt = $conexao->prepare("UPDATE cliente SET tipo_conta = ? WHERE id = ?");
    if(!$stmt){
        return;
    }
    $stmt->bind_param("si", $tipo, $id_cliente);
    $stmt->execute();
}

/**
 * Resolve o id do promotor da conta com recuperação:
 * 1) linha em promotor por id_cliente
 * 2) $_SESSION['promotor_id'] válido
 * 3) se tipo_conta = promotor e a linha sumiu, recria o perfil
 *
 * Também sincroniza tipo_conta quando a linha de promotor existe.
 */
function resolverPromotorId($conexao, $id_cliente = null){
    $id_cliente = (int) ($id_cliente ?? ($_SESSION["usuario_id"] ?? 0));
    if($id_cliente <= 0){
        return null;
    }

    // Conta de cliente nunca vira promotor ao abrir uma página.
    if(obterTipoConta($conexao, $id_cliente) !== "promotor"){
        return null;
    }

    $id_promotor = obterPromotorExistente($conexao, $id_cliente);
    if($id_promotor !== null){
        return $id_promotor;
    }

    // Fallback: promotor_id da sessão SÓ se pertencer a ESTE usuario_id.
    // Nunca troca a conta logada — isso fazia o promotor "virar" outra conta
    // ao abrir o perfil público (card da home).
    if(!empty($_SESSION["promotor_id"])){
        $pid = (int) $_SESSION["promotor_id"];
        $stmt = $conexao->prepare("SELECT id, id_cliente FROM promotor WHERE id = ? LIMIT 1");
        if($stmt){
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $linha = $stmt->get_result()->fetch_assoc();
            if($linha && (int) $linha["id_cliente"] === $id_cliente){
                return (int) $linha["id"];
            }
        }
        // promotor_id sujo / de outro usuário — descarta.
        unset($_SESSION["promotor_id"]);
    }

    // Conta marcada como promotor sem linha: recria perfil mínimo.
    if(obterTipoConta($conexao, $id_cliente) === "promotor"){
        $stmtCriar = $conexao->prepare("INSERT INTO promotor (id_cliente, status_plano) VALUES (?, 'ativo')");
        if($stmtCriar){
            $stmtCriar->bind_param("i", $id_cliente);
            if($stmtCriar->execute()){
                return (int) $conexao->insert_id;
            }
        }
    }

    return null;
}

/**
 * True se a conta é (ou deve ser tratada como) promotor.
 */
function ehPromotor($conexao, $id_cliente = null){
    $id_cliente = (int) ($id_cliente ?? ($_SESSION["usuario_id"] ?? 0));
    if($id_cliente <= 0){
        return false;
    }
    return obterTipoConta($conexao, $id_cliente) === "promotor";
}

/**
 * Inicia (ou troca) a sessão de autenticação sem misturar dados da conta anterior.
 */
function iniciarSessaoUsuario($id_cliente, $nome, $tipo, $promotor_id = null){
    unset(
        $_SESSION["promotor_id"],
        $_SESSION["promotor_foto"],
        $_SESSION["promotor_foto_uid"],
        $_SESSION["conta_foto_rel"],
        $_SESSION["conta_foto_uid"],
        $_SESSION["id_promotor_visto"],
        $_SESSION["id_solicitacao"],
        $_SESSION["status_solicitacao"],
        $_SESSION["cadastro"]
    );

    $_SESSION["usuario_id"] = (int) $id_cliente;
    $_SESSION["usuario_nome"] = $nome;
    $_SESSION["usuario_tipo"] = ($tipo === "promotor") ? "promotor" : "cliente";

    if($tipo === "promotor" && $promotor_id !== null){
        $_SESSION["promotor_id"] = (int) $promotor_id;
    }
}

/**
 * Guarda das páginas do PAINEL: exige sessão E perfil de promotor.
 * Só manda para a área de cliente se a conta NÃO for promotor de verdade.
 */
function exigirPromotor($conexao){
    if(!isset($_SESSION["usuario_id"])){
        header("Location: ../Login/login.php");
        exit;
    }

    $id_cliente = (int) $_SESSION["usuario_id"];
    $id_promotor = resolverPromotorId($conexao, $id_cliente);

    if($id_promotor === null){
        // Conta genuinamente de cliente — área do painel não aplica.
        $_SESSION["usuario_tipo"] = "cliente";
        unset($_SESSION["promotor_id"], $_SESSION["promotor_foto"], $_SESSION["promotor_foto_uid"], $_SESSION["conta_foto_rel"], $_SESSION["conta_foto_uid"]);
        header("Location: ../home/homeAnt.php");
        exit;
    }

    $_SESSION["usuario_tipo"] = "promotor";
    $_SESSION["promotor_id"] = $id_promotor;
    $_SESSION["usuario_id"] = (int) ($_SESSION["usuario_id"] ?? $id_cliente);
    return $id_promotor;
}

/**
 * Páginas exclusivas do cliente. Promotor autenticado volta ao painel.
 */
function exigirCliente($conexao, $voltar = "home/homeAnt.php"){
    if(!isset($_SESSION["usuario_id"])){
        header("Location: ../Login/login.php?voltar=" . rawurlencode($voltar));
        exit;
    }

    $id_cliente = (int) $_SESSION["usuario_id"];
    if(obterTipoConta($conexao, $id_cliente) === "promotor"){
        header("Location: ../Dashboard/dashboard.php");
        exit;
    }

    $_SESSION["usuario_tipo"] = "cliente";
    unset($_SESSION["promotor_id"]);
    return $id_cliente;
}

/**
 * Rótulo do topo ("Cliente"/"Promotor"; "Visitante" sem login).
 * Usa tipo_conta + linha de promotor; não rebaixa promotor por falha passageira.
 */
function rotuloTipoUsuario($conexao){
    if(!isset($_SESSION["usuario_id"])){
        return "Visitante";
    }

    $id_cliente = (int) $_SESSION["usuario_id"];
    if(ehPromotor($conexao, $id_cliente)){
        $id_promotor = resolverPromotorId($conexao, $id_cliente);
        $_SESSION["usuario_tipo"] = "promotor";
        if($id_promotor !== null){
            $_SESSION["promotor_id"] = $id_promotor;
        }
        return "Promotor";
    }

    $_SESSION["usuario_tipo"] = "cliente";
    unset($_SESSION["promotor_id"]);
    return "Cliente";
}

/**
 * Compatibilidade: não cria promotor para cliente comum.
 * Use resolverPromotorId() / exigirPromotor() nos fluxos novos.
 */
function obterOuCriarPromotor($conexao, $id_cliente){
    return resolverPromotorId($conexao, $id_cliente);
}

/**
 * Prefixo relativo da página atual até a raiz do projeto.
 * home/ e Cliente/ ficam em ../ ; pastas mais fundas ganham o número certo de níveis.
 */
function base_url_projeto(){
    static $cache = null;
    if($cache !== null){
        return $cache;
    }

    $raiz = realpath(__DIR__ . "/..");
    $script = $_SERVER["SCRIPT_FILENAME"] ?? "";
    $pasta = $script !== "" ? realpath(dirname($script)) : false;
    if($raiz && $pasta){
        $raiz = str_replace("\\", "/", $raiz);
        $pasta = str_replace("\\", "/", $pasta);
        if(strpos($pasta, $raiz) === 0){
            $resto = trim(substr($pasta, strlen($raiz)), "/");
            $niveis = $resto === "" ? 0 : substr_count($resto, "/") + 1;
            $cache = $niveis > 0 ? str_repeat("../", $niveis) : "";
            return $cache;
        }
    }

    $cache = "../";
    return $cache;
}

/**
 * URL de uma imagem gravada no banco. Só devolve o arquivo se ele existir em uploads.
 */
function midia_publica($valor, array $pastas = [], $base_url = ""){
    $valor = str_replace("\\", "/", trim((string) $valor));
    if($valor === "" || strpos($valor, "..") !== false){
        return "";
    }

    $nome = basename($valor);
    $info = pathinfo($nome);
    $base = $info["filename"] ?? "";
    $nomes = [$nome];
    if($base !== ""){
        foreach(["jpg", "jpeg", "png", "webp"] as $extensao){
            $nomes[] = $base . "." . $extensao;
        }
    }
    $nomes = array_unique($nomes);

    $pastas = array_merge([""], $pastas, ["buffet", "organizador", "local", "org", "banner", "galeria", "pacotes", "perfil-promotor", "perfil-cliente"]);
    $candidatos = [ltrim($valor, "/")];
    $diretorio = str_replace("\\", "/", dirname(ltrim($valor, "/")));
    foreach($nomes as $arquivo){
        if($diretorio !== "" && $diretorio !== "."){
            $candidatos[] = $diretorio . "/" . $arquivo;
        }
        foreach($pastas as $pasta){
            $pasta = trim((string) $pasta, "/");
            $candidatos[] = $pasta === "" ? "uploads/" . $arquivo : "uploads/" . $pasta . "/" . $arquivo;
        }
    }

    $prefixo = $base_url !== "" ? $base_url : base_url_projeto();
    foreach(array_unique($candidatos) as $relativo){
        if($relativo === "" || strpos($relativo, "..") !== false){
            continue;
        }
        if(is_file(__DIR__ . "/../" . $relativo)){
            return $prefixo . $relativo;
        }
    }
    return "";
}

function banner_publico_promotor($banner, string $base_url = ""): string {
    $url = midia_publica($banner, ["banner"], $base_url);
    if ($url !== "") {
        return $url;
    }
    $prefixo = $base_url !== "" ? $base_url : base_url_projeto();
    return $prefixo . "assets/banner-padrao-nathus.png";
}

function reserva_visual_promotor($id, $base_url = "../"){
    $buffet = ["cardapio1.png", "cardapio6.png", "cardapio8.png", "cardapio11.png", "cardapio3.png", "cardapio14.png", "cardapio2.png", "cardapio4.png", "cardapio5.png", "cardapio7.png", "cardapio9.png", "cardapio10.png", "cardapio12.png", "cardapio13.png", "cardapio15.png"];
    $espaco = ["EC.png", "thevoila.png", "aurelisse.png", "lunavoir.png", "quoralys.png", "essalya.png", "elarune.png", "fioralis.png", "solarise.png", "vellanova.png", "montalise.png", "savennaya.png", "orilia.png", "bellayra.png", "aubelyn.png"];
    $id = (int) $id;
    $arquivo = "";
    if($id >= 1 && $id <= 15){
        $arquivo = $buffet[$id - 1];
    } elseif($id >= 16 && $id <= 30){
        $arquivo = "o" . ($id - 15) . ".png";
    } elseif($id >= 31 && $id <= 45){
        $arquivo = $espaco[$id - 31];
    }
    if($arquivo !== "" && is_file(__DIR__ . "/../assets/home/" . $arquivo)){
        return $base_url . "assets/home/" . $arquivo;
    }
    return "";
}

function foto_card_promotor(array $promotor, $base_url = ""){
    return midia_publica($promotor["foto_perfil"] ?? "", ["perfil-promotor", "buffet", "organizador", "local"], $base_url);
}
