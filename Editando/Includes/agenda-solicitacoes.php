<?php
require_once __DIR__ . "/periodo-evento.php";

function intervalo_dias_evento(string $data): ?array {
    $data = trim($data);
    $periodo = datas_periodo_evento($data);
    if ($periodo) {
        $inicio = data_evento_iso($periodo[0]);
        $fim = data_evento_iso($periodo[1]);
        return $inicio <= $fim ? [$inicio, $fim] : null;
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:\s.*)?$/D', $data, $partes)) {
        $data = "$partes[3]/$partes[2]/$partes[1]";
    } elseif (preg_match('/^(\d{2}\/\d{2})\/(\d{2})$/D', $data, $partes)) {
        $data = "$partes[1]/20$partes[2]";
    }
    $dia = data_evento_iso($data);
    return $dia === null ? null : [$dia, $dia];
}

function dias_reservados_promotor(mysqli $conexao, int $id_promotor, int $ignorar_solicitacao = 0): array {
    $stmt = $conexao->prepare(
        "SELECT data_festa FROM solicitacoes WHERE id_promotor = ? AND status IN ('aceito', 'aceita') AND id <> ?"
    );
    $stmt->bind_param("ii", $id_promotor, $ignorar_solicitacao);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $intervalos = [];
    while ($linha = $resultado->fetch_assoc()) {
        $periodo = intervalo_dias_evento((string) $linha["data_festa"]);
        if ($periodo !== null) {
            $intervalos[] = $periodo;
        }
    }
    return $intervalos;
}

function periodo_tem_reserva(array $reservas, string $inicio, string $fim): bool {
    foreach ($reservas as [$primeiro_dia, $ultimo_dia]) {
        if ($inicio <= $ultimo_dia && $fim >= $primeiro_dia) {
            return true;
        }
    }
    return false;
}

// O envio e o aceite usam a mesma trava antes de consultar a disponibilidade.
function bloquear_agenda_promotor(mysqli $conexao, int $id_promotor): ?int {
    $stmt = $conexao->prepare("SELECT id_cliente FROM promotor WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $id_promotor);
    $stmt->execute();
    $promotor = $stmt->get_result()->fetch_assoc();
    return $promotor ? (int) $promotor["id_cliente"] : null;
}
