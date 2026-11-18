<?php

function notificacoes_cliente(mysqli $conexao, int $id_cliente): array {
    $stmt = $conexao->prepare(
        "SELECT id, nome_evento, status, resposta_pendente
         FROM solicitacoes
         WHERE id_cliente = ?
         ORDER BY (resposta_pendente IS NOT NULL AND status NOT IN ('cancelado', 'cancelada')) DESC,
                  COALESCE(respondida_em, recebida) DESC, id DESC
         LIMIT 3"
    );
    $stmt->bind_param('i', $id_cliente);
    $stmt->execute();
    $linhas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($linhas as &$linha) {
        $status = strtolower(trim($linha['status'] ?? ''));
        $linha['nova'] = $linha['resposta_pendente'] !== null && !in_array($status, ['cancelado', 'cancelada'], true);
        $linha['rotulo'] = match ($status) {
            'aceito', 'aceita' => 'Solicitação aceita',
            'recusado', 'recusada' => 'Solicitação recusada',
            'cancelado', 'cancelada' => 'Solicitação cancelada',
            'concluido', 'concluida' => 'Solicitação concluída',
            default => 'Solicitação pendente',
        };
    }
    return $linhas;
}
