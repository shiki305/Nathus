<?php

function telefone_brasileiro_valido(string $valor): bool {
    if (!preg_match('/^\+?[\d ().-]{1,18}$/D', $valor)) return false;
    $numero = preg_replace('/\D/', '', $valor);
    if (str_starts_with($valor, '+') || strlen($numero) > 11) {
        return (bool) preg_match('/^55\d{10,11}$/D', $numero);
    }
    return (bool) preg_match('/^\d{10,11}$/D', $numero);
}

function cpf_formato_valido(string $valor): bool {
    return (bool) preg_match('/^(?:\d{11}|\d{3}\.\d{3}\.\d{3}-\d{2})$/D', $valor);
}
