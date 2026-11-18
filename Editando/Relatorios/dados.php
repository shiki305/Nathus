<?php
require_once __DIR__ . "/../Includes/periodo-evento.php";


function relatorio_rotulo_status($status) {
    $status = strtolower(trim((string) $status));
    if (in_array($status, ["aceito", "aceita"], true)) {
        return "Aceita";
    }
    if (in_array($status, ["recusado", "recusada"], true)) {
        return "Recusada";
    }
    if (in_array($status, ["cancelada", "cancelado"], true)) {
        return "Cancelada";
    }
    if (in_array($status, ["concluido", "concluida"], true)) {
        return "Concluída";
    }
    return "Pendente";
}

function relatorio_classe_status($status) {
    $rotulo = relatorio_rotulo_status($status);
    $mapa = [
        "Aceita" => "aceita",
        "Recusada" => "recusada",
        "Cancelada" => "cancelada",
        "Concluída" => "concluida",
        "Pendente" => "pendente",
    ];
    return $mapa[$rotulo];
}

function relatorio_mostrar_data($valor) {
    $valor = trim((string) $valor);
    if (datas_periodo_evento($valor)) return $valor;
    if ($valor === "") {
        return "—";
    }
    if (preg_match("/(\d{2}\/\d{2}(?:\/\d{2,4})?)/", $valor, $m)) {
        return $m[1];
    }
    if (preg_match("/^(\d{4})-(\d{2})-(\d{2})/", $valor, $m)) {
        return $m[3] . "/" . $m[2] . "/" . $m[1];
    }
    $ts = strtotime(str_replace("/", "-", $valor));
    return $ts ? date("d/m/Y", $ts) : $valor;
}

function relatorio_dados($conexao, $id_promotor, $ano_pedido) {
    $ano_atual = (int) date("Y");
    $anos = [$ano_atual];
    $stmtAnos = $conexao->prepare(
        "SELECT DISTINCT YEAR(recebida) AS ano
         FROM solicitacoes
         WHERE id_promotor = ? AND recebida IS NOT NULL
         ORDER BY ano DESC"
    );
    if ($stmtAnos) {
        $stmtAnos->bind_param("i", $id_promotor);
        $stmtAnos->execute();
        $resAnos = $stmtAnos->get_result();
        while ($linha = $resAnos->fetch_assoc()) {
            $ano = (int) $linha["ano"];
            if ($ano >= 2000 && $ano <= 2100 && !in_array($ano, $anos, true)) {
                $anos[] = $ano;
            }
        }
    }
    rsort($anos);

    $ano = (int) $ano_pedido;
    if (!in_array($ano, $anos, true)) {
        $ano = $ano_atual;
    }

    $linhas = [];
    $stmt = $conexao->prepare(
        "SELECT id, nome_evento, nome_cliente, status, data_festa, recebida, numero_convidados, local_festa
         FROM solicitacoes
         WHERE id_promotor = ? AND recebida IS NOT NULL AND YEAR(recebida) = ?
         ORDER BY recebida DESC, id DESC"
    );
    if ($stmt) {
        $stmt->bind_param("ii", $id_promotor, $ano);
        $stmt->execute();
        $linhas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    $contagem = [
        "Pendente" => 0,
        "Aceita" => 0,
        "Recusada" => 0,
        "Cancelada" => 0,
        "Concluída" => 0,
    ];
    $por_mes = array_fill(1, 12, 0);
    $convidados = 0;
    foreach ($linhas as $linha) {
        $contagem[relatorio_rotulo_status($linha["status"] ?? "")]++;
        $ts = strtotime((string) ($linha["recebida"] ?? ""));
        if ($ts) {
            $mes = (int) date("n", $ts);
            if ($mes >= 1 && $mes <= 12) {
                $por_mes[$mes]++;
            }
        }
        if (in_array(relatorio_rotulo_status($linha["status"] ?? ""), ["Aceita", "Concluída"], true)) {
            $convidados += (int) ($linha["numero_convidados"] ?? 0);
        }
    }

    $total = count($linhas);
    $aceitas = $contagem["Aceita"] + $contagem["Concluída"];

    return [
        "anos" => $anos,
        "ano" => $ano,
        "linhas" => $linhas,
        "contagem" => $contagem,
        "por_mes" => $por_mes,
        "convidados" => $convidados,
        "total" => $total,
        "aceitas" => $aceitas,
        "taxa" => $total > 0 ? (int) round(($aceitas / $total) * 100) : null,
        "pico" => max($por_mes),
        "nomes_meses" => ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"],
    ];
}
