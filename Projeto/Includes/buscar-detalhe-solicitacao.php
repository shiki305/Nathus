<?php

function buscar_detalhe_solicitacao(mysqli $conexao, int $id, int $id_promotor): ?array {
    $stmt = $conexao->prepare(
        "SELECT s.*, c.nome AS conta_cliente, c.foto AS foto_cliente, pacote.imagem AS foto_pacote,
                pacote.itens AS pacote_itens_atuais
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
         WHERE s.id = ? AND s.id_promotor = ?"
    );
    $stmt->bind_param("ii", $id, $id_promotor);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}
