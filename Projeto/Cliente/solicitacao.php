<?php
// 1. INICIA A SESSÃO E VERIFICA O LOGIN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// A sessão do app usa usuario_id (conta de cliente), não id_usuario.
$usuario_logado = isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] > 0;
$id_cliente_logado = $usuario_logado ? (int) $_SESSION['usuario_id'] : 0;

require_once __DIR__ . "/dados.php";
require_once __DIR__ . "/../Includes/validar-contato.php";
require_once __DIR__ . "/../Includes/endereco-espaco.php";
require_once __DIR__ . "/../Includes/agenda-solicitacoes.php";

$id_pacote = (int) ($_GET["pacote"] ?? $_POST["pacote"] ?? 0);
$pacote = null;
foreach ($pacotes as $item) {
    if ((int) $item["id"] === $id_pacote) {
        $pacote = $item;
        break;
    }
}
if (!$pacote) {
    if ($id_pacote !== 0 || !$pacotes) {
        header("Location: perfil.php?id=" . $id_promotor);
        exit;
    }
    $pacote = $pacotes[0];
}

$imagem_pacote = midia_publica($pacote["imagem"] ?? "", ["pacotes"], $base_url);
$preco_pacote = is_numeric($pacote["preco"])
    ? "R$ " . number_format((float) $pacote["preco"], 2, ",", ".")
    : (trim((string) ($pacote["preco"] ?? "")) !== "" ? $pacote["preco"] : "Preço sob consulta");

$erros = [];
$eh_espaco = $id_promotor > 0 && promotor_eh_espaco($conexao, $id_promotor);
$eh_buffet = false;
if (!$eh_espaco && $id_promotor > 0) {
    $stmtBuffet = $conexao->prepare("SELECT 1 FROM promotor_campo WHERE id_promotor = ? AND campo = 'buffet' LIMIT 1");
    $stmtBuffet->bind_param("i", $id_promotor);
    $stmtBuffet->execute();
    $eh_buffet = $stmtBuffet->get_result()->num_rows > 0;
}
$mostrar_tipo_festa = !$eh_buffet && (!$eh_espaco || $tipos_festa !== []);
$entrada_tipo = $_POST["tipo_festa"] ?? "";
$tipo = $mostrar_tipo_festa && is_string($entrada_tipo) ? trim($entrada_tipo) : "";
$limite_convidados = $capacidade_maxima > 0 ? $capacidade_maxima : null;
$entrada_convidados = $_POST["convidados"] ?? "";
$convidados_digitados = is_string($entrada_convidados) ? trim($entrada_convidados) : "";
$erro_convidados = "";
$limite_mensagem = 500;
$entrada_mensagem = $_POST["mensagem"] ?? "";
$mensagem_digitada = is_string($entrada_mensagem) ? str_replace(["\r\n", "\r"], "\n", $entrada_mensagem) : "";
$erro_mensagem = "";
$endereco_espaco = $eh_espaco ? endereco_espaco_formatado($promotor) : null;
$espaco_sem_endereco = $eh_espaco && $endereco_espaco === null;
$local_informado = !$eh_espaco && is_string($_POST["local_festa"] ?? null) ? trim($_POST["local_festa"]) : "";
$eh_conta_promotor = $usuario_logado && obterTipoConta($conexao, $id_cliente_logado) === "promotor";
$horario_inicio = trim((string) ($_POST["horario_inicio"] ?? ""));
$horario_fim = trim((string) ($_POST["horario_fim"] ?? ""));
$data_evento = trim((string) ($_POST["data_festa"] ?? ""));
$outro_dia = ($_POST["outro_dia"] ?? "") === "1";
$data_termino = $outro_dia ? trim((string) ($_POST["data_termino"] ?? "")) : "";
$aviso_reserva = "Este promotor já tem uma reserva no período escolhido. Escolha outras datas.";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // 2. TRAVA DE SEGURANÇA: só a conta de cliente envia a solicitação.
    if (!$usuario_logado) {
        $volta = "Cliente/solicitacao.php?pacote=" . (int) $id_pacote . "&id=" . (int) $id_promotor;
        header("Location: ../Login/login.php?voltar=" . rawurlencode($volta));
        exit();
    }

    if ($eh_conta_promotor) {
        $erros[] = "A conta de promotor só visualiza. A solicitação é enviada pela conta do cliente.";
    }

    $titulo_solicitacao = $mostrar_tipo_festa ? $tipo : mb_substr(trim($pacote["titulo"]), 0, 100);
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $horario = $horario_inicio . " - " . $horario_fim;
    $inicio_iso = data_evento_iso($data_evento);
    $fim_iso = $outro_dia ? data_evento_iso($data_termino) : $inicio_iso;
    $data_festa = $outro_dia ? "$data_evento a $data_termino" : $data_evento;
    $local_festa = $eh_espaco ? $endereco_espaco : $local_informado;
    $convidados = filter_var($entrada_convidados, FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 1, "max_range" => 2147483647],
    ]);
    if (is_string($entrada_convidados) && $convidados_digitados === "") {
        $erro_convidados = "Informe o número de convidados.";
    } elseif ($convidados === false) {
        $erro_convidados = "Informe um número inteiro de convidados maior que zero.";
    } elseif ($limite_convidados !== null && $convidados > $limite_convidados) {
        $erro_convidados = "Este promotor atende até $limite_convidados convidados.";
    }
    if ($erro_convidados !== "") {
        $erros[] = $erro_convidados;
    }
    $mensagem = trim($mensagem_digitada);
    if (!is_string($entrada_mensagem) || !mb_check_encoding($mensagem_digitada, "UTF-8")) {
        $erro_mensagem = "Informe uma mensagem válida.";
    } elseif (strlen(mb_convert_encoding($mensagem_digitada, "UTF-16LE", "UTF-8")) > 2 * $limite_mensagem) {
        // O maxlength do navegador também conta unidades UTF-16.
        $erro_mensagem = "A mensagem deve ter até $limite_mensagem caracteres.";
    }
    if ($erro_mensagem !== "") {
        $erros[] = $erro_mensagem;
    }

    if ($mostrar_tipo_festa) {
        if ($tipos_festa && !in_array($tipo, $tipos_festa, true)) {
            $erros[] = "Selecione um tipo de festa atendido pelo promotor.";
        } elseif (!$tipos_festa && ($tipo === "" || mb_strlen($tipo) > 100)) {
            $erros[] = "Informe o tipo de festa (até 100 caracteres).";
        }
    }
    if ($nome === "") {
        $erros[] = "Informe o nome.";
    }
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Informe um e-mail válido.";
    }
    if (!telefone_brasileiro_valido($telefone)) {
        $erros[] = "Informe um telefone válido com DDD.";
    }
    $horarios_validos = horario_evento_valido($horario_inicio) && horario_evento_valido($horario_fim);
    if (!$horarios_validos) {
        $erros[] = "Informe os horários de início e término no formato de 24 horas, como 14:30.";
    }
    if ($inicio_iso === null) {
        $erros[] = "Informe uma data de início válida no formato dia/mês/ano.";
    } elseif ($outro_dia && ($fim_iso === null || $fim_iso <= $inicio_iso)) {
        $erros[] = "Informe uma data de término posterior à data de início.";
    } elseif (!$outro_dia && $horarios_validos && $horario_fim < $horario_inicio) {
        $erros[] = "O evento passa da meia-noite. Informe uma data de término posterior à data de início.";
    } elseif (!$outro_dia && $horarios_validos && $horario_fim === $horario_inicio) {
        $erros[] = "O horário de término deve ser depois do início para um evento no mesmo dia.";
    }
    if ($espaco_sem_endereco) {
        $erros[] = "Este espaço ainda precisa cadastrar o endereço para receber solicitações.";
    } elseif ($local_festa === "") {
        $erros[] = "Informe o local da festa.";
    } elseif (mb_strlen($local_festa) > 255) {
        $erros[] = "O local da festa deve ter até 255 caracteres.";
    }

    if (!$erros && !$eh_conta_promotor) {
        $pacote_texto = $pacote["titulo"] . "\n" . $preco_pacote;
        $pacote_id_solicitacao = (int) $pacote["id"] > 0 ? (int) $pacote["id"] : null;
        $pacote_itens_envio = (string) ($pacote["itens"] ?? "");
        $id_enviado = 0;
        $salvou = false;

        $conexao->begin_transaction();
        try {
            $id_conta_promotor = bloquear_agenda_promotor($conexao, $id_promotor);
            $reservas = dias_reservados_promotor($conexao, $id_promotor);
            if ($id_conta_promotor === null) {
                $erros[] = "Este promotor não está cadastrado.";
            } elseif ($id_conta_promotor === $id_cliente_logado) {
                $erros[] = "Você não pode enviar uma solicitação para o próprio perfil.";
            } elseif (periodo_tem_reserva($reservas, $inicio_iso, $fim_iso)) {
                $erros[] = $aviso_reserva;
            } else {
                $stmt = $conexao->prepare(
                    "INSERT INTO solicitacoes
                    (id_promotor, id_cliente, nome_evento, nome_cliente, email, telefone, horario, data_festa, local_festa, numero_convidados, observacoes, pacote_selecionado, pacote_id, pacote_itens_envio, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente')"
                );
                $stmt->bind_param(
                    "iisssssssissis",
                    $id_promotor,
                    $id_cliente_logado,
                    $titulo_solicitacao,
                    $nome,
                    $email,
                    $telefone,
                    $horario,
                    $data_festa,
                    $local_festa,
                    $convidados,
                    $mensagem,
                    $pacote_texto,
                    $pacote_id_solicitacao,
                    $pacote_itens_envio
                );
                $id_enviado = $stmt->execute() ? $stmt->insert_id : 0;
            }
            $conexao->commit();
            $salvou = $id_enviado > 0;
            if ($salvou) {
                $_SESSION["id_solicitacao"] = $id_enviado;
            }
        } catch (mysqli_sql_exception $erro) {
            $conexao->rollback();
            error_log($erro->getMessage());
        }

        if ($salvou) {
            $_SESSION["status_solicitacao"] = "pendente";
            unset($_SESSION["avaliacao_promotor"][$id_promotor]);
            header("Location: perfil.php?id=" . $id_promotor . "&enviado=1");
            exit;
        } elseif (!$erros) {
            $erros[] = "Não foi possível enviar a solicitação. Tente novamente.";
        }
    }
}
$reservas ??= dias_reservados_promotor($conexao, $id_promotor);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Serviço | Nathus</title>
    <link rel="icon" type="image/png" href="../assets/favicon.png?v=2">
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="../home/css/vitrine.css?v=<?php echo filemtime(__DIR__ . "/../home/css/vitrine.css"); ?>">
    <link rel="stylesheet" href="../Includes/css/icones-solicitacao.css?v=<?= filemtime(__DIR__ . '/../Includes/css/icones-solicitacao.css') ?>">
    <link rel="stylesheet" href="../Includes/css/botao-voltar.css?v=<?= filemtime(__DIR__ . '/../Includes/css/botao-voltar.css') ?>">
    <link rel="stylesheet" href="css/solicitacao.css?v=<?php echo filemtime(__DIR__ . "/css/solicitacao.css"); ?>">
</head>
<body class="vt">
    <?php
    $base_url = "../";
    $vt_inicio = "../home/homeAnt.php";
    $vt_acao = "../home/salvar-conta.php";
    $vt_voltar = "../Cliente/solicitacao.php?pacote=" . (int) $id_pacote . "&id=" . (int) $id_promotor;
    include __DIR__ . "/../home/topo-vitrine.php";
    ?>

<main class="pagina" id="pagina">
    <section class="form" id="formulario">
        <a class="botao-voltar" href="perfil.php?id=<?php echo $id_promotor; ?>">Voltar</a>
        <div class="form-cabecalho">
            <div>
                <h1>Solicitar Serviço</h1>
                <p>Preencha o formulário de acordo com as informações da sua festa</p>
            </div>
        </div>

        <?php if ($erros): ?>
            <ul class="lista-erros" id="lista-erros">
                <?php foreach ($erros as $erro): ?>
                    <li><?php echo htmlspecialchars($erro); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" id="form-solicitacao" data-reexibido="<?= $_SERVER['REQUEST_METHOD'] === 'POST' ? '1' : '0' ?>"
            data-reservas="<?= htmlspecialchars(json_encode($reservas)) ?>">
            <input type="hidden" name="pacote" value="<?php echo (int) $pacote["id"]; ?>">

            <fieldset class="grupo-form">
            <legend>Dados de contato</legend>
            <label class="label" for="nome">Nome</label>
            <input class="field" id="nome" name="nome" type="text" placeholder="Digite seu nome" value="<?php echo htmlspecialchars($_POST["nome"] ?? $nome_cliente_form); ?>" required>

            <label class="label" for="email">Email</label>
            <input class="field" id="email" name="email" type="email" placeholder="Digite seu email" value="<?php echo htmlspecialchars($_POST["email"] ?? $email_cliente_form); ?>" required>

            <label class="label" for="telefone">Telefone</label>
            <input class="field" id="telefone" name="telefone" type="tel" data-mascara="telefone" maxlength="19" autocomplete="tel" placeholder="Ex: (21) 90000-0000" value="<?php echo htmlspecialchars($_POST["telefone"] ?? $telefone_cliente_form); ?>" required>
            </fieldset>

            <fieldset class="grupo-form">
            <legend>Dados do evento</legend>
            <?php if ($mostrar_tipo_festa): ?>
                <label class="label label--tipo" for="tipo_festa">Tipo de festa</label>
                <?php if ($tipos_festa): ?>
                    <select class="field" id="tipo_festa" name="tipo_festa" required>
                        <option value="">Selecione o tipo de festa</option>
                        <?php foreach ($tipos_festa as $opcao): ?>
                            <option value="<?= htmlspecialchars($opcao) ?>" <?= $tipo === $opcao ? "selected" : "" ?>>
                                <?= htmlspecialchars($opcao) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input class="field" id="tipo_festa" name="tipo_festa" type="text" maxlength="100"
                        placeholder="Ex.: Casamento" value="<?= htmlspecialchars($tipo) ?>" required>
                <?php endif; ?>
            <?php endif; ?>

            <label class="label label--data" for="data_festa">Data de início</label>
            <div class="campo-data">
                <input class="field" id="data_festa" name="data_festa" type="text" inputmode="numeric" data-mascara="data"
                    maxlength="10" placeholder="DD/MM/AAAA" value="<?= htmlspecialchars($data_evento) ?>" aria-describedby="aviso-reserva" required>
                <button class="btn-calendario" type="button" aria-label="Abrir calendário da data de início" aria-haspopup="dialog" aria-controls="calendario-evento" hidden></button>
            </div>
            <div class="row">
                <div>
                    <label class="label label--horario" for="horario_inicio">Horário de início</label>
                    <input class="field" id="horario_inicio" name="horario_inicio" type="text" inputmode="numeric" data-mascara="horario"
                        maxlength="5" placeholder="Ex: 14:30" value="<?= htmlspecialchars($horario_inicio) ?>" required>
                </div>
                <div>
                    <label class="label label--horario" for="horario_fim">Horário de término</label>
                    <input class="field" id="horario_fim" name="horario_fim" type="text" inputmode="numeric" data-mascara="horario"
                        maxlength="5" placeholder="Ex: 22:00" value="<?= htmlspecialchars($horario_fim) ?>" required>
                </div>
            </div>
            <label class="outro-dia">
                <input type="checkbox" id="outro_dia" name="outro_dia" value="1" aria-controls="campo-data-termino"
                    aria-describedby="aviso-outro-dia" <?= $outro_dia ? "checked" : "" ?>>
                Termina em outro dia
            </label>
            <p class="aviso-outro-dia" id="aviso-outro-dia" role="status" hidden></p>
            <div id="campo-data-termino">
                <label class="label label--data" for="data_termino">Data de término</label>
                <div class="campo-data">
                    <input class="field" id="data_termino" name="data_termino" type="text" inputmode="numeric" data-mascara="data"
                        maxlength="10" placeholder="DD/MM/AAAA" value="<?= htmlspecialchars($data_termino) ?>" aria-describedby="aviso-reserva">
                    <button class="btn-calendario" type="button" aria-label="Abrir calendário da data de término" aria-haspopup="dialog" aria-controls="calendario-evento" hidden></button>
                </div>
            </div>
            <p class="aviso-reserva" id="aviso-reserva" role="status"></p>

            <div class="row">
                <div>
                    <?php if ($eh_espaco): ?>
                        <p class="label label--local" id="rotulo-local-festa">Endereço do espaço</p>
                        <p class="field endereco-fixo" aria-labelledby="rotulo-local-festa"><?php echo htmlspecialchars($endereco_espaco ?? "Endereço ainda não cadastrado pelo espaço."); ?></p>
                    <?php else: ?>
                        <label class="label label--local" for="local_festa">Local da festa</label>
                        <input class="field" id="local_festa" name="local_festa" type="text" maxlength="255" placeholder="Ex: Paulista, 1235 - São Paulo" value="<?php echo htmlspecialchars($local_informado); ?>" required>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="label label--convidados" for="convidados">Número de convidados</label>
                    <input class="field" id="convidados" name="convidados" type="number" min="1" step="1" required
                        placeholder="Ex.: 50"
                        value="<?= htmlspecialchars($convidados_digitados) ?>"
                        <?php if ($limite_convidados !== null): ?>max="<?= $limite_convidados ?>" aria-describedby="limite-convidados"<?php endif; ?>
                        <?php if ($erro_convidados !== ""): ?>aria-invalid="true"<?php endif; ?>>
                </div>
            </div>
            <?php if ($limite_convidados !== null): ?>
                <p class="limite-convidados" id="limite-convidados" aria-live="polite">Até <?= $limite_convidados ?> convidados.</p>
            <?php endif; ?>

            <label class="label label--mensagem" for="mensagem">Mensagem/pedido</label>
            <textarea class="field field--area" id="mensagem" name="mensagem" maxlength="<?= $limite_mensagem ?>"
                aria-describedby="limite-mensagem" <?= $erro_mensagem !== "" ? 'aria-invalid="true"' : '' ?>
                placeholder="Conte mais do que você imagina para o seu evento..."><?= htmlspecialchars($mensagem_digitada) ?></textarea>
            <p class="limite-mensagem<?= $erro_mensagem !== "" ? ' limite-excedido' : '' ?>" id="limite-mensagem">
                <?= htmlspecialchars($erro_mensagem ?: "Até $limite_mensagem caracteres.") ?>
            </p>
            </fieldset>

            <!-- 3. BOTÃO INTELIGENTE: Alterna entre Enviar ou Login -->
            <?php if ($espaco_sem_endereco): ?>
                <?php if (!$erros): ?>
                    <p class="lista-erros">O espaço precisa cadastrar o endereço para receber solicitações.</p>
                <?php endif; ?>
            <?php elseif ($eh_conta_promotor): ?>
                <p class="lista-erros">A conta de promotor só visualiza. Quem envia a solicitação é o cliente.</p>
            <?php elseif ($usuario_logado): ?>
                <button class="btn-submit" type="submit">Enviar Solicitação</button>
            <?php else: ?>
                <a href="../Login/login.php?voltar=<?php echo rawurlencode('Cliente/solicitacao.php?pacote=' . (int) $id_pacote . '&id=' . (int) $id_promotor); ?>" class="btn-submit" style="display: block; text-align: center; text-decoration: none; box-sizing: border-box; padding: 15px;">
                    Fazer Login para Solicitar
                </a>
            <?php endif; ?>

        </form>
    </section>

<aside class="lateral" id="lateral">
        <div class="card-promotor" id="cartao-promotor">
            <div class="capa-lateral">
                <?php if ($banner): ?>
                    <img src="<?php echo htmlspecialchars($banner); ?>" alt="">
                <?php endif; ?>
            </div>
            <img class="foto-lateral <?php echo $tem_foto ? "" : "foto-vazia"; ?>" src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="<?php echo htmlspecialchars($nome_promotor); ?>">
            <h2><?php echo htmlspecialchars($nome_promotor); ?></h2>
            <p class="item-meta">
                <img src="<?php echo $base_url; ?>assets/estrela_perfilprom.svg" alt="">
                <?php echo $nota_media; ?> (<?php echo $total_avaliacoes; ?> avaliações)
            </p>
            <p class="item-meta">
                <img src="<?php echo $base_url; ?>assets/local_perfilprom.svg" alt="">
                <?php echo htmlspecialchars($localizacao); ?>
            </p>
            <?php
            $telefone_perfil = $promotor['telefone_promotor'] ?? '';
            include __DIR__ . '/../Includes/telefone-perfil.php';
            ?>
        </div>

        <div class="box" id="pacote-selecionado">
            <h3>Pacote Selecionado</h3>
            <article class="card-pkg" id="pacote-<?php echo (int) $pacote["id"]; ?>">
                <h3><?php echo htmlspecialchars($pacote["titulo"]); ?></h3>
                <?php if ($imagem_pacote !== ""): ?>
                <div class="pkg-imagem">
                    <img src="<?php echo htmlspecialchars($imagem_pacote); ?>" alt="">
                </div>
                <?php endif; ?>
                <?php include __DIR__ . '/../Includes/itens-pacote.php'; ?>
                <div class="pkg-preco">
                    <img src="<?php echo $base_url; ?>assets/perfil-bolsa.png" alt="">
                    <?php echo htmlspecialchars($preco_pacote); ?>
                </div>
            </article>
        </div>
    </aside>
</main>
<dialog id="calendario-evento" class="calendario-evento" aria-labelledby="calendario-titulo"
    aria-describedby="calendario-legenda">
    <div class="calendario-cabecalho">
        <h2 id="calendario-titulo">Escolher data</h2>
        <button type="button" data-calendario-fechar aria-label="Fechar calendário">×</button>
    </div>
    <div class="calendario-controles">
        <button type="button" data-mes-anterior aria-label="Mês anterior">‹</button>
        <select aria-label="Mês" data-calendario-mes></select>
        <input type="number" aria-label="Ano" min="1" max="9999" inputmode="numeric" data-calendario-ano>
        <button type="button" data-mes-seguinte aria-label="Próximo mês">›</button>
    </div>
    <div class="calendario-semana" aria-hidden="true">
        <span>Dom</span><span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span>
    </div>
    <div class="calendario-dias" data-calendario-dias></div>
    <div class="calendario-rodape" id="calendario-legenda">
        <span class="calendario-legenda calendario-legenda--hoje">Dia atual</span>
        <span class="calendario-legenda calendario-legenda--reservado">Dia reservado</span>
    </div>
</dialog>
<script defer src="js/calendario-evento.js?v=<?= filemtime(__DIR__ . '/js/calendario-evento.js') ?>"></script>
<?php require_once __DIR__ . "/../Includes/mascaras.php"; ?>
<?php include __DIR__ . "/../home/conta-modal.php"; ?>
<script>
(function() {
    const STORAGE_KEY = 'nathus_solicitacao_rascunho';
    const promotorId = <?= (int) $id_promotor ?>;
    const form = document.getElementById('form-solicitacao');
    if (!form) return;
    const outroDia = form.elements.outro_dia;
    const horarioInicio = form.elements.horario_inicio;
    const horarioFim = form.elements.horario_fim;
    const dataInicio = form.elements.data_festa;
    const termino = form.elements.data_termino;
    const campoTermino = document.getElementById('campo-data-termino');
    const avisoOutroDia = document.getElementById('aviso-outro-dia');
    const reservas = JSON.parse(form.dataset.reservas);
    const avisoReserva = document.getElementById('aviso-reserva');
    const textoReserva = <?= json_encode($aviso_reserva, JSON_HEX_TAG) ?>;
    const convidados = form.elements.convidados;
    const avisoCapacidade = document.getElementById('limite-convidados');
    const textoCapacidade = avisoCapacidade?.textContent;
    let convidadosAceitos = '';

    function atualizarCapacidade() {
        if (convidados.validity.valid) convidados.removeAttribute('aria-invalid');
        if (!avisoCapacidade) return;
        const excedeu = convidados.validity.rangeOverflow;
        if (excedeu) {
            convidados.value = convidadosAceitos;
        } else if (convidados.validity.valid || convidados.validity.valueMissing) {
            convidadosAceitos = convidados.value;
        }
        const aviso = excedeu ? `O limite é de ${convidados.max} convidados.` : textoCapacidade;
        avisoCapacidade.classList.toggle('limite-excedido', excedeu);
        if (avisoCapacidade.textContent !== aviso) avisoCapacidade.textContent = aviso;
    }
    convidados.addEventListener('input', atualizarCapacidade);

    const mensagem = form.elements.mensagem;
    const limiteMensagem = document.getElementById('limite-mensagem');
    function atualizarLimiteMensagem() {
        const excedeu = mensagem.value.length > mensagem.maxLength;
        const ajuda = `Até ${mensagem.maxLength} caracteres.`;
        const erro = `A mensagem deve ter até ${mensagem.maxLength} caracteres.`;
        mensagem.setCustomValidity(excedeu ? erro : '');
        if (excedeu) mensagem.setAttribute('aria-invalid', 'true');
        else mensagem.removeAttribute('aria-invalid');
        limiteMensagem.classList.toggle('limite-excedido', excedeu);
        const aviso = excedeu ? erro : ajuda;
        if (limiteMensagem.textContent !== aviso) limiteMensagem.textContent = aviso;
    }
    mensagem.addEventListener('input', atualizarLimiteMensagem);

    function horarioValido(valor) {
        return /^(?:[01]\d|2[0-3]):[0-5]\d$/.test(valor);
    }

    function dataComparavel(valor) {
        const partes = valor.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        return partes ? `${partes[3]}-${partes[2]}-${partes[1]}` : '';
    }

    function atualizarTermino() {
        const horariosValidos = horarioValido(horarioInicio.value) && horarioValido(horarioFim.value);
        const passaDaMeiaNoite = horariosValidos && horarioFim.value < horarioInicio.value;
        if (passaDaMeiaNoite) {
            outroDia.checked = true;
            outroDia.setAttribute('aria-disabled', 'true');
        } else {
            outroDia.removeAttribute('aria-disabled');
        }
        avisoOutroDia.hidden = !passaDaMeiaNoite;
        const aviso = passaDaMeiaNoite ? 'O evento passa da meia-noite. Informe a data de término.' : '';
        if (avisoOutroDia.textContent !== aviso) avisoOutroDia.textContent = aviso;

        campoTermino.hidden = !outroDia.checked;
        termino.disabled = !outroDia.checked;
        termino.required = outroDia.checked;

        const inicio = dataComparavel(dataInicio.value);
        const fim = dataComparavel(termino.value);
        const inicioReservado = reservas.some(([de, ate]) => inicio && de <= inicio && inicio <= ate);
        const fimReservado = outroDia.checked && reservas.some(([de, ate]) => fim && de <= fim && fim <= ate);
        const periodoReservado = outroDia.checked && inicio && fim && inicio < fim
            && reservas.some(([de, ate]) => inicio <= ate && fim >= de);
        const erroTermino = outroDia.checked && inicio && fim && fim <= inicio
            ? 'Informe uma data de término posterior à data de início.'
            : fimReservado || (periodoReservado && !inicioReservado) ? textoReserva : '';
        dataInicio.setCustomValidity(inicioReservado ? textoReserva : '');
        termino.setCustomValidity(erroTermino);
        const mensagemReserva = inicioReservado || fimReservado || periodoReservado ? textoReserva : '';
        if (avisoReserva.textContent !== mensagemReserva) avisoReserva.textContent = mensagemReserva;
        horarioFim.setCustomValidity(!outroDia.checked && horariosValidos && horarioFim.value === horarioInicio.value
            ? 'O horário de término deve ser depois do início para um evento no mesmo dia.' : '');
    }
    outroDia.addEventListener('click', (evento) => {
        if (outroDia.getAttribute('aria-disabled') === 'true') evento.preventDefault();
    });
    outroDia.addEventListener('change', atualizarTermino);
    for (const evento of ['input', 'change']) {
        window.addEventListener(evento, (e) => {
            if ([horarioInicio, horarioFim, dataInicio, termino].includes(e.target)) atualizarTermino();
        });
    }

    const CAMPOS_RASCUNHO = [
        'tipo_festa', 'outro_dia', 'data_termino',
        'horario_inicio', 'horario_fim', 'data_festa', 'local_festa',
        'convidados', 'mensagem'
    ];
    function salvar() {
        const dados = { promotorId };
        CAMPOS_RASCUNHO.forEach(name => {
            const el = form.querySelector('[name="' + name + '"]');
            if (el && !el.disabled) dados[name] = el.type === 'checkbox' ? el.checked : el.value;
        });
        dados.timestamp = Date.now();
        localStorage.setItem(STORAGE_KEY, JSON.stringify(dados));
    }

    function restaurar() {
        try {
            const salvo = localStorage.getItem(STORAGE_KEY);
            if (!salvo) return;
            const dados = JSON.parse(salvo);
            if (Date.now() - dados.timestamp > 7 * 24 * 60 * 60 * 1000) {
                localStorage.removeItem(STORAGE_KEY);
                return;
            }
            const horarioAntigo = typeof dados.horario === 'string'
                ? dados.horario.match(/^(\d{2}:\d{2}) - (\d{2}:\d{2})$/) : null;
            if (horarioAntigo) {
                dados.horario_inicio ??= horarioAntigo[1];
                dados.horario_fim ??= horarioAntigo[2];
            }
            if (typeof dados.data_festa === 'string') {
                dados.data_festa = dados.data_festa.replace(/^(\d{4})-(\d{2})-(\d{2})$/, '$3/$2/$1');
            }
            CAMPOS_RASCUNHO.forEach(name => {
                if (name === 'tipo_festa' && dados.promotorId !== promotorId) return;
                const el = form.querySelector('[name="' + name + '"]');
                if (el && dados[name] !== undefined) {
                    if (el.type === 'checkbox') el.checked = dados[name] === true;
                    else el.value = dados[name];
                }
            });
        } catch (e) {
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    let timeout;
    form.addEventListener('input', function(e) {
        if (CAMPOS_RASCUNHO.includes(e.target.name)) {
            clearTimeout(timeout);
            timeout = setTimeout(salvar, 300);
        }
    });

    form.addEventListener('change', function(e) {
        if (CAMPOS_RASCUNHO.includes(e.target.name)) salvar();
    });

    // Força save ao clicar no botão de login (caso não tenha digitado recentemente)
    const btnLogin = form.querySelector('a[href*="login.php"]');
    if (btnLogin) {
        btnLogin.addEventListener('click', salvar);
    }

    if (form.dataset.reexibido !== '1') restaurar();
    atualizarTermino();
    atualizarCapacidade();
    if (mensagem.getAttribute('aria-invalid') !== 'true' || mensagem.value.length > mensagem.maxLength) atualizarLimiteMensagem();
})();
</script>
</body>
</html>
