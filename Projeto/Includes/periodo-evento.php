<?php

function data_evento_iso(string $valor): ?string {
    if (!preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/D', $valor, $partes)
        || !checkdate((int) $partes[2], (int) $partes[1], (int) $partes[3])) {
        return null;
    }
    return "$partes[3]-$partes[2]-$partes[1]";
}

function horario_evento_valido(string $valor): bool {
    return (bool) preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $valor);
}

function datas_periodo_evento(string $valor): array {
    if (preg_match('/^([0-9]{2}\/[0-9]{2}\/[0-9]{4}) a ([0-9]{2}\/[0-9]{2}\/[0-9]{4})$/D', $valor, $partes)
        && data_evento_iso($partes[1]) !== null && data_evento_iso($partes[2]) !== null) {
        return [$partes[1], $partes[2]];
    }
    return [];
}

function fim_periodo_evento(string $datas, string $horarios): ?DateTimeImmutable {
    $periodo = datas_periodo_evento($datas);
    if (!$periodo || !preg_match('/ - ([0-9]{2}:[0-9]{2})$/D', $horarios, $partes)
        || !horario_evento_valido($partes[1])) {
        return null;
    }
    return new DateTimeImmutable(data_evento_iso($periodo[1]) . ' ' . $partes[1], new DateTimeZone('America/Sao_Paulo'));
}
