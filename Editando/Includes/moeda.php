<?php

const MOEDA_BR_MAX_DIGITOS_INTEIROS = 8;

function moeda_br_excede_limite($valor): bool {
    if (!is_string($valor)) return false;

    $valor = preg_replace('/^R\$\s*/iu', '', trim($valor));
    if (!preg_match('/^([0-9][0-9.]*)(?:,[0-9]{0,2})?$/D', $valor, $partes)) return false;

    return strlen(str_replace('.', '', $partes[1])) > MOEDA_BR_MAX_DIGITOS_INTEIROS;
}

function moeda_br_decimal($valor): ?string {
    if (!is_string($valor)) return null;

    $valor = preg_replace('/^R\$\s*/iu', '', trim($valor));
    if (!preg_match('/^(?:[0-9]+|[0-9]{1,3}(?:\.[0-9]{3})+)(?:,[0-9]{1,2})?$/D', $valor)) {
        return null;
    }

    [$inteiros, $centavos] = array_pad(explode(',', str_replace('.', '', $valor), 2), 2, '');
    if (strlen($inteiros) > MOEDA_BR_MAX_DIGITOS_INTEIROS) return null;

    return (string) (int) $inteiros . '.' . str_pad($centavos, 2, '0');
}
