<?php

const CAMPOS_ENDERECO_ESPACO = [
    "espaco_logradouro" => ["Logradouro", 90],
    "espaco_numero" => ["Número", 20],
    "espaco_bairro" => ["Bairro", 40],
    "espaco_complemento" => ["Complemento (opcional)", 30],
];

function area_espaco_decimal(string $valor): ?string {
    $valor = trim($valor);
    if (preg_match('/^[0-9]{1,8}\.[0-9]{1,2}$/D', $valor)) {
        return (float) $valor > 0 ? $valor : null;
    }
    if (!preg_match('/^(?:[0-9]{1,8}|[0-9]{1,3}(?:\.[0-9]{3}){1,2})(?:,[0-9]{1,2})?$/D', $valor)) {
        return null;
    }
    $decimal = str_replace(['.', ','], ['', '.'], $valor);
    return (float) $decimal > 0 && (float) $decimal <= 99999999.99 ? $decimal : null;
}

function area_espaco_formatada($area): string {
    if (!is_numeric($area) || (float) $area <= 0) {
        return "";
    }
    $formatada = number_format((float) $area, 2, ",", ".");
    return rtrim(rtrim($formatada, "0"), ",");
}

function promotor_eh_espaco($conexao, int $id_promotor): bool {
    $stmt = $conexao->prepare("SELECT 1 FROM promotor_campo
        WHERE id_promotor = ? AND campo IN ('local', 'espaco', 'espaço') LIMIT 1");
    $stmt->bind_param("i", $id_promotor);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// O texto completo cabe nos 255 caracteres de solicitacoes.local_festa.
function endereco_espaco_formatado(array $perfil): ?string {
    foreach (["espaco_logradouro", "espaco_numero", "espaco_bairro", "cidade", "estado"] as $campo) {
        if (trim($perfil[$campo] ?? "") === "") {
            return null;
        }
    }

    $endereco = $perfil["espaco_logradouro"] . ", " . $perfil["espaco_numero"];
    if (trim($perfil["espaco_complemento"] ?? "") !== "") {
        $endereco .= ", " . $perfil["espaco_complemento"];
    }
    return $endereco . " - " . $perfil["espaco_bairro"] . ", " . $perfil["cidade"] . "/" . $perfil["estado"];
}
