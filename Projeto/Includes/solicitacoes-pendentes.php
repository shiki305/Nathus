<?php

function solicitacoes_pendentes(mysqli $conexao, int $id_promotor, ?int $limite = null): array {
    $sql = "SELECT s.id, s.nome_evento, s.nome_cliente, s.data_festa, s.horario, s.local_festa,
                   s.numero_convidados, s.observacoes, s.pacote_selecionado, s.recebida,
                   c.nome AS conta_cliente, c.foto AS foto_cliente, pacote.imagem AS pacote_imagem
            FROM solicitacoes s
            LEFT JOIN cliente c ON c.id = s.id_cliente
            LEFT JOIN promotor_pacote pacote ON pacote.id_promotor = s.id_promotor
                AND pacote.id = COALESCE(s.pacote_id, (
                    SELECT anterior.id FROM promotor_pacote anterior
                    WHERE anterior.id_promotor = s.id_promotor
                      AND anterior.titulo IN (TRIM(SUBSTRING_INDEX(s.pacote_selecionado, CHAR(10), 1)),
                                             TRIM(SUBSTRING_INDEX(s.pacote_selecionado, ' R$', 1)))
                    ORDER BY CHAR_LENGTH(anterior.titulo) DESC, anterior.id LIMIT 1
                ))
            WHERE s.id_promotor = ? AND (s.status = 'pendente' OR s.status IS NULL OR s.status = '')
            ORDER BY s.recebida DESC, s.id DESC";

    if ($limite === null) {
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id_promotor);
    } else {
        $stmt = $conexao->prepare($sql . " LIMIT ?");
        $stmt->bind_param("ii", $id_promotor, $limite);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
