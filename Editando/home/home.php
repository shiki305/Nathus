<?php
session_start();
$base_url = '../';
$mostrar_busca = false;

include_once __DIR__ . '/../Includes/conexao.php';
include_once __DIR__ . '/../Includes/promotor.php';
include_once __DIR__ . '/../Includes/cidades-rj.php';
require_once __DIR__ . '/../Includes/moeda.php';
require_once __DIR__ . '/../Includes/tipos-festa.php';
$campo = $_GET['campo'] ?? 'organizadores';
if (!in_array($campo, ['organizadores', 'buffet', 'espaco'], true)) {
    $campo = 'organizadores';
}
$busca = trim($_GET['busca'] ?? '');
$secao_ativa = $campo;

function vt_esc($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function vt_bate($texto, $termo) {
    if ($termo === '') return true;
    $texto = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
    $termo = function_exists('mb_strtolower') ? mb_strtolower($termo, 'UTF-8') : strtolower($termo);
    return strpos($texto, $termo) !== false;
}

function vt_menu($nome, $placeholder, $atual, $opcoes, $com_busca = false) {
    $escolhido = isset($opcoes[$atual]) ? $opcoes[$atual] : '';
    $vazio = $escolhido === '';
    ?>
    <div class="vt-menu<?= $vazio ? ' vt-vazio' : '' ?><?= $com_busca ? ' vt-menu-com-busca' : '' ?>">
        <button type="button" class="vt-menu-botao" aria-haspopup="listbox" aria-expanded="false">
            <span class="vt-menu-valor" data-placeholder="<?= vt_esc($placeholder) ?>"><?= vt_esc($vazio ? $placeholder : $escolhido) ?></span>
            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <input type="hidden" name="<?= vt_esc($nome) ?>" value="<?= vt_esc($vazio ? '' : $atual) ?>">
        <div class="vt-menu-painel" hidden>
            <?php if ($com_busca): ?>
                <div class="vt-menu-busca">
                    <input type="search" placeholder="Buscar cidade" autocomplete="off" aria-label="Buscar cidade">
                </div>
            <?php endif; ?>
            <ul class="vt-menu-lista" role="listbox">
                <li role="option" data-valor=""<?= $vazio ? ' aria-selected="true"' : '' ?>><?= vt_esc($com_busca ? 'Todas as cidades' : $placeholder) ?></li>
                <?php foreach ($opcoes as $valor => $texto): ?>
                    <li role="option" data-valor="<?= vt_esc($valor) ?>"<?= (string) $atual === (string) $valor ? ' aria-selected="true"' : '' ?>><?= vt_esc($texto) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php if ($com_busca): ?>
                <p class="vt-menu-sem-resultados" role="status" hidden>Nenhuma cidade encontrada.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function vt_iniciais($nome) {
    $partes = preg_split('/\s+/u', trim($nome));
    $ini = mb_strtoupper(mb_substr($partes[0] ?? '?', 0, 1, 'UTF-8'), 'UTF-8');
    if (!empty($partes[1])) {
        $ini .= mb_strtoupper(mb_substr($partes[1], 0, 1, 'UTF-8'), 'UTF-8');
    }
    return $ini;
}

$somente_ver = !empty($_SESSION['usuario_id']) && obterTipoConta($conexao, (int) $_SESSION['usuario_id']) === 'promotor';

function vt_href_perfil($id) {
    $id = (int) $id;
    if ($id <= 0) return '';
    global $campo;
    $de = in_array($campo ?? '', ['organizadores', 'buffet', 'espaco'], true) ? $campo : 'organizadores';
    return '../Cliente/perfil.php?id=' . $id . '&ver_como_cliente=1&campo=' . rawurlencode($de);
}

$especialidades_evento = array_map(static fn($tipo) => $tipo['label'], TIPOS_FESTA);
$especialidades_buffet = [
    'massas' => 'Massas',
    'infantil' => 'Buffet infantil',
    'drinks' => 'Buffet de drinks',
    'pratos' => 'Pratos principais',
    'geral' => 'Buffet geral',
];

function vt_teto_legado($faixa): string {
    if (!is_string($faixa)) return '';
    $texto = trim(str_ireplace('R$', '', $faixa));
    if (preg_match('/^(?:a partir de|mín(?:imo)?|min(?:imo)?|desde)\s/iu', $texto)) {
        return '';
    }
    if (preg_match('/^(?:[\d.,]+\s*(?:-|–|até|ate|a)|até|ate|máx(?:imo)?|max(?:imo)?)\s*([\d.,]+)$/iu', $texto, $m)) {
        return $m[1];
    }
    return $texto;
}

$cidade = trim($_GET['cidade'] ?? '');
$entrada_preco = $_GET['preco_max'] ?? vt_teto_legado($_GET['faixa'] ?? '');
$preco_max = moeda_br_decimal($entrada_preco);
$erro_preco = $preco_max === null && (!is_string($entrada_preco) || trim($entrada_preco) !== '')
    ? 'Informe um valor em reais, como 1.500,00.'
    : '';
$preco_max_txt = $preco_max !== null
    ? number_format((float) $preco_max, 2, ',', '.')
    : (is_string($entrada_preco) ? $entrada_preco : '');
$especialidade = trim($_GET['especialidade'] ?? '');
$tipo_festa = trim($_GET['tipo_festa'] ?? '');
$convidados = (int) preg_replace('/\D/', '', (string) ($_GET['convidados'] ?? ''));
if ($convidados < 0 || $convidados > 100000) $convidados = 0;
if (!isset($especialidades_buffet[$especialidade])) $especialidade = '';
if (!isset($especialidades_evento[$tipo_festa])) $tipo_festa = '';

if ($campo === 'organizadores') {
    $condicao_categoria = "EXISTS (SELECT 1 FROM promotor_campo pc WHERE pc.id_promotor = p.id AND LOWER(pc.campo) IN ('organizacao', 'organização'))";
    $titulo = 'Nathus | Organizadores';
    $frase_padrao = 'Organização de eventos';
} elseif ($campo === 'buffet') {
    $condicao_categoria = "EXISTS (SELECT 1 FROM promotor_campo pc WHERE pc.id_promotor = p.id AND LOWER(pc.campo) = 'buffet')";
    $titulo = 'Nathus | Buffets';
    $frase_padrao = 'Buffet para eventos';
} else {
    $condicao_categoria = "EXISTS (SELECT 1 FROM promotor_campo pc WHERE pc.id_promotor = p.id AND LOWER(pc.campo) IN ('local', 'espaco', 'espaço'))";
    $titulo = 'Nathus | Espaços';
    $frase_padrao = 'Espaço para eventos';
}

function vt_cidade_chave($nome) {
    return strtr(mb_strtolower(trim($nome), 'UTF-8'), [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'é' => 'e', 'ê' => 'e',
        'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c',
    ]);
}

$municipios_rj = [];
foreach (cidades_do_rio() as $nome_cidade) {
    $municipios_rj[vt_cidade_chave($nome_cidade)] = $nome_cidade;
}
$cidades = [];
$resCidades = $conexao->query(
    "SELECT DISTINCT TRIM(p.cidade) AS cidade
     FROM promotor p
     WHERE p.cidade IS NOT NULL AND TRIM(p.cidade) <> ''
       AND UPPER(TRIM(p.estado)) = 'RJ'
       AND {$condicao_categoria}
     ORDER BY cidade"
);
if ($resCidades) {
    while ($linha = $resCidades->fetch_assoc()) {
        $chave = vt_cidade_chave($linha['cidade']);
        if (isset($municipios_rj[$chave])) {
            $cidades[$chave] = $municipios_rj[$chave];
        }
    }
}
ksort($cidades);
$cidade = $cidades[vt_cidade_chave($cidade)] ?? '';

$where = [$condicao_categoria];
$types = '';
$params = [];
if ($cidade !== '') {
    $where[] = "TRIM(p.cidade) = ? AND UPPER(TRIM(p.estado)) = 'RJ'";
    $types .= 's';
    $params[] = $cidade;
}
if ($preco_max !== null) {
    $where[] = 'precos.preco_minimo <= ?';
    $types .= 'd';
    $params[] = $preco_max;
}
if ($busca !== '') {
    $like = '%' . $busca . '%';
    $where[] = '(p.nome_fantasia LIKE ? OR c.nome LIKE ? OR p.cidade LIKE ? OR p.frase_destaque LIKE ?)';
    $types .= 'ssss';
    array_push($params, $like, $like, $like, $like);
}
if ($campo === 'buffet' && $especialidade !== '') {
    if ($especialidade === 'geral') {
        $where[] = "EXISTS (SELECT 1 FROM promotor_especialidade pe WHERE pe.id_promotor = p.id AND pe.tipo = 'geral')";
    } else {
        $where[] = "EXISTS (SELECT 1 FROM promotor_especialidade pe WHERE pe.id_promotor = p.id AND pe.tipo IN (?, 'geral'))";
        $types .= 's';
        $params[] = $especialidade;
    }
}
if ($campo === 'organizadores' && $tipo_festa !== '') {
    $where[] = 'EXISTS (SELECT 1 FROM promotor_especialidade pe WHERE pe.id_promotor = p.id AND pe.tipo = ?)';
    $types .= 's';
    $params[] = $tipo_festa;
}
if ($campo === 'espaco' && $convidados > 0) {
    $where[] = 'p.capacidade_maxima IS NOT NULL AND p.capacidade_maxima >= ?';
    $types .= 'i';
    $params[] = $convidados;
}

$sql = "SELECT DISTINCT p.id, p.nome_fantasia, p.foto_perfil, p.banner, p.cidade, p.estado,
               p.nota_media, p.total_avaliacoes, p.frase_destaque, precos.preco_minimo,
               c.nome AS nome_cliente
        FROM promotor p
        LEFT JOIN (" . SQL_PRECOS_MINIMOS . ") precos ON precos.id_promotor = p.id
        LEFT JOIN cliente c ON c.id = p.id_cliente
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.id DESC";
$stmtLista = $conexao->prepare($sql);
if ($stmtLista && $types !== '') {
    $refs = [];
    foreach ($params as $i => &$valor) {
        $refs[$i] = &$valor;
    }
    $stmtLista->bind_param($types, ...$refs);
    unset($valor);
}
if ($stmtLista) {
    $stmtLista->execute();
    $reais = $stmtLista->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $reais = [];
}

$itens = [];
foreach ($reais as $p) {
    $nome = trim((string) ($p['nome_fantasia'] ?? '')) !== '' ? $p['nome_fantasia'] : ($p['nome_cliente'] ?? 'Promotor');
    $local = trim(($p['cidade'] ?? '') . (!empty($p['estado']) ? ', ' . $p['estado'] : ''));
    $frase = trim((string) ($p['frase_destaque'] ?? ''));
    $foto = banner_publico_promotor($p['banner'] ?? '');
    $avatar = midia_publica($p['foto_perfil'] ?? '', ['perfil-promotor']);
    $itens[] = [
        'nome' => $nome,
        'frase' => $frase !== '' ? $frase : $frase_padrao,
        'local' => $local !== '' ? $local : 'Localização não informada',
        'nota' => (float) ($p['nota_media'] ?? 0),
        'avaliacoes' => (int) ($p['total_avaliacoes'] ?? 0),
        'preco_minimo' => $p['preco_minimo'],
        'foto' => $foto,
        'avatar' => $avatar,
        'href' => vt_href_perfil($p['id']),
    ];
}

$filtros_ativos = $cidade !== '' || $preco_max !== null || $erro_preco !== '' || $busca !== '' || $especialidade !== '' || $tipo_festa !== '' || $convidados > 0;
$url_categoria = 'home.php?campo=' . rawurlencode($campo);
$total = count($itens);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= vt_esc($titulo) ?></title>
    <link rel="icon" type="image/png" href="../assets/favicon.png?v=2">
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . '/../Includes/css/global.css'); ?>">
    <link rel="stylesheet" href="css/vitrine.css?v=<?php echo filemtime(__DIR__ . '/css/vitrine.css'); ?>">
    <link rel="stylesheet" href="../Includes/css/botao-voltar.css?v=<?= filemtime(__DIR__ . '/../Includes/css/botao-voltar.css') ?>">
</head>
<body class="vt">
    <?php $vt_abas = true; include __DIR__ . '/topo-vitrine.php'; ?>

    <main class="vt-lista">
        <?php if ($filtros_ativos): ?>
            <a class="botao-voltar" href="<?= vt_esc($url_categoria) ?>">Voltar</a>
        <?php else: ?>
            <a class="botao-voltar" href="homeAnt.php">Voltar</a>
        <?php endif; ?>

        <form class="vt-barra" method="get" action="home.php" autocomplete="off">
            <input type="hidden" name="campo" value="<?= vt_esc($campo) ?>">
            <?php if ($busca !== ''): ?>
                <input type="hidden" name="busca" value="<?= vt_esc($busca) ?>">
            <?php endif; ?>
            <div class="vt-barra-campo">
                <?php if ($campo === 'buffet'): ?>
                    <span>Especialidade</span>
                    <?php vt_menu('especialidade', 'Qual especialidade?', $especialidade, $especialidades_buffet); ?>
                <?php elseif ($campo === 'organizadores'): ?>
                    <span>Tipo de festa</span>
                    <?php vt_menu('tipo_festa', 'Qual tipo de festa?', $tipo_festa, $especialidades_evento); ?>
                <?php else: ?>
                    <label for="vt-capacidade">Capacidade</label>
                    <input id="vt-capacidade" type="text" name="convidados" inputmode="numeric" placeholder="Quantos convidados?" value="<?= $convidados > 0 ? (int) $convidados : '' ?>">
                <?php endif; ?>
            </div>
            <label class="vt-barra-campo">
                <span>Preço por evento até</span>
                <span class="vt-preco-valor">
                    <span aria-hidden="true">R$</span>
                    <input type="text" name="preco_max" inputmode="decimal" placeholder="Sem limite"
                        aria-label="Preço por evento até, em reais" value="<?= vt_esc($preco_max_txt) ?>" data-mascara="moeda"
                        <?php if ($erro_preco !== ''): ?>aria-invalid="true" aria-describedby="vt-erro-preco"<?php endif; ?>>
                </span>
            </label>
            <div class="vt-barra-campo">
                <span>Cidade</span>
                <?php
                    $opcoes_cidade = [];
                    foreach ($cidades as $nome_cidade) {
                        $opcoes_cidade[$nome_cidade] = $nome_cidade;
                    }
                    vt_menu('cidade', 'Em qual cidade?', $cidade, $opcoes_cidade, true);
                ?>
            </div>
            <button type="submit" aria-label="Buscar">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            </button>
        </form>
        <?php if ($erro_preco !== ''): ?>
            <p id="vt-erro-preco" class="vt-erro-preco" role="alert"><?= vt_esc($erro_preco) ?></p>
        <?php endif; ?>

        <header class="vt-cabeca">
            <div>
                <h1>Profissionais em destaque</h1>
                <p>Explore perfis, confira avaliações e encontre o parceiro ideal para seu evento.</p>
            </div>
            <span class="vt-contagem"><?= $total ?> <?= $total === 1 ? 'profissional encontrado' : 'profissionais encontrados' ?></span>
        </header>
        <?php if ($somente_ver): ?>
            <p class="pro-aviso">Como promotor você só visualiza. Entrar no perfil e solicitar é da conta do cliente.</p>
        <?php endif; ?>

        <section class="vt-profissionais" aria-label="Profissionais">
            <?php if ($total === 0): ?>
                <p class="pro-vazio"><?= $filtros_ativos ? 'Nenhum profissional encontrado para esses filtros.' : 'Nenhum promotor cadastrado nesta categoria ainda.' ?></p>
            <?php endif; ?>

            <?php foreach ($itens as $item): ?>
                <?php
                    $tag = $item['href'] !== '' ? 'a' : 'article';
                    $attr = $item['href'] !== '' ? ' href="' . vt_esc($item['href']) . '"' : '';
                ?>
                <<?= $tag ?> class="pro-card"<?= $attr ?>>
                    <div class="pro-capa">
                        <?php if ($item['foto'] !== ''): ?>
                            <img src="<?= vt_esc($item['foto']) ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <div class="pro-ident">
                        <span class="pro-avatar">
                            <?php if ($item['avatar'] !== ''): ?>
                                <img src="<?= vt_esc($item['avatar']) ?>" alt="">
                            <?php else: ?>
                                <?= vt_esc(vt_iniciais($item['nome'])) ?>
                            <?php endif; ?>
                        </span>
                        <div class="pro-nome">
                            <h2><?= vt_esc($item['nome']) ?></h2>
                            <span class="pro-selo" aria-label="Verificado">
                                <svg viewBox="0 0 16 16" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M3 8.2l3.1 3.1L13 4.5"/></svg>
                            </span>
                        </div>
                    </div>
                    <p class="pro-frase"><?= vt_esc($item['frase']) ?></p>
                    <div class="pro-preco">
                        <span>Preço mínimo por evento</span>
                        <strong><?= $item['preco_minimo'] !== null
                            ? 'R$ ' . number_format((float) $item['preco_minimo'], 2, ',', '.')
                            : 'Sob consulta' ?></strong>
                    </div>
                    <div class="pro-meta">
                        <span class="pro-nota">
                            <svg viewBox="0 0 24 24" width="13" height="13" aria-hidden="true"><path fill="#f6b423" d="M12 2.6l2.7 5.6 6.2.9-4.5 4.3 1.1 6.1L12 16.8 6.5 19.5l1.1-6.1L3.1 9.1l6.2-.9L12 2.6z"/></svg>
                            <?php if ($item['nota'] > 0): ?>
                                <strong><?= number_format($item['nota'], 1, ',', '.') ?></strong>
                                <?php if ($item['avaliacoes'] > 0): ?>(<?= (int) $item['avaliacoes'] ?> avaliações)<?php endif; ?>
                            <?php else: ?>
                                <strong>Novo</strong>
                            <?php endif; ?>
                        </span>
                        <span class="pro-local">
                            <img src="../assets/local_perfilprom.svg" width="10" height="13" alt="">
                            <?= vt_esc($item['local']) ?>
                        </span>
                    </div>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </section>
    </main>
    <?php include __DIR__ . '/conta-modal.php'; ?>
    <?php require_once __DIR__ . '/../Includes/mascaras.php'; ?>
    <?php include '../Includes/footer-home.php'; ?>
    <script>
        (function () {
            const preco = document.querySelector('.vt-barra input[name="preco_max"]');
            if (preco) {
                preco.addEventListener('input', function (evento) {
                    if (evento.inputType?.startsWith('delete') && /^,0{0,2}$/.test(preco.value)) {
                        preco.value = '';
                    }
                });
            }

            var menus = document.querySelectorAll('.vt-menu');
            function fechar(exceto) {
                menus.forEach(function (menu) {
                    if (menu === exceto) return;
                    menu.classList.remove('aberto');
                    menu.querySelector('.vt-menu-botao').setAttribute('aria-expanded', 'false');
                    menu.querySelector('.vt-menu-painel').hidden = true;
                });
            }
            menus.forEach(function (menu) {
                var botao = menu.querySelector('.vt-menu-botao');
                var painel = menu.querySelector('.vt-menu-painel');
                var lista = menu.querySelector('.vt-menu-lista');
                var campo = menu.querySelector('input[type="hidden"]');
                var valor = menu.querySelector('.vt-menu-valor');
                var busca = menu.querySelector('.vt-menu-busca input');
                var semResultados = menu.querySelector('.vt-menu-sem-resultados');
                function semAcento(texto) {
                    return texto.toLocaleLowerCase('pt-BR').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                }
                function filtrarCidades() {
                    var termo = semAcento(busca.value.trim());
                    var temResultado = false;
                    lista.querySelectorAll('li[data-valor]').forEach(function (item) {
                        var cidade = item.getAttribute('data-valor');
                        item.hidden = cidade ? !semAcento(cidade).includes(termo) : termo !== '';
                        if (cidade && !item.hidden) temResultado = true;
                    });
                    semResultados.hidden = termo === '' || temResultado;
                }
                botao.addEventListener('click', function (evento) {
                    evento.preventDefault();
                    evento.stopPropagation();
                    var abrir = painel.hidden;
                    fechar();
                    if (!abrir) return;
                    painel.hidden = false;
                    botao.setAttribute('aria-expanded', 'true');
                    menu.classList.add('aberto');
                    if (busca) {
                        busca.value = '';
                        filtrarCidades();
                        lista.scrollTop = 0;
                        busca.focus({preventScroll: true});
                    }
                });
                if (busca) {
                    busca.addEventListener('click', function (evento) { evento.stopPropagation(); });
                    busca.addEventListener('keydown', function (evento) {
                        if (evento.key === 'Enter') evento.preventDefault();
                    });
                    busca.addEventListener('input', filtrarCidades);
                }
                lista.addEventListener('click', function (evento) {
                    var item = evento.target.closest('[data-valor]');
                    if (!item) return;
                    campo.value = item.getAttribute('data-valor') || '';
                    valor.textContent = campo.value === '' ? valor.dataset.placeholder : item.textContent;
                    menu.classList.toggle('vt-vazio', campo.value === '');
                    lista.querySelectorAll('[aria-selected="true"]').forEach(function (el) {
                        el.removeAttribute('aria-selected');
                    });
                    item.setAttribute('aria-selected', 'true');
                    fechar();
                    botao.focus({preventScroll: true});
                });
            });
            document.addEventListener('click', function () { fechar(); });
            document.addEventListener('keydown', function (evento) {
                if (evento.key === 'Escape') fechar();
            });
        })();
    </script>
</body>
</html>
