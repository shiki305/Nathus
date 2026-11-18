<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "nathus";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);

if($conexao->connect_error){
    die("Erro ao conectar com o banco: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

/**
 * Sessão órfã: o banco foi reimportado (o reset muda os ids) ou a conta foi
 * apagada. O id guardado na sessão pode não existir mais — e aí o topo mostra
 * o nome antigo da sessão com o tipo de outra conta no banco ("Marcelo Prado"
 * aparecendo como Cliente). Aqui a sessão é descartada e o login volta.
 */
function sessao_usuario_existe($conexao): bool {
    if(session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION["usuario_id"])){
        return true;
    }
    $id_sessao = (int) $_SESSION["usuario_id"];
    if($id_sessao <= 0){
        return false;
    }
    $stmt_sessao = $conexao->prepare("SELECT id FROM cliente WHERE id = ? LIMIT 1");
    if(!$stmt_sessao){
        return true; // falha de consulta não desloga ninguém
    }
    $stmt_sessao->bind_param("i", $id_sessao);
    $stmt_sessao->execute();
    return (bool) $stmt_sessao->get_result()->fetch_row();
}

if(!sessao_usuario_existe($conexao)){
    $_SESSION = [];
    if(!headers_sent()){
        session_regenerate_id(true);
    }
    $_SESSION["aviso_sessao"] = "Sua sessão anterior não vale mais (o banco foi atualizado). Entre novamente.";
}

// Migrações leves (só com sessão ativa).
if(session_status() === PHP_SESSION_ACTIVE){
    garantir_email_unico_cliente($conexao);
    garantir_tipo_conta_cliente($conexao);
    garantir_foto_cliente($conexao);
    normalizar_campos_promotor($conexao);
    garantir_busca_promotor($conexao);
    garantir_endereco_espaco($conexao);
}

function garantir_endereco_espaco($conexao){
    if(!empty($_SESSION["_migracao_endereco_espaco_v2"])){
        return;
    }
    $colunas = $conexao->query("SHOW COLUMNS FROM promotor WHERE Field IN
        ('espaco_logradouro', 'espaco_numero', 'espaco_bairro', 'espaco_complemento', 'espaco_area_m2')");
    if($colunas->num_rows === 5 || $conexao->query("ALTER TABLE promotor
        ADD COLUMN IF NOT EXISTS espaco_logradouro VARCHAR(90) NULL,
        ADD COLUMN IF NOT EXISTS espaco_numero VARCHAR(20) NULL,
        ADD COLUMN IF NOT EXISTS espaco_bairro VARCHAR(40) NULL,
        ADD COLUMN IF NOT EXISTS espaco_complemento VARCHAR(30) NULL,
        ADD COLUMN IF NOT EXISTS espaco_area_m2 DECIMAL(10,2) NULL")){
        $_SESSION["_migracao_endereco_espaco_v2"] = true;
    }
}

/**
 * Garante e-mail único em cliente (evita duas contas com o mesmo login).
 * Roda uma vez por sessão PHP.
 */
function garantir_email_unico_cliente($conexao){
    if(session_status() !== PHP_SESSION_ACTIVE){
        return;
    }
    if(!empty($_SESSION["_migracao_email_unico"])){
        return;
    }

    $resultado = $conexao->query("SHOW INDEX FROM cliente WHERE Key_name = 'uniq_cliente_email'");
    if($resultado && $resultado->num_rows === 0){
        // Só cria o índice se não houver e-mails duplicados.
        $dup = $conexao->query("SELECT email FROM cliente WHERE email IS NOT NULL AND email != '' GROUP BY email HAVING COUNT(*) > 1 LIMIT 1");
        if($dup && $dup->num_rows === 0){
            $conexao->query("ALTER TABLE cliente ADD UNIQUE KEY uniq_cliente_email (email)");
        }
    }

    $_SESSION["_migracao_email_unico"] = true;
}

/**
 * Garante coluna cliente.tipo_conta (cliente|promotor) e preenche
 * contas que já têm linha em promotor.
 */
function garantir_tipo_conta_cliente($conexao){
    if(session_status() !== PHP_SESSION_ACTIVE){
        return;
    }
    if(!empty($_SESSION["_migracao_tipo_conta"])){
        return;
    }

    $resultado = $conexao->query("SHOW COLUMNS FROM cliente LIKE 'tipo_conta'");
    if($resultado && $resultado->num_rows === 0){
        $conexao->query("ALTER TABLE cliente ADD COLUMN tipo_conta VARCHAR(20) NOT NULL DEFAULT 'cliente' AFTER cpf");
    }

    // Não promove cliente a promotor só porque existe uma linha em promotor.
    // As duas contas são separadas; o tipo só muda no cadastro de cada uma.
    $_SESSION["_migracao_tipo_conta"] = true;
}

/**
 * Foto da conta de cliente (o promotor usa promotor.foto_perfil).
 */
function garantir_foto_cliente($conexao){
    if(session_status() !== PHP_SESSION_ACTIVE){
        return;
    }
    if(!empty($_SESSION["_migracao_foto_cliente"])){
        return;
    }

    $resultado = $conexao->query("SHOW COLUMNS FROM cliente LIKE 'foto'");
    if($resultado && $resultado->num_rows === 0){
        if(!$conexao->query("ALTER TABLE cliente ADD COLUMN foto VARCHAR(255) NULL AFTER cpf")){
            return;
        }
    }

    $_SESSION["_migracao_foto_cliente"] = true;
}

/**
 * O script de exemplo grava Buffet / Organizacao / Espaco.
 * A home usa buffet / organizacao / local.
 */
/**
 * Filtros da home: capacidade do espaço e especialidades reais.
 * Não transforma perfil vazio em "geral". Só substitui o rótulo antigo
 * (Buffet / Organizacao / Espaco) que estava na tabela de especialidade.
 */
function garantir_busca_promotor($conexao){
    if(session_status() !== PHP_SESSION_ACTIVE){
        return;
    }

    $coluna = $conexao->query("SHOW COLUMNS FROM promotor LIKE 'capacidade_maxima'");
    $criou_capacidade = false;
    if($coluna && $coluna->num_rows === 0){
        if(!$conexao->query("ALTER TABLE promotor ADD COLUMN capacidade_maxima INT NULL")){
            return;
        }
        $criou_capacidade = true;
    }

    if(!empty($_SESSION["_migracao_busca_v1"])){
        return;
    }

    if($criou_capacidade){
        $capacidades = [80, 100, 120, 150, 180, 200, 250, 300, 350, 400, 500, 220, 160, 90, 280];
        $locais = $conexao->query(
            "SELECT p.id
             FROM promotor p
             INNER JOIN promotor_campo pc ON pc.id_promotor = p.id
             WHERE LOWER(pc.campo) IN ('local', 'espaco', 'espaço')
               AND (p.capacidade_maxima IS NULL OR p.capacidade_maxima = 0)
             ORDER BY p.id"
        );
        if($locais){
            $i = 0;
            $upd = $conexao->prepare("UPDATE promotor SET capacidade_maxima = ? WHERE id = ? AND (capacidade_maxima IS NULL OR capacidade_maxima = 0)");
            while($linha = $locais->fetch_assoc()){
                $cap = $capacidades[$i % count($capacidades)];
                $id = (int) $linha["id"];
                if($upd){
                    $upd->bind_param("ii", $cap, $id);
                    $upd->execute();
                }
                $i++;
            }
        }
    }

    $conexao->query("DELETE FROM promotor_especialidade WHERE LOWER(tipo) IN ('buffet', 'espaco', 'espaço', 'organizacao', 'organização')");

    $mapa = [
        "organizacao" => [
            ["15_anos"],
            ["casamento"],
            ["formatura"],
            ["15_anos", "casamento"],
            ["casamento", "formatura"],
            ["15_anos", "formatura"],
            ["15_anos", "casamento", "formatura"],
        ],
        "buffet" => [
            ["massas"],
            ["infantil"],
            ["drinks"],
            ["pratos"],
            ["geral"],
            ["massas", "pratos"],
            ["infantil", "drinks"],
            ["massas", "geral"],
        ],
    ];
    $validos = "'15_anos','casamento','formatura','massas','infantil','drinks','pratos','geral'";
    $antigos = $conexao->query(
        "SELECT p.id, LOWER(pc.campo) AS campo
         FROM promotor p
         INNER JOIN promotor_campo pc ON pc.id_promotor = p.id
         WHERE LOWER(pc.campo) IN ('organizacao', 'buffet')
           AND EXISTS (
                SELECT 1 FROM promotor_especialidade pe
                WHERE pe.id_promotor = p.id
                  AND LOWER(pe.tipo) IN ('buffet', 'espaco', 'espaço', 'organizacao', 'organização')
           )
           AND NOT EXISTS (
                SELECT 1 FROM promotor_especialidade pe2
                WHERE pe2.id_promotor = p.id AND pe2.tipo IN ($validos)
           )
         ORDER BY p.id"
    );
    if($antigos){
        $indice = ["organizacao" => 0, "buffet" => 0];
        $apagar = $conexao->prepare("DELETE FROM promotor_especialidade WHERE id_promotor = ?");
        $inserir = $conexao->prepare("INSERT INTO promotor_especialidade (id_promotor, tipo) VALUES (?, ?)");
        while($linha = $antigos->fetch_assoc()){
            $grupo = $linha["campo"] === "buffet" ? "buffet" : "organizacao";
            $opcoes = $mapa[$grupo];
            $escolha = $opcoes[$indice[$grupo] % count($opcoes)];
            $indice[$grupo]++;
            $id = (int) $linha["id"];
            if($apagar){
                $apagar->bind_param("i", $id);
                $apagar->execute();
            }
            foreach($escolha as $tipo){
                if($inserir){
                    $inserir->bind_param("is", $id, $tipo);
                    $inserir->execute();
                }
            }
        }
    }

    $_SESSION["_migracao_busca_v1"] = true;
}

function normalizar_campos_promotor($conexao){
    if(session_status() !== PHP_SESSION_ACTIVE){
        return;
    }
    if(!empty($_SESSION["_migracao_campo_v1"])){
        return;
    }
    $conexao->query("UPDATE promotor_campo SET campo = 'buffet' WHERE LOWER(campo) = 'buffet'");
    $conexao->query("UPDATE promotor_campo SET campo = 'organizacao' WHERE LOWER(campo) IN ('organizacao', 'organização')");
    $conexao->query("UPDATE promotor_campo SET campo = 'local' WHERE LOWER(campo) IN ('espaco', 'espaço', 'local')");
    $_SESSION["_migracao_campo_v1"] = true;
}

/**
 * Garante colunas novas na tabela solicitacoes (id_promotor, status).
 * Roda uma vez por sessão PHP — se algo falhar em produção, apague
 * a chave "_migracao_solicitacoes" da sessão ou reimporte o nathus.sql.
 */
function garantir_colunas_solicitacoes($conexao){
    if(session_status() !== PHP_SESSION_ACTIVE){
        return; // precisa de sessão ativa para rodar só uma vez
    }
    if(!empty($_SESSION["_migracao_solicitacoes_v2"])){
        garantir_texto_solicitacoes($conexao);
        garantir_resposta_solicitacoes($conexao);
        return;
    }

    // id_promotor: dono da solicitação (evita que promotores diferentes
    // vejam as solicitações uns dos outros).
    $resultado = $conexao->query("SHOW COLUMNS FROM solicitacoes LIKE 'id_promotor'");
    if($resultado && $resultado->num_rows === 0){
        $conexao->query("ALTER TABLE solicitacoes ADD COLUMN id_promotor INT NULL AFTER id");
    }

    // status: pendente / aceito / recusado / concluido
    $resultado = $conexao->query("SHOW COLUMNS FROM solicitacoes LIKE 'status'");
    if($resultado && $resultado->num_rows === 0){
        $conexao->query("ALTER TABLE solicitacoes ADD COLUMN status VARCHAR(30) NULL DEFAULT 'pendente'");
    }

    // id_cliente: conta que enviou a solicitação (separada da conta do promotor).
    $resultado = $conexao->query("SHOW COLUMNS FROM solicitacoes LIKE 'id_cliente'");
    if($resultado && $resultado->num_rows === 0){
        $conexao->query("ALTER TABLE solicitacoes ADD COLUMN id_cliente INT NULL AFTER id_promotor");
    }

    $_SESSION["_migracao_solicitacoes_v2"] = true;
    garantir_texto_solicitacoes($conexao);
    garantir_resposta_solicitacoes($conexao);
}

/**
 * Momento em que o promotor aceitou ou recusou. O cliente tem 2 dias
 * a partir daí para cancelar uma solicitação aceita.
 */
function garantir_resposta_solicitacoes($conexao){
    if(!empty($_SESSION["_migracao_solicitacoes_v6"])){
        return;
    }
    $colunas = [
        'respondida_em' => 'DATETIME NULL',
        'pacote_id' => 'INT NULL',
        'pacote_itens_envio' => 'TEXT NULL',
        'resposta_pendente' => 'VARCHAR(8) NULL',
    ];
    $resultado = $conexao->query("SHOW COLUMNS FROM solicitacoes");
    if(!$resultado){
        return;
    }
    foreach($resultado->fetch_all(MYSQLI_ASSOC) as $coluna){
        unset($colunas[$coluna['Field']]);
    }
    foreach($colunas as $nome => $tipo){
        if(!$conexao->query("ALTER TABLE solicitacoes ADD COLUMN IF NOT EXISTS `$nome` $tipo")){
            return;
        }
    }
    $_SESSION["_migracao_solicitacoes_v6"] = true;
}

/**
 * A data digitada pelo cliente é texto ("19:00 - 21/08"), não uma data SQL.
 * Se a coluna for DATE, o INSERT falha e a solicitação não chega ao promotor.
 */
function garantir_texto_solicitacoes($conexao){
    if(!empty($_SESSION["_migracao_solicitacoes_v3"])){
        return;
    }
    $conexao->query("ALTER TABLE solicitacoes MODIFY horario VARCHAR(80) NULL");
    $conexao->query("ALTER TABLE solicitacoes MODIFY data_festa VARCHAR(80) NULL");
    $conexao->query("ALTER TABLE solicitacoes MODIFY pacote_selecionado VARCHAR(255) NULL");
    $_SESSION["_migracao_solicitacoes_v3"] = true;
}
?>
