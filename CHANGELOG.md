    # CHANGELOG

## 2026-11-25 - Limpeza de imagens não usadas (~31 MB)

- Removidos `Projeto/assets/home/Home-espaço.png` (14,0 MB) e
  `Projeto/assets/home/Home-buffet.png` (7,1 MB): nenhum arquivo do projeto os citava.
- Removida a pasta `Projeto/Banco/imagens Cadastro/` (10,0 MB, 38 imagens em PNG/JPG/HEIC):
  eram originais de cadastro, sem referência no código nem no `Banco/perfis-demo.sql`.
- O repositório caiu de ~106 MB para ~75 MB sem nenhuma mudança visual no site.

## 2026-11-25 - Ajustes pontuais no dashboard, nas solicitações e nas especialidades

- O vazio da coluna "Solicitações em 2026" ficou alinhado com o "Nenhum serviço agendado" da
  coluna "Próximos Serviços": `.proximos-lista` ganhou `padding-top: 24px`, o mesmo respiro que
  `.painel-corpo` já dava ao gráfico. Antes os dois textos começavam com 24px de diferença.
- O foco dos campos da solicitação do cliente deixou o anel lilás e passou a usar o amarelo da
  marca (`--ft02-amarelo`, #f5b600) com 3px e `outline-offset: -3px`, igual ao login.
- O modal "Avaliação enviada. Obrigado!" trocou o `check-dourado.png` (22×22 exibido em 42×42,
  que borrava) por `assets/check-dourado.svg`, no mesmo dourado com check branco.
- As especialidades **Aniversário** e **Corporativo** saíram de `Includes/tipos-festa.php`, das
  regras de ícone em `Includes/css/global.css` e dos assets `aniversario.svg` e
  `corporativo.svg`. O placeholder "Ex.: Aniversário" do formulário virou "Ex.: Casamento".
  As descrições demo que citavam "aniversários e eventos corporativos" foram reescritas para
  "formaturas e festas de 15 anos" (SQL e banco).
- Em "Minhas solicitações", o ícone de data e horário voltou ao cinza do rótulo (`#6d6784`):
  saiu a regra `.sol-periodo .label--data::before` que forçava o roxo.
- O botão **Recusar** da solicitação agora pede confirmação em um `<dialog>` nativo com
  "Recusar solicitação?", "Voltar" (foco inicial) e "Recusar solicitação" em vermelho. É o
  mesmo padrão do cancelamento do cliente; Esc fecha.
- Ao **aceitar** uma solicitação, o promotor vai direto para a página do serviço recém-criado
  (`Servicos/servicos.php?id=...`), em vez de voltar para o detalhe da solicitação.

## 2026-11-18 - Cards de solicitação x serviço e página do cliente

- O card compartilhado (`Includes/card-solicitacao.php`) agora tem duas leituras:
  - **Solicitações (caixa de entrada):** sem o bloco de data à esquerda (a data continua na
    linha de informações), com o selo "Aguardando resposta" e a linha "Recebida em" no topo do
    card, e a ação principal "Ver e responder".
  - **Meus Serviços (agenda):** o bloco de data ficou roxo sólido, com a contagem ("faltam N
    dias", "é hoje", "amanhã"), o selo "Confirmado", "Confirmado em" no topo e a ação
    secundária "Ver serviço". O Dashboard mantém o resumo compacto de antes.
- `Servicos/servicos.php` trocou o título "Serviços aceitos" por **"Serviços confirmados"**, com
  a contagem ao lado (mesmo selo usado nas Solicitações).
- Na página do cliente (`home/minhas-solicitacoes.php`), o card deixa de esconder informação no
  `<details>`: o tipo de evento aparece sob o título do pacote, o local entrou na lista de
  período (ocupando a linha inteira), a mensagem enviada virou um parágrafo de até 2 linhas e
  "Enviada em" ficou como rodapé. O aviso de prazo encerrado perdeu a caixa e ficou só uma linha
  discreta.
- `.sol-lista` ganhou `grid-template-columns: minmax(0, 1fr)`: uma mensagem longa sem espaços
  esticava a coluna do grid e o card inteiro passava de 4.000px de largura em telas grandes.
- CSS morto removido: `.card-pendencia-recebida` (card e `Servicos/CSS/servicos.css`) e
  `.sol-detalhes` / `.sol-mensagem`.
- O resumo do pacote no card do cliente perdeu o fundo lavanda: agora é um painel neutro
  (`#fbfafc` com borda), que separa o pacote do resto do card sem puxar cor.
- A foto do pacote ficou com caixa fixa: **120×80** no desktop e **96×64** até 560px, com
  `object-fit: cover`. Antes a altura seguia a proporção do arquivo, então uma foto quadrada e
  uma panorâmica não ocupavam o mesmo espaço.
- O pedido demo da Locação Diurna (id 1) tinha `nome_evento` igual ao título do pacote e por
  isso perdia a linha "Tipo de evento", ficando 20px mais baixo que os outros cards. O dado
  virou "Aniversário de 15 anos" e todos os cards do cliente ficaram com a mesma altura de
  resumo.
- A mensagem enviada quebra linha de verdade: `overflow-wrap: anywhere` (uma palavra de 500
  caracteres sem espaço não vaza mais do card) e `white-space: pre-wrap` (preserva as quebras de
  linha digitadas no campo). O limite de 2 linhas saiu, então a mensagem inteira aparece, como
  antes no `<details>`.
- A foto do pacote cresceu para **162×108** no desktop e virou faixa de **170px de altura** (com
  a coluna de texto embaixo) até 560px. A lista do plano agora mostra **3 linhas** e rola quando
  o pacote tem mais itens: todos os itens são enviados pela página (antes o PHP cortava em 3) e
  a área recebe `tabindex`/rótulo quando há rolagem, para funcionar também pelo teclado.
- O cancelamento da solicitação não usa mais o `confirm()` do navegador. O botão abre um
  `<dialog>` nativo com título "Cancelar solicitação?", o aviso de que a ação não pode ser
  desfeita e os botões "Voltar" (foco inicial) e "Cancelar solicitação" (vermelho). É o mesmo
  padrão já usado em `Servicos/acoes-detalhe.php`; Esc fecha e o foco fica preso no diálogo.

### Painel do promotor: caixa de entrada x agenda

- Cada uma das duas páginas passou a dizer o que faz, sem depender do rótulo da sidebar:
  - **Solicitações** → título **"Aguardando resposta"** e a linha *"Pedidos que chegaram e você
    ainda não respondeu."*
  - **Meus Serviços** → título **"Serviços confirmados"** e a linha *"Seus compromissos, com o
    próximo evento no topo."*
- O selo de contagem agora fica dentro do título e tem rótulo acessível ("2 solicitações
  aguardando resposta", "1 serviço confirmado"). Ele some quando a lista está vazia: um "0" ao
  lado de um título não ajuda ninguém.
- Cada lista ganhou um vazio próprio (`.lista-vazia`), em vez da linha solta de antes:
  - Solicitações: "Nenhuma solicitação aguardando resposta" + "Quando um cliente enviar um
    pedido, ele aparece aqui."
  - Meus Serviços: "Nenhum serviço confirmado" + "Aceite uma solicitação para o evento entrar na
    sua agenda." + link **"Ver solicitações pendentes"**, que leva direto à caixa de entrada.
- `Servicos/servicos.php` deixou de ordenar por data de recebimento e agora ordena por **data do
  evento**: próximos eventos primeiro, depois os passados (do mais recente para o mais antigo) e
  por fim as datas que não dá para ler. A agenda começa pelo compromisso mais próximo.
- O cabeçalho virou um bloco de duas linhas (`.solicitacoes-titulo` + `.solicitacoes-descricao`)
  no CSS compartilhado; o Dashboard continua usando o resumo compacto, sem mudança.
- Revisão automática (Better Design) nas duas telas: `check-comprehension` passou nas duas, e
  `inspect-spacing` mediu 1440, 1728 e 390 px até chegar a **100 nas duas**. O que a medição
  apontou e foi corrigido:
  - Em telas estreitas a prévia da mensagem no card ficava presa em 1 linha e cortava o texto:
    até 760px ela mostra **2 linhas** (o desktop segue com 1).
  - Espaçamentos fora da escala do projeto (6, 10, 18 px) foram para 8, 12, 16 px: gap do
    título, margem do cabeçalho, etiqueta do card, selo de contagem e bloco vazio.
  - A página da agenda usava 28px de padding lateral no desktop e a de solicitações 24px; as
    duas agora usam **24px**, e o rodapé da página foi de 60 para 64px.

### Foto do pacote na caixa de entrada

- O card de solicitação pendente passou a mostrar a **foto do pacote**, no mesmo lugar em que a
  agenda mostra o bloco de data: **160px de largura** no desktop, com cantos de 10px e
  `object-fit: cover`, e **96×72** no celular. Serve para reconhecer o pedido de relance, sem
  precisar ler o título.
- A consulta de `Includes/solicitacoes-pendentes.php` passou a trazer `pacote.imagem`, resolvendo
  o pacote por `pacote_id` e, quando ele falta, pelo nome em `pacote_selecionado` — a mesma regra
  que a página do cliente já usava. Pedido sem pacote localizado (ou pacote sem foto) não gera
  coluna vazia: o card volta ao layout de duas colunas.
- O card da caixa de entrada **não mostra mais a prévia da mensagem**: com a foto ocupando a
  esquerda e a ação à direita, o texto ficaria espremido. A mensagem continua no card da agenda e
  na página de detalhe, atrás de "Ver e responder".
- No card da caixa de entrada a ação deixou de ser uma terceira coluna: ela foi para dentro do
  card, junto do nome do cliente (`.card-pendencia-rodape`). Antes, com 140px de foto, a coluna de
  texto caía para ~97px em telas intermediárias; agora foto e texto dividem a largura e o rodapé
  usa a linha inteira.
- Dashboard, agenda e página de detalhe continuam iguais: a foto é exclusiva da caixa de entrada.

### Linha do selo e rodapé do card de solicitação

- A linha do topo da caixa de entrada passou a ser **selo → data e horário → convidados →
  "Recebida em …"**, com o registro empurrado para a direita (`margin-left: auto`). Assim o que
  identifica o pedido (quando e para quantas pessoas) fica ao lado de "Aguardando resposta", e o
  carimbo de chegada vira o dado menos importante da linha, encostado na borda.
- A cidade saiu desse card: o promotor já vê o endereço no detalhe, e ali ela só competia com a
  data e os convidados. Na agenda o local continua.
- O nome do cliente e a ação ficam no rodapé, e o rodapé fecha exatamente na **linha de baixo da
  foto do pacote**: a foto acompanha a altura do texto (`align-self: stretch`, imagem em
  `position: absolute` para não esticar o card com a própria proporção) e o rodapé é empurrado
  para a base (`margin-top: auto`). Medido a 1440px: base da foto, do avatar e do botão em 130px,
  e nenhum elemento do card abaixo dessa linha.
- A foto de perfil foi para **36px** e o nome para **16px** (antes 24px e 13px). O rodapé usa
  `align-items: flex-end`, então a base do avatar, a do valor e a do botão caem na mesma linha —
  antes o avatar ficava centralizado num rodapé de 44px e sobrava 2px de cada lado.
- Entre 761 e 1099px o rodapé passou a ocupar a largura toda do card (começa embaixo da foto),
  porque a coluna de texto ao lado de uma foto de 160px não cabia o nome e o botão juntos. Entre
  761 e 839px o "Valor no envio" e o botão empilham à direita, para o nome continuar na mesma
  linha deles e na base da foto.
- No celular (até 760px) a foto volta à proporção 4:3 (96×72) e o rodapé empilha — numa tela de
  390px não há largura para nome, valor e botão na mesma linha. Ali a foto não acompanha mais a
  altura do texto: alongá-la até a base deixaria uma tira de 96×280.
- No celular o bloco de ações da caixa de entrada não recebe mais a borda superior e o
  `padding-top` que eram do layout antigo (quando a ação era a última linha do grid do card):
  dentro do rodapé isso desalinhava o botão em 6px.
- Espaçamento entre selo, título do pacote e cliente subiu de 8 para **12px**, e o rodapé usa
  `gap: 12px 16px` com quebra para duas linhas em telas estreitas.
- O gap entre ícone e rótulo (data, convidados, local, mensagem) foi de 6 para **8px**, dentro da
  escala de 4/8/12/16/24/32/48/64/96/128 do projeto.
- O ícone de convidados deixou de ser roxo e passou a usar `currentColor`, a mesma cor do ícone de
  data — no card da caixa de entrada os dois ficam cinza, junto do texto do rótulo. Só o ícone de
  mensagem continua roxo.
- `inspect-spacing` voltou a **100** em 1440, 1728 e 390 px, nas duas telas (caixa de entrada e
  agenda), sem clipping e sem espaçamento fora de escala.

### Meus Serviços com a mesma estrutura da caixa de entrada

- A agenda passou a usar o mesmo desenho de card de Solicitações: os rótulos com ícone (data do
  evento e convidados) subiram para a **linha do selo**, ao lado de "Confirmado", com
  "Confirmado em …" empurrado para a direita. O bloco `.card-pendencia-meta` deixou de existir
  nesse card e a **cidade saiu** (continua na página de detalhe).
- O nome do cliente e a ação foram para o mesmo rodapé de duas pontas, e o bloco de data roxo
  acompanha a altura do texto (`align-self: stretch` + `align-content: center`): a base da data,
  a do avatar e a do botão "Ver serviço" caem na mesma linha, e nada do card fica abaixo dela.
  Medido a 1440px: as três em 161px.
- O dia e o mês da data cresceram: **23 → 28px** o dia e **12 → 13px** o mês (**22px** no
  celular). O "faltam N dias" continua abaixo.
- A foto de perfil subiu para **36px** e o nome para **16px**, iguais aos da caixa de entrada.
- Em `Servicos/CSS/servicos.css` saíram as regras que faziam das ações uma coluna esticada à
  direita (valor no topo, botão no pé) — herança da época em que elas eram a terceira coluna do
  grid. Sem elas, o valor e o botão ficam na mesma linha, dentro do rodapé.
- O corpo do card virou uma coluna flex com `gap: 12px` e as margens próprias de etiqueta, título
  e mensagem foram zeradas: antes o título somava 12px de margem com 8px da mensagem, resultando
  em 20px entre um e outra. Agora selo → título → mensagem → rodapé ficam todos a 12px.
- No celular o rodapé empilha (nome em cima, valor e botão embaixo), como na caixa de entrada.
- O bloco de data da esquerda passou a ter a **mesma largura da foto do pacote** na caixa de
  entrada: **160px** (era 104px) e **96px** no celular. Como nos dois casos o bloco estica até a
  base do card, as duas telas ficam com a mesma coluna à esquerda — a 1440px, 160×132 na agenda e
  160×113 em Solicitações (a diferença de altura vem do texto de cada card).
- A mensagem do cliente saiu de entre o título e o rodapé: agora fica **embaixo do nome**, dentro
  do rodapé, formando um bloco com o cliente que também encosta na base do card. O card encolheu
  de 178 para **166px** a 1440px e a linha de base continua fechada (data, mensagem e botão em
  149px). O bloco do nome usa `flex: 1 1 200px`, então entre 761 e 840px ele encolhe em vez de
  jogar as ações para outra linha.
- `inspect-spacing`: **100** nas duas telas, em 1440, 1728 e 390 px.

### Mesmo card no Dashboard, data transparente e reticências na mensagem

- O card da seção **"Solicitações pendentes" do Dashboard** chegou a ser o mesmo da caixa de
  entrada (foto de 160px, selo, convidados, "Recebida em", título e rodapé com cliente e ação).
  **Na revisão seguinte ele virou o card simples** descrito acima — o desenho completo ficou só
  em Solicitações.
- O layout antigo do Dashboard (a variante `--resumo`, com bloco de data de 80px e a linha de
  informações embaixo) saiu do código: `$resumo_dashboard`, as regras `.card-pendencia--resumo`,
  o ramo `else` do card e as variáveis que só ele usava (`$periodo`, `$cidade`, `$horario`) foram
  removidos. Desde então o desenho de cada tela é escolhido pelo `$layout_card` da página que
  inclui o card.
- Na agenda, o **bloco de data** deixou o roxo cheio com texto branco: passou por um "só contorno"
  e ficou num **lilás bem claro** (`#f7f4fd`, com a borda `#ddd2f1` e o texto em roxo escuro) —
  dá corpo ao bloco sem competir com o pacote. Como o bloco acompanha a altura do card, a linha de
  base do rodapé não mudou (149px a 1440px).
- O **mês saiu da abreviação e virou o nome por extenso** no bloco de data da agenda
  ("05" + "DEZEMBRO", antes "05" + "DEZ"). Como o nome por extenso é mais largo que o dia, ele
  virou legenda: **11px com entreletra de .06em**, abaixo do dia de 28px (22px no celular). A 1440px
  o conteúdo do bloco tem 64px numa caixa de 160px; a 390px, 64px numa caixa de 96px — sem quebra
  e sem estouro. O Dashboard continua com a abreviação ("5 dez"), porque ali a caixa do dia tem só
  64px.
- A **mensagem do cliente na agenda corta com reticências** quando passa de uma linha no desktop
  (duas no celular), e o texto completo aparece no `title` ao passar o mouse.
- Correção de layout: entre 761 e 1099px o rodapé era esticado com `margin-left` negativo para
  ocupar a largura toda do card. Como o bloco da esquerda (foto ou data) acompanha a altura toda,
  o nome do cliente e a mensagem ficavam **por cima dele**. A regra saiu: o rodapé volta a viver
  dentro da própria coluna e só quebra para uma segunda linha quando falta espaço (medido em 761,
  790, 823, 900, 1000, 1100 e 1728px, sem sobreposição e sem estouro do card).
- No celular o "Recebida em"/"Confirmado em" deixou de ser empurrado para a direita quando cai
  numa linha própria — o empurrão só faz sentido quando ele divide a linha com o selo, e sozinho
  ele deixava a linha desalinhada do resto do card. Isso zerou o achado
  `spacing/leading-edge-drift` que aparecia no card do Dashboard a 390px.
- `inspect-spacing`: **100** na agenda, no painel do Dashboard e nas telas de Solicitações, em
  1440, 1728, 1000, 823 e 390 px. A única ressalva é o corte da mensagem da agenda, que o inspetor
  lê como "clips vertical content" — é a truncagem pedida, com o texto integral disponível no
  `title` e na página de detalhe.

### Card simples no Dashboard e Próximos Serviços só com a data

- A seção **"Solicitações pendentes" do Dashboard** passou a mostrar um card **enxuto**, de
  relance: miniatura do pacote (72px, 56px no celular), nome do pacote, cliente com avatar e
  "Recebida em ...". Saíram o selo "Aguardando resposta", os convidados e o botão "Ver e
  responder". O **card inteiro é um link** para a página de Solicitações, onde está a leitura
  completa (selo, convidados, valor e a ação de responder) — o grid do card simples é
  `foto | texto | seta`, com a seta discreta à direita indicando o clique.
- O Dashboard passou a ter **três desenhos** de card, escolhidos pelo `$layout_card`:
  `solicitacoes` (leitura completa, com foto), `servicos` (agenda, com o bloco de data) e
  `dashboard` (o card simples). As variáveis `$card_entrada` e `$card_simples` são mutuamente
  exclusivas, e o `--simples` define o próprio grid no desktop e no celular (onde sem a coluna da
  seta o terceiro filho do grid cairia numa linha própria).
- Em **"Próximos Serviços"** saíram a **localização** e a **linha com a data/período** embaixo:
  ficou só o bloco de data (dia + mês) à esquerda, o pacote e o cliente. Com isso o
  `.proximo-meta` e as regras dele em `Dashboard/css/dashboard.css` foram removidos, junto com a
  função `data_curta_agenda()` e as variáveis `$periodo`, `$termino` e `$local`, que só serviam
  para montar essas linhas (o local continua na página de Meus Serviços e no detalhe).
- Cada item de **"Próximos Serviços"** ganhou **borda própria** (1px, raio de 8px) em vez do
  fio que só separava um item do outro: os cards ficam soltos, com 8px de folga entre eles
  (`.proximos-lista` virou grid com `align-content: start`, para um único item não esticar até a
  altura reservada da lista). No hover o fundo clareia e a borda fica lilás.
- Medido a 1440px: o card simples do Dashboard tem **1039×80** (antes 1020×147) e o item de
  Próximos Serviços, 484×80. Sem estouro nem sobreposição em 1728, 1440, 1100, 900, 700 e 390px.
- `inspect-spacing`: **100** no painel do Dashboard (1440 e 390px) e nas telas de Solicitações e
  Meus Serviços. A única ressalva continua sendo o corte da mensagem da agenda, que o inspetor lê
  como "clips vertical content" — é a truncagem pedida, com o texto integral no `title` e no
  detalhe.

### Data do evento de volta na caixa de entrada e ícone de "Tipo de festa"

- A **data do evento voltou para a linha do selo** no card de Solicitações, ao lado dos
  convidados (`label--data`), na ordem anterior: selo → data → convidados → "Recebida em". O card
  da caixa de entrada não tem bloco de data, então sem essa linha o promotor só sabia a data da
  festa abrindo o detalhe.
- De 761px para cima a data leva o horário ("28/11/2026 · 19:00 às 01:00"). Até 760px o horário
  sai (`.card-pendencia-data-horas`, `display: none`): na coluna de 218px do celular ele gastaria
  uma linha inteira, e com a data seca ela cabe ao lado dos convidados — a etiqueta fica com 3
  linhas em vez de 4 (62px de altura e card 358×289, antes 82px e 309).
- Medido: a 1440px a linha do selo fecha em 22px e o card mantém **1092×147**, com a foto e o
  rodapé na mesma base (130px); a 760px tudo cabe numa linha; a 390px fica selo / data +
  convidados / "Recebida em", sem estouro horizontal.
- "Tipo de festa" ganhou ícone — era o único `dt` do detalhe sem um. A classe `label--tipo` usa
  `assets/tipo_festa_solicitacoes.svg`, um **bolo** (silhueta cheia 24×24, ~43% de tinta, contra
  21% de um confete que sumia a 14px), na mesma receita dos outros: `mask` de 14px com
  `background: currentColor`.
- O mesmo ícone entrou no rótulo do formulário do cliente: o `:is(...)` de
  `Cliente/css/solicitacao.css` e as duas listas de `Includes/css/icones-solicitacao.css` passaram
  a incluir `.label--tipo`, então o campo fica ao lado de data, horário, local, convidados e
  mensagem, com o mesmo ícone de 14px.
- `inspect-spacing`: **100** na caixa de entrada (1440 e 390px). No detalhe os achados são
  anteriores a esta mudança (o link "Voltar para solicitações" e os contatos medem menos de 44px
  de altura, e o grid do painel usa valores fora da escala).

### Foto do pacote no detalhe: mesmo quadro do card

- O **recorte do promotor já está no arquivo**: o editor de enquadramento do perfil grava o pacote
  em 16:9 e o upload mantém a proporção (os envios recentes estão em 900×507, e as fotos demo, em
  900×900). Todas as telas leem o mesmo `pacote.imagem` — o recorte nunca se perdeu no caminho.
- O desencontro era o **quadro**: o detalhe usava `aspect-ratio: 16/9` e o card da lista, ~4:3.
  Num arquivo quadrado, o detalhe mostrava só **56% da altura** e o card, **71%** — daí a impressão
  de que o detalhe não respeitava o recorte.
- `.sol-detalhe-pacote-foto` passou a usar **`aspect-ratio: 4/3`**, o mesmo quadro do card (e do
  card simples do Dashboard): **421×316** a 1440px e **324×243** a 390px. Num arquivo quadrado, o
  detalhe agora mostra **75%** da altura contra os 71% do card; num upload 16:9, mostra 75% da
  largura contra os 79% do card — ou seja, o detalhe nunca corta mais que a lista.
- Vale para os dois detalhes de uma vez, porque Solicitações e Meus Serviços usam o mesmo
  `Includes/detalhe-solicitacao.php`.

### Ícone ativo da sidebar mais fino

- O ícone do item ativo da sidebar não engorda mais: o `stroke-width` caiu de **2.5 para 2** (o
  normal é 1.75). O item ativo continua se distinguindo pelo fundo branco, pela pastilha lilás e
  pelo texto em 600 — com 2.5 o ícone ficava mais grosso que todos os outros e chamava mais
  atenção que o próprio rótulo.

### Amarelo da marca na página Minha Assinatura

- O cartão do plano em uso continua **branco**, com um degradê amarelo discreto de ponta a ponta:
  8% de `--ft02-amarelo` no topo (`rgb(254, 249, 235)`) caindo para 2% na base
  (`rgb(255, 254, 250)`), sem paradas em branco puro no meio. A borda ficou num dourado suave
  (55% amarelo + 45% `--borda`) em vez do lilás `#cfc4e3`.
- O selo "Plano atual" passou a ser amarelo com texto `--ft02-roxo-escuro`, e o valor de "Plano
  atual" no resumo virou o mesmo par (`.chip-plano`): fundo amarelo, texto roxo. É o único par de
  amarelo com contraste de sobra (9,2:1).
- `.botao-plano` ("Mudar para este plano") mantém o texto branco sobre o amarelo, por pedido. O
  par fica em **1,8:1**, abaixo do mínimo de 4,5:1 do WCAG AA; o par com contraste folgado continua
  sendo amarelo + roxo (9,2:1), usado no selo e no chip do resumo.
- O anel amarelo em volta da foto do resumo saiu: era decoração sem função.
- Um cartão inteiro em roxo sólido foi testado como alternativa e descartado: pesava mais que o
  próprio conteúdo da página. O degradê mantém o cartão claro e ainda marca o plano em uso.
- Medido a 1440px e a 390px: cartões em **532×334** no desktop e **358** de largura no celular,
  sem rolagem horizontal; chip em **62×27**, dentro da linha de dados; selo a 17px da borda
  interna do cartão.

## 2026-11-17 - Raiz do repositorio limpa

- `cadastro-direita-original.png` (1,21 MB) e `design-ontology.json` (4 KB) saíram do
  versionamento: nenhum dos dois é usado pelo aplicativo (a tela de login usa
  `Projeto/assets/cadastro-direita.png`). Os arquivos continuam no disco, agora listados no
  `.gitignore`, então não voltam para o repositório sozinhos.

## 2026-11-17 - Pasta do app renomeada para Projeto e README na raiz

- A pasta do aplicativo passou de `Editando` para `Projeto` (mesmo conteúdo, mesmo
  funcionamento). As URLs do site local viraram `http://localhost:<porta>/Nathus/Projeto/...`.
  `setup.cmd`, `parar.cmd` e `verificar.cmd` detectam o nome da pasta sozinhos, então só os
  textos de ajuda precisaram de ajuste. As entradas **abaixo** desta citam a pasta pelo nome
  antigo (`Editando/`), porque descrevem o estado da época.
- Adicionado `README.md` na raiz do repositório, com a visão geral do projeto e o passo a passo
  de como usar o `setup.cmd`.
- As fotos das contas de demonstração (`Projeto/uploads/**/demo-*`, ~29 MB) passaram a ser
  versionadas, para os perfis de exemplo aparecerem completos em um clone novo. O restante de
  `Projeto/uploads/` continua fora do repositório.

## 2026-11-17 - Subtítulo do topo removido nas telas do promotor

- O topo do painel (`Includes/header.php`) mostrava uma linha de descrição sob o título
  ("Bem-vindo(a) de volta, ...", "Revise os pedidos...", etc.), que quebrava a linha de forma
  errada em telas estreitas. O `<p>` do subtítulo foi removido do header (e o texto padrão de
  boas-vindas junto), e as atribuições de `$subtitulo_pagina` foram tiradas de
  `Assinatura/assinatura.php`, `Solicitacoes/solicitacoes.php`, `Relatorios/relatorios.php`,
  `Perfil/perfil.php` e `Servicos/servicos.php`. Agora todas as telas mostram só o título.

## 2026-11-17 - Largura de Relatórios e anel de foco do cartão

- A página de Relatórios (`.rel-pagina`) estava com conteúdo de 1180px, mais estreito que as
  outras telas do promotor (Dashboard e Meu perfil usam 1480px, Solicitações 1440px), o que
  deixava muito vazio nas laterais. Agora usa `max-width: 1480px` e o mesmo padding lateral
  fluido (`clamp(16px, 2.5vw, 36px)`), ficando uniforme ao resto do painel.
- No campo do número do cartão (`e5-cartao`) apareciam **dois** anéis amarelos de foco: um no
  conteúdo editável (`.cartao-valor`, que começa depois do ícone) e outro no cartão inteiro
  (`.campo-cartao:focus-within`). O `outline: none` do conteúdo perdia na especificidade para o
  anel geral do cadastro. Em `Includes/css/cadastro-campos.css` o seletor geral passou a usar
  `:where(...)`, com especificidade baixa, então o conteúdo não desenha o próprio anel e fica
  **um** anel só, no cartão inteiro com o ícone.

## 2026-11-17 - Frase de destaque no cadastro e máscara do cartão

- A etapa 8 do cadastro (`Cadastro-geral/cadastro-promotor/e7/e7.php`) ganhou o campo
  "Frase de destaque" (opcional, até 150 caracteres), com o mesmo acabamento da descrição.
  `salvar-perfil.php` grava `frase_destaque` junto de `descricao` e da foto, truncando em
  150 caracteres. É a mesma frase editável depois em Meu perfil > Editar sobre.
- O número do cartão em `e5-cartao/cartao.php` agora recebe máscara em grupos de 4
  dígitos, limitado a 16 dígitos (`e5-cartao/js/cartao.js`): `4111111111111111` vira
  `4111 1111 1111 1111`. Letras e separadores digitados são descartados. CVV (3 dígitos)
  e validade (MM/AA) seguem iguais.

## 2026-11-17 - Altura das imagens dos pacotes no perfil

- As fotos dos pacotes no painel do promotor (`.pacote-imagem`) e no perfil público
  (`.foto-pacote`, inclusive "ver como cliente") ficam em 220px de altura (antes 156px).
  A página de solicitação (`.pkg-imagem`) ficou como estava, em 168px.

## 2026-11-17 - Menu do setup sem piscar

- O menu usava `set /p`: uma linha vazia (Enter sobrando, espaco, Ctrl+C anterior)
  fazia o `goto :menu` limpar a tela e redesenhar tudo, e cada volta ainda esperava
  a checagem do MySQL (~3s) -> o menu ficava piscando. Trocado por `choice /c 123456780`,
  que so aceita as teclas 0-8: Enter, espaco e tecla errada sao ignorados sem redesenhar.
  Se o terminal nao entregar entrada, aparece "Nao consegui ler a opcao neste terminal"
  em vez do loop. Testado com entrada vazia, tecla invalida e opcao valida.

## 2026-11-17 - Sessão orfã após reset e menu de setup

- Depois do reset do banco os ids reiniciam, mas a sessao do navegador continuava com o id
  antigo. O topo pegava o nome da sessao (local1 = Marcelo Prado) e o tipo no banco pelo id
  antigo, que nao existia mais -> aparecia como Cliente ate sair e entrar de novo.
  Agora Includes/conexao.php descarta sessão cujo id não existe mais e a página de login avisa que o banco foi atualizado;
  o setup.cmd reset tambem apaga as sessoes do PHP.

## 2026-11-17 - Área do espaço obrigatória e setup por .cmd

- Área do espaço (m²) virou **obrigatória** em contas de local de festas: o campo no
  perfil agora é "Área do espaço (m², obrigatório)" com `required`, o `Perfil/js/perfil.js`
  barra o envio vazio e `Perfil/salvar-editar-perfil.php` recusa com
  `erro=area_obrigatoria`. Criar pacote passou a exigir endereço **e** área
  (`Perfil/pacote-salvar.php`), e o botão bloqueado virou "Completar dados do espaço".
- `Banco/perfis-demo.sql` passa a preencher a área das 10 contas de local (local2, 4, 7 e 9
  estavam sem metragem); o banco atual recebeu o mesmo ajuste.
- Banco enxuto: saíram `corrigir_caminhos_imagens.sql` (reescrevia caminhos de imagens que
  não existem mais), `integridade_solicitacoes.sql` (repetia índices e FKs que o
  `nathus.sql` já cria), `remover_preco_minimo_manual.sql` (coluna já inexistente) e
  `perfis-demo-imagens.json` (inventário sem uso no código).
- Novos `setup.cmd`, `parar.cmd` e `verificar.cmd` dentro de `Editando` (a raiz do projeto): o
  `setup.cmd` abre um menu numerado no cmd (verificar programas, subir MySQL, criar banco,
  importar perfis demo, abrir/parar o site, diagnóstico, reset) e usa o servidor embutido do
  PHP numa porta livre, sem depender do Apache.

## 2026-11-17 - Amarelo de ação, login em Poppins e Voltar do cliente

- O amarelo de ação deixou de ser o tom amarromado (#946B00) e passou ao
  amarelo claro da paleta (#F5B600). Afeta Mudar para este plano (Minha
  Assinatura) e Ver detalhes dos cards de pendência.
- Magica removida do projeto: `--f2` agora é Poppins e o `@font-face` saiu do
  `global.css`; os CSS antigos (home, style, buffet, org) também foram limpos.
- Login segue o padrão dos cadastros: Poppins no título (40px/800) e no
  subtítulo (18px); campos de 48px, raio 8px, fundo #E2E4E9, rótulo 15px
  grafite e foco de 3px #F5B600; botão "Continuar" (sem caixa alta) com
  52px, peso 700 e 16px.
- A estrela/marca d'água do canto inferior direito de
  `assets/cadastro-direita.png` (fundo do login) foi removida externamente
  pelo usuário; a versão limpa (`Mulher Escrevendo à Luz Púrpura.png`, em
  Downloads) substituiu o arquivo e o login já carrega a nova (1122×1402).
  O remendo local anterior, que ficou visível como um quadrado, foi descartado.
  Cópia crua em `cadastro-direita-original.png`, na raiz.
  O fundo do login entra por estilo inline com `?v=filemtime` (cache-busting),
  para o navegador não continuar exibindo uma versão antiga em cache.
- Em Minhas solicitações do cliente, Voltar usa o botão compartilhado
  (`botao-voltar.css`): contorno roxo, ícone de seta, hover preenchido e
  área de toque de 44px. O mesmo botão entrou no perfil público do cliente
  (`Cliente/perfil.php`), na solicitação (`Cliente/solicitacao.php`) e na
  listagem da vitrine (`home/home.php`, nos três campos: organizadores,
  buffet e espaço), sempre com `width: fit-content` para não esticar.
- Itens dos pacotes (`Includes/itens-pacote.php`) não abrem mais "Ver todos os
  itens": a lista rola por dentro (máx. 11rem, `overflow-y: auto`, foco por
  teclado), então os cards não crescem quando o pacote tem muitos itens.
  Vale para o perfil público, o perfil do promotor e o resumo da solicitação.
- Pacote sem foto no perfil público mostra o mesmo espaço reservado da visão
  do promotor: moldura tracejada com "Sem imagem", na altura das fotos (156px).
- Rodapé (`Includes/footer-home.php`): copyright atualizado para 2026. "Sobre",
  "Serviços", "Funcionalidades" e "Assinaturas" passaram a apontar para as
  âncoras da landing page (`Landing%20Page/landingpage.php#sobre`, `#servicos`,
  `#funcionalidades`, `#planos-lp`), em vez das âncoras inexistentes da home.
  "Dashboard" só aparece para conta promotor; cliente e visitante não veem o item.
- Removida a pasta legada `Nathus_imagens_e_SQL/` (LEIA-ME + `atualizar_imagens.sql`
  do seed inicial dos campos 1–15). Nenhum registro do banco aponta para os
  caminhos `uploads/buffetXX`; os promotores usam as imagens `demo-*`.
- O bloco "Seu evento dos sonhos / em um só lugar com a Nathus" foi padronizado
  em `Includes/painel-texto.php` + `Includes/css/painel-texto.css`: Poppins,
  título 32px (clamp 22–32), frase 22px (clamp 15–22) e mesmo espaçamento no
  login, na escolha do tipo de usuário e nas etapas dos dois cadastros.
  O botão sob o texto ("Criar conta" no login → cadastro; "Já tenho conta" na
  escolha do tipo → login) aparece só nesses dois pontos de entrada; as etapas
  do fluxo exibem apenas o texto.
- Verificado no navegador: estilos calculados do login e do Voltar, página de
  Assinatura com o novo amarelo e ausência de erros de console.


## 2026-11-17 — Largura e finalização do cadastro

- Ver detalhes nos cards de solicitações e serviços e Mudar para este plano
  usam texto branco sobre dourado. O botão Voltar dos detalhes compartilha
  o estilo do cadastro. Dados do plano aparece antes da comparação de planos.
- O preço do pacote bloqueia digitação ou colagem acima do limite e mantém
  o valor anterior. Erros de formato são tratados no próprio modal.
- Os campos dos dois cadastros usam contorno de 3 px em dourado mais claro
  (#F5B600) ao receber foco.
  O menu de débito e crédito usa seta, contorno e marcador dourados,
  com fundo amarelo claro e texto grafite na opção ativa.
- Finalizar cartão, Ir para meu perfil e Explorar usam dourado da paleta
  (#D5A20A) com texto branco e hover mais escuro, para avaliação visual nos
  dois cadastros. O tom foi clareado após a avaliação do usuário.
- As transições entre etapas do cadastro do promotor passam a redirecionar
  com HTTP 303 após salvar os dados na sessão, permitindo voltar e avançar pelo
  navegador sem reenviar os formulários. Plano e método recuperam a seleção salva.
  Os dois cadastros usam um botão Voltar roxo com ícone, preenchimento no hover
  e adaptação à preferência por movimento reduzido.
- Os dois cadastros usam Poppins também nos títulos e rótulos grafite mais escuros.
  Os campos compartilham raio de 8px. O formulário de cartão acompanha a largura
  de até 680px dos dados pessoais e tem opções de débito e crédito com contraste
  reforçado e seleção destacada no menu.
- No Pix, o rótulo “Código Pix” fica centralizado, com 32px de espaço
  abaixo do QR Code e borda amarela de 6px. A nota de demonstração saiu
  da tela de apresentação.
- O formulário de cartão passa a usar a mesma largura, o resumo de plano
  e valor e a escala de espaçamento da tela de Pix.
- Os rótulos do cartão usam 15px, como nos formulários de cadastro do
  cliente e do promotor.
- Os campos de cartão voltam ao fundo cinza com cantos retos, mantendo
  o espaçamento atual. Os dados de cartão podem ser digitados na simulação,
  com “Mês/Ano” no campo de validade, barra automática e sem incluir esses
  valores no envio do cadastro.
- Na etapa 8 do promotor, a confirmação usa o ícone de sucesso e indica
  como abrir o perfil. O Pix volta à ordem vertical, com plano e valor,
  QR Code centralizado e código copiável abaixo.
- Cards de pacote no painel, perfil público e formulário mostram até cinco
  itens na prévia e permitem consultar a lista completa sem estourar o card.
- A vitrine e os perfis usam a mesma capa padrão quando não há banner próprio.
  A nova arte mantém os ícones de atuação longe do avatar.

- Ampliada a área de conteúdo dos cadastros de cliente e promotor em Editando.
  Na escolha do campo, o cartão marcado tem indicação própria e foco pelo
  teclado. A tela de Pix usa uma grade responsiva e informa que é demonstrativa;
  a confirmação indica como concluir o perfil.
- A etapa final do promotor usa o avatar padrão. Cadastro e modal Sobre mim
  compartilham a validação de 1.000 caracteres. Ao passar o mouse ou focar
  o avatar no cadastro, aparece a câmera sobre a foto escurecida.
- Em Editando, os avatares do cadastro e dos modais de perfil usam o mesmo
  escurecimento no hover e uma câmera de 32px com preenchimento mais opaco.

## 2026-11-16 — Correções no cadastro e no endereço do promotor
- Os formulários de cliente e promotor usam Magica no título e o rótulo
  “Continuar” no botão da primeira etapa.
- A etapa de cadastro do cliente usa o avatar padrão da conta e o botão
  “Continuar” com inicial maiúscula.
- O hover da foto em Editar perfil do promotor segue o do cliente, com a
  câmera original de 24px e transição apenas no escurecimento da foto.
- O cadastro de cliente só avança com uma foto enviada quando ela é salva
  corretamente. A mensagem de erro informa os formatos aceitos.
- O botão de cópia do PIX mantém o SVG original após copiar.
- A etapa final do promotor usa a câmera de Editar perfil e limita a
  descrição a 1.000 caracteres no formulário e no servidor.
- Em Editando, a opção Local exige endereço completo no modal e no
  servidor. O aviso em Pacotes de Serviços tem 16px de margem inferior.
- O filtro Capacidade da vitrine de Espaços de festas usa só o rótulo de texto.

## 2026-11-16 — Integridade das solicitações e dados do plano

- Editando e Atual deixam pedidos legados sem promotor fora dos painéis.
  O schema remove a amostra associada a um ID fixo e inclui índices e chaves
  estrangeiras para os vínculos de conta. A migração foi aplicada no MariaDB local.
- Em Editando, o aceite rejeita datas inválidas e conflitos sob a trava do
  promotor. Serviços aceitos com data inválida mostram um aviso no detalhe.
- Dashboard e Relatórios contam serviços concluídos como aceitos. O sino
  mostra o status atual, e o cancelamento limpa o aviso de aceite.
- Minha Assinatura mostra os dados cadastrados do plano e esclarece que a
  troca nesta tela não gera cobrança.

## 2026-11-16 — Hierarquia do PDF de Relatórios

- O título aparece uma vez, ao lado da logo. O resumo usa valores em
  grafite, com destaque para recebidas, aceitas e taxa de aceite.
- O gráfico mantém os doze meses e rotula apenas as barras com solicitações.

## 2026-11-16 — Exportação e ícones de Relatórios

- O PDF usa a logo oficial no cabeçalho, barras no amarelo do painel e uma
  coluna mais larga para exibir o período completo dos eventos.
- Os cabeçalhos da tabela de Relatórios usam ícones roxos preenchidos, com
  tamanho e espaçamento uniformes. Cliente e Convidados usam o mesmo ícone
  de pessoa do painel.

## 2026-11-16 — Minha Assinatura e gráfico de Relatórios

- Minha Assinatura segue a largura e os cards brancos do painel, com títulos
  grafite, selo lilás no plano atual e ação de troca em amarelo.
- Relatórios usa nas barras o mesmo amarelo do gráfico do Dashboard.

## 2026-11-16 — Acabamento do Dashboard

- Gráfico, agenda e solicitações pendentes usam títulos dentro dos cards
  brancos, seguindo a página Relatórios. Os links das listas usam roxo e o
  contador de pendências mantém o amarelo da marca.
- Os indicadores usam padding, espaçamento e cantos mais próximos de
  Relatórios, com os ícones coloridos do Dashboard.
- O Dashboard usa a mesma largura máxima de Meu Perfil, com margens laterais
  próximas às da página de referência. O grafite mais escuro destaca os três
  títulos de seção.

## 2026-11-16 — Agenda do Dashboard e histórico do cliente

- O Dashboard conta os próximos eventos pelo início do serviço aceito, sem
  limitar o indicador aos três serviços exibidos. A agenda usa ícones roxos e
  separa os horários com “até”. Todos os itens da agenda mostram o ícone de
  calendário e a data junto ao horário, inclusive eventos de um dia.
- O gráfico mantém as barras amarelas, mostra a contagem dos meses com pedidos
  e exibe os doze meses também no celular.
- Em Minhas solicitações, data e horário usam o ícone de calendário e “até”.
  O destaque do pedido aberto pela notificação usa o roxo do painel.
- A linha de data, horário e convidados segue a escala de espaçamento do projeto.

## 2026-11-16 — Cancelamento em Minhas solicitações

- O botão Cancelar solicitação usa a tipografia da vitrine com 13 px e
  peso 600, e mantém uma área de toque de pelo menos 44 px de altura.

## 2026-11-16 — Disponibilidade no formulário de solicitação

- O calendário marca e bloqueia os dias reservados por serviços aceitos do
  promotor, incluindo as duas pontas e os dias intermediários. A digitação e
  o servidor também rejeitam períodos sobrepostos. O aceite no painel impede
  conflitos entre pedidos que estavam pendentes.
- O calendário usa vermelho e número riscado para dias reservados, e amarelo
  para o dia atual. A legenda das duas cores substitui o botão Digitar data.
  Quando o dia atual está reservado, mantém o amarelo com o número riscado.
- As células dos dias têm área de toque de pelo menos 44 × 44 px a partir
  de uma tela de 320 px, com margens menores em telas estreitas.
- A mensagem começa maior e cresce até um limite nos navegadores compatíveis,
  sem alça de redimensionamento e com o limite de 500 caracteres. Texto
  sem espaços quebra dentro do campo e mantém a largura das colunas.

## 2026-11-16 — Ícones e limite da mensagem da solicitação

- Restaura os ícones de data, horário e local no formulário e padroniza o ícone
  roxo de convidados no formulário, no painel, no histórico, na vitrine, no
  perfil do promotor e em Relatórios.
- Adiciona o mesmo ícone de mensagem ao formulário, aos cards, aos detalhes
  e ao histórico do cliente.
- Limita a mensagem do pedido a 500 caracteres no formulário e no servidor.
  O detalhe limita a altura da mensagem e mantém o texto integral disponível.

## 2026-11-16 — Período no detalhe do evento

- Os detalhes da solicitação e do serviço mostram data e horário em Início
  e Término também para eventos de um dia. Registros com dados incompletos
  continuam exibindo as informações disponíveis.
- O card compartilhado de serviço e solicitação usa “até” entre as datas
  dos eventos que terminam em outro dia. Eventos de um dia mostram a data
  junto ao horário, com o mesmo ícone de calendário.

## 2026-11-16 — Meus Serviços em Editando

- A lista de serviços aceitos compartilha o card e a paleta de Solicitações.
  Exibe o cliente com foto ou avatar padrão e o valor no envio. Nos dois cards,
  período, convidados e cidade ficam lado a lado quando há espaço, com uma
  prévia da mensagem abaixo quando disponível. A paginação aparece quando há
  mais de uma página. No desktop, o horário do aceite se alinha à última linha
  do resumo. Em telas estreitas, o cabeçalho mantém o título e os menus visíveis.
- O card compartilhado usa a escala de espaçamento no padding, nas colunas,
  nos ícones e no botão de detalhes.
- O detalhe compartilha as seções de contato, evento e pacote com a solicitação.
  O card de contato usa o mesmo espaçamento nas duas telas, com links
  compactos no desktop e área de toque maior no celular.
  A conclusão fica junto aos dados do evento, com confirmação e aviso do horário
  de liberação quando disponível. Eventos de um dia também respeitam o horário final.

## 2026-11-16 — Lista de solicitações do promotor

- A página Solicitações usa um contador de pendências e cards próprios com
  selo de data, cliente, período, cidade e valor registrado no envio.
  O endereço completo e o texto integral continuam no detalhe.
- A lista usa a mesma largura máxima de 1440px da página de detalhe.
- O selo de data é centralizado na altura do card, e os ícones do evento
  usam o roxo do formulário.
- O selo ficou maior e mais separado do resumo. O rótulo da mensagem usa
  o roxo do formulário, e os textos secundários usam grafite mais escuro.
- O selo da sidebar, o contador de Pendentes e a ação Ver detalhes usam
  o amarelo FT-02. A seta acompanha a cor do texto do botão.
- O contador de Pendentes usa o mesmo raio de 6px do selo da sidebar.
- O Dashboard usa uma versão resumida do mesmo card, com cores compartilhadas,
  selo de data, valor, link de detalhes e contador total no cabeçalho.

## 2026-11-16 — Detalhe da solicitação em Editando

- A tela do promotor mostra status, evento, mensagem, contato sem duplicação
  e pacote com o valor registrado no envio. Formulário e detalhe compartilham
  os ícones de data, horário e local.
- Depois de aceitar, recusar ou voltar para pendente, a tela mostra o estado
  atualizado do pedido. Pedidos aceitos têm um link para Meus Serviços.
- A mensagem e as ações ficam junto aos dados do evento. O pacote usa
  foto menor e mostra os itens ao lado do valor registrado no envio.
- Novos pedidos guardam uma cópia dos itens do pacote, usada no detalhe
  e no histórico do cliente. No detalhe de pedidos antigos, a lista atual
  pode aparecer identificada como tal.
- No detalhe, o contato fica acima do evento. A coluna do pacote é mais
  larga, com foto maior e valor na base, alinhada à coluna do evento.
- O formulário exige um número inteiro positivo de convidados, respeitando
  a capacidade do promotor. Novos pedidos sem quantidade são rejeitados.

## 2026-11-16 — Cards de solicitações pendentes em Editando

- O Dashboard e a página Solicitações compartilham o mesmo card, com pacote,
  foto ou avatar padrão do cliente, data, horário, convidados, local e prévia da mensagem.
  O endereço quebra dentro do card em telas estreitas.
- O mesmo avatar padrão do topo aparece para clientes sem foto em solicitações
  e serviços, nas listas e nos detalhes do painel.
- O Dashboard mostra até três pendências por ordem de recebimento e leva à
  lista completa pelo link Ver todas.

## 2026-11-16 — Próximos serviços do Dashboard em Editando

- O resumo mostra até três serviços aceitos, do pedido mais recente ao mais antigo.
  Cada linha abre o detalhe e destaca período, pacote, cliente e cidade
  quando presente no endereço registrado.
- No desktop, a caixa se alinha ao gráfico e reserva altura para três
  serviços. As linhas crescem quando o texto quebra, e o selo de calendário
  mantém seu fundo próprio ao passar o mouse.

## 2026-11-16 — Menu e logo do painel em Editando

- O menu de perfil do promotor oferece Dashboard na vitrine e no painel,
  com a mesma cor de texto e de foco nos dois.
- Removidos o contorno e o fundo próprio do bloco da logo. A barra lateral
  usa o roxo escuro original como fundo uniforme, sem os degradês.

## 2026-11-16 — Preço mínimo automático em Editando

- A vitrine, o filtro de preço e as duas visões do perfil calculam o preço
  mínimo pelo pacote com o menor preço cadastrado. O campo manual foi removido
  da edição de perfil.
- Removida a coluna de preço mínimo manual do banco compartilhado por
  Editando e Atual, dos esquemas de instalação e dos cadastros de demonstração.
  A migração está em `Editando/Banco/remover_preco_minimo_manual.sql`.
- Perfis sem pacotes exibem o estado vazio. Links para pacotes inexistentes
  voltam ao perfil público.

## 2026-11-15 — Menu de perfil da vitrine em Editando

- O menu de perfil das páginas públicas mantém as cores de texto e foco
  da Home, inclusive no formulário de solicitação.
- As opções do menu usam Poppins Medium (peso 500) para dar mais destaque aos textos.

## 2026-11-15 — Histórico do cliente em Editando

- Os cards de Minhas solicitações destacam promotor, status, pacote, valor e
  período com horário. Endereço e mensagem ficam nos detalhes expansíveis.
- O prazo de cancelamento aparece ao lado da ação, e o título da página
  segue o alinhamento dos cards.
- Os cards do cliente mostram a foto e até três itens do pacote disponível.
  O nome e o preço do pedido vêm do registro gravado no envio.
- Aceitar ou recusar no painel do promotor atualiza o status e cria um aviso
  não lido no sino do cliente. O aviso abre o card correspondente no histórico.

## 2026-11-15 — Formulário de solicitação em Editando

- O botão Enviar Solicitação usa fundo roxo e texto com peso 600.
- A confirmação de envio usa o ícone vetorial de sucesso no roxo da FT-02.
- O formulário usa os acentos da FT-02 e o card lateral do promotor mostra
  o telefone clicável junto da avaliação e da localização.
- O valor no card do pacote selecionado usa o grafite do formulário.
- As datas de início e término aceitam calendário e digitação em
  DD/MM/AAAA. O campo de convidados rejeita valores acima do limite
  durante a digitação ou colagem e mostra o aviso na hora.
- O calendário das duas datas tem interface em português com a paleta do
  formulário e seleção de mês, ano e dia.
- A janela do calendário mantém seis linhas, abre ao lado do ícone quando
  há espaço e fecha ao clicar fora, sem escurecer a página.
- O dia de hoje tem destaque amarelo no calendário.
- As seis semanas mostram também as datas dos meses vizinhos em cinza,
  com seleção direta.
- Quando o horário final é anterior ao inicial, o formulário marca
  “Termina em outro dia” e exige uma data de término posterior.

## 2026-11-15 — Botões do perfil do promotor em Editando

- A prévia com preço e botão lado a lado foi revertida. O preço volta
  à caixa com ícone, acima do botão em largura total.
- Os botões ativos de solicitação dos pacotes voltaram ao roxo anterior.
- No perfil público, o nome fantasia aparece como título, como na Home.
  O nome próprio fica abaixo quando for diferente do título.
- Visualizar perfil ganhou contorno roxo e os dois botões passaram a usar
  peso de fonte 500. No hover, só o fundo muda, para lilás ou amarelo suave.
- O botão Editar de Sobre mim passou a compartilhar o estilo de Editar perfil.
- Os ícones de especialidades acompanham a cor das etiquetas. No perfil
  público, as etiquetas e as ações usam a FT-02. Os títulos e o texto
  descritivo usam os tons de grafite do painel. A barra superior usa o
  mesmo roxo da Home.

## 2026-11-14 — Paleta do conteúdo do painel em Editando

- Cabeçalhos e ações em roxo do painel usam os tons da paleta FT-02.
- Visualizar perfil e Editar perfil usam fundo transparente e texto regular.
  Editar perfil usa amarelo `#F5B600` no contorno, no texto e no ícone.
  No hover, os dois botões realçam o contorno, o texto e o ícone.

## 2026-11-14 — Barra lateral do promotor em Editando

- A barra compartilhada segue a referência do Figma, com logo emoldurada,
  ícones de contorno, destaque lilás e Relatórios após Minha Assinatura.
- Solicitações mostra a contagem de pedidos pendentes do promotor quando
  houver pendências. No celular, a navegação continua na parte inferior.
- No desktop, a barra passou a 300 px, com itens maiores e espaçamento
  vertical ajustado à altura da tela. Sair usa o vermelho da paleta no hover.

## 2026-11-14 — Menu de conta da vitrine em Editando

- Na vitrine, a lupa branca fica sobre um botão amarelo e os cards usam o
  mesmo marcador de localização amarelo preenchido do perfil.
- O menu mostra "Meu perfil" para promotores e mantém a edição de conta e as
  solicitações enviadas no menu de clientes. Promotores também são direcionados
  ao próprio perfil caso acessem diretamente a rota de salvamento da vitrine.

## 2026-11-14 — Sobre mim e área do espaço em Editando

- O segundo card do perfil tem um modal próprio para frase, descrição e
  especialidades. O endereço do espaço aceita área opcional em m².
- O campo de área formata milhares e decimais no padrão brasileiro. A metragem
  cadastrada aparece entre as etiquetas de Especialidades no painel e no perfil público.
- O SQL de demonstração preenche a área dos seis espaços cuja descrição já
  informa a metragem. Os mesmos valores foram preenchidos nas contas demo locais.
- As etiquetas de Aniversário e Corporativo usam ícones no modal e nos perfis.

## 2026-11-14 — Tipo de festa na solicitação em Editando

- Organizadores recebem pedidos com os tipos cadastrados ou com texto livre
  quando a lista está vazia. Espaços só pedem o tipo se cadastrarem opções no
  modal do perfil. Buffets dispensam o campo, e solicitações sem tipo usam o
  nome do pacote como título no histórico e no painel.

## 2026-11-14 — Contato e endereço do promotor em Editando

- O valor mínimo por evento usa a mesma escala tipográfica no painel e no
  perfil público. No perfil público, o telefone acompanha a cor da localização
  e usa ícone amarelo e número sem sublinhado.
- O perfil público e o painel mostram o telefone do promotor junto da avaliação
  e da localização, com ícone amarelo preenchido e link para ligar quando há um
  número válido.
- O ícone de telefone passou a 20 px nas duas visões para compensar a margem
  interna do SVG e aproximar o tamanho visível ao dos outros ícones.
- O endereço fixo na solicitação usa fundo e borda mais escuros para
  diferenciá-lo dos campos editáveis.

## 2026-11-14 — Período no formulário de solicitação em Editando

- Formulário compartilhado com datas em dia/mês/ano, horários de 24 horas e
  data de término opcional para eventos que terminam em outro dia.
- A solicitação preserva o período no rascunho e no pedido. O painel exibe
  as duas datas e respeita o horário final ao liberar serviços de vários dias.
- Seções de contato e evento, botão com Poppins e campos lado a lado com
  alturas iguais, inclusive quando o endereço do espaço quebra linha.
- Na solicitação, o cartão do promotor omite o preço mínimo e o cartão do
  pacote selecionado exibe o próprio preço.
- O formulário mostra a capacidade de qualquer promotor que cadastrou um limite
  no perfil e bloqueia solicitações acima desse número de convidados.
- Rótulos de data, horário e endereço ganharam ícones. O limite de convidados
  aparece uma vez abaixo do campo, e o botão de envio usa o amarelo da marca
  com texto branco.

## 2026-11-14 — Filtro de preço na vitrine em Editando

- A busca tem um campo opcional de preço por evento “Até”, com prefixo R$ e
  máscara brasileira. Links antigos com faixa usam o limite máximo.
- A mesma máscara e validação em reais atendem o preço mínimo no perfil do
  promotor e o preço dos pacotes. Digitar valores inteiros não vira centavos.
- A contagem da busca exibe zero sem preencher com um dígito extra.
- As abas da vitrine abrem cada categoria sem filtros. Apagar o preço mostra
  “Sem limite” no campo. A lista só atualiza com Enter ou pelo botão de pesquisa.
- Campos de dinheiro vazios mostram `0,00` ao receber foco. A digitação começa
  pelos reais, e os centavos podem ser editados com as setas ou com um clique.

## 2026-11-13 — Modal Editar perfil do promotor em Editando

- O modal do promotor usa a largura de 640px e os cantos de 14px do modal do cliente.
  A barra de rolagem lateral fica visível com um indicador fino e trilho transparente.
- A área rolável fica dentro da moldura arredondada, com recuo nas extremidades.

### Alinhamento do Dashboard em Editando
- O cartão Próximos Serviços acompanha a altura do gráfico quando os dois estão lado a lado.
  No celular, os cartões ficam empilhados e o gráfico rola dentro do próprio cartão.
- Verificado com cinco solicitações pendentes do promotor. O Dashboard mostra as três
  mais recentes e a página Solicitações mostra as cinco.

### Dados de contato no formulário de solicitação em Editando
- Em `Editando/Cliente/solicitacao.php`, o rascunho guarda e restaura apenas os dados
  do evento. Nome, e-mail e telefone são preenchidos com os dados da conta e
  mantêm o valor enviado quando o formulário volta com erro de validação.

### Máscaras de contato e horários na solicitação em Editando
- Os campos de telefone, CPF e CNPJ têm limite no HTML e na máscara compartilhada,
  inclusive na colagem. Telefone aceita até 11 dígitos locais e o prefixo +55.
- A máscara compartilhada de telefone e CPF formata os campos durante a digitação,
  na colagem e ao abrir formulários com valores preenchidos. O PHP valida o formato
  nos perfis, nos cadastros e na solicitação.
- A solicitação usa seletores de data, início e fim. Rascunhos antigos são convertidos
  ao abrir o formulário e os valores gravados mantêm o formato usado pelo painel.

### Endereço do espaço de festas em Editando
- O modal do promotor permite cadastrar um endereço por espaço. Sem endereço completo,
  o atalho de novo pacote abre essa seção e o servidor rejeita a criação do pacote.
- A solicitação usa o endereço do perfil em modo de leitura e grava uma cópia no pedido.
  Espaços sem endereço precisam completar o perfil para receber novas solicitações.
- A migração de conexão adiciona as colunas do endereço em bancos existentes.
- O `Editando/Banco/perfis-demo.sql` preenche endereços fictícios nos dez espaços
  demo e garante as colunas antes de recriar as contas.

### Perfis do cliente e do promotor em Editando
- A visão do cliente centraliza o conjunto de cartões com margens externas iguais.
  A foto e os dados ficam à esquerda e o preço à direita no cartão principal.
- O perfil público acompanha a largura, o espaçamento, a capa e o avatar do painel.
  O nome do promotor aparece abaixo do avatar, seguido do nome fantasia.
- No painel, as etiquetas de especialidades e capacidade usam o roxo e os cantos
  de 8px do perfil público.

### Preço mínimo na vitrine em Editando
- Os cards da busca mostram o preço mínimo por evento em reais ou “Sob consulta”
  quando não há valor cadastrado. A grade mantém os cards da mesma altura e
  compartilha a largura centralizada com os filtros e o título.

## 2026-11-12 — Avaliação no perfil público em Editando

- Em `Editando/Cliente/dados.php` e `Editando/Cliente/perfil.php`, o botão, o modal e o envio
  da avaliação exigem o ID explícito do perfil e uma solicitação do cliente para aquele promotor.
  O formulário aponta para o mesmo perfil e a regra de status pendente ou aceito foi preservada.

### Escala do perfil público em Editando
- Em `Editando/Cliente/css/perfil.css`, restaurados banner de 150px, avatar de 96px,
  tipografia Poppins e espaçamentos compactos. Os pacotes ocupam a largura disponível,
  com fotos em 16:9 e 16px até a descrição. No celular, a galeria fica em duas colunas.

### Modal Editar perfil do cliente em Editando
- O modal da vitrine mostra o avatar padrão em contraste com o fundo e oferece remoção de foto.
  A prévia muda na hora e a exclusão é gravada ao salvar, com atualização do avatar na topbar.
- Cabeçalho, campos e botão seguem o visual do modal do promotor. No celular, telefone e CPF
  ficam em linhas separadas.

### Capacidade e ação principal no modal do promotor em Editando
- Em `Editando/Perfil/CSS/perfil.css`, o campo numérico de capacidade máxima usa a mesma
  borda, largura e espaçamento dos outros campos. O botão Salvar usa o amarelo da marca
  com texto e ícone brancos. A preferência por mais amarelo na interface
  foi registrada em `AGENTS.md`.

### Modal de pacotes em Editando
- Em `Editando/Perfil/CSS/perfil.css`, o modal de pacotes passa a ter até 560px de largura,
  prévia de imagem em 16:9 e mais espaço para a descrição. O botão Salvar Pacote usa
  o amarelo da marca com texto e ícone brancos, seguindo o modal Editar perfil.

### Seleção de cidade no perfil do promotor em Editando
- O campo Cidade abre as opções da UF preenchida e só filtra pelo nome quando o promotor
  digita. Com a UF em branco, apresenta toda a lista disponível e preenche o estado
  correspondente quando uma cidade é escolhida.
- O campo Estado (UF) também oferece sugestões. Escolher uma UF filtra as cidades e
  limpa a cidade preenchida quando ela não faz parte das opções da UF escolhida.

### Filtro de cidade na vitrine em Editando
- A busca fica acima da lista rolável, com hover reservado às opções e aviso quando
  não há resultados. O filtro lista apenas municípios válidos do RJ com promotores
  na categoria e usa a grafia da lista oficial do projeto.

## 2026-10-25 — Mais contas demo por categoria

### Compressão das imagens do projeto
- Otimizadas imagens em `assets/`, `uploads/` e referências de `Banco/imagens Cadastro/`,
  mantendo nomes, caminhos e formatos. JPEG/WebP recomprimidos com alvo de qualidade 80;
  PNG com compressão e paleta limitada a qualidade 90–100, preservando transparência.
  Resoluções excessivas reduzidas conforme o uso; capturas longas preservam a largura de leitura.
- 151 arquivos reduzidos entre 567 verificados: total de 153,58 MiB para 112,04 MiB
  (41,54 MiB economizados; redução de 27%). Arquivos pequenos ou sem ganho foram mantidos.
  Originais auxiliares do editor de enquadramento e SVGs não foram recomprimidos.
- Todos os arquivos decodificados após a otimização; formatos e transparência dos PNGs
  conferidos sem erros, além de comparação visual de amostras. Hashes, tamanhos e dimensões
  das 120 fotos novas sincronizados em `Banco/perfis-demo-imagens.json`. SQL e banco inalterados.

### Fotos diferentes e recriação das contas demo
- Substituídas as 120 cópias dos perfis 6..10 por fotografias novas do Pexels: avatar, banner,
  dois pacotes e quatro imagens de galeria por promotor. Arquivos JPEG independentes,
  conferidos por decodificação e comparação de hashes; fontes, licença e hashes registrados
  em `Banco/perfis-demo-imagens.json`. Conferência visual por folha de contato.
- `Banco/perfis-demo.sql`: após escolha explícita do usuário, voltou a apagar/recriar somente
  as 32 contas demo (30 promotores e os dois clientes originais), com seus registros associados,
  incluindo solicitações. Contas fora da lista são preservadas; edições e IDs dos demos são
  descartados. A sessão das contas demo deve ser renovada após importar.
- Testado em MariaDB isolado: duas importações mantiveram 10 promotores por área, 60 pacotes,
  120 registros de galeria e dois clientes demo. Edição e solicitação de um demo foram removidas;
  conta e solicitação fora da lista permaneceram. Banco principal não foi alterado.

### Contas consolidadas no mesmo arquivo
- `Banco/perfis-demo.sql`: cinco novos buffets (`buffet6..10`), cinco espaços (`local6..10`)
  e cinco organizadores (`organizador6..10`) integrados ao mesmo arquivo. Mantidos somente
  os clientes originais `cliente1` e `cliente2`, conforme correção do escopo pelo usuário.
  Removido o arquivo adicional. Emails com `@nathus.com`, senha `123`; cada promotor tem área,
  especialidades quando aplicáveis, dois pacotes e quatro imagens de galeria.
- Imagens próprias em `uploads/perfil-promotor`, `banner`, `pacotes` e `galeria`, com slugs 6..10.
  Os arquivos do seed usam as fotos novas descritas acima, sem compartilhar arquivos entre contas.

## 2026-10-23 — Vitrine nova (`home/home.php`) + documentação sincronizada com o código

### Preservar foto do promotor ao salvar pacote
- App localizado em `Nathus - Perfil Promotor/`, nome atual da antiga pasta `Nathus - Perfis Demo`;
  documentação de execução atualizada para o caminho existente.
- `Includes/imagens.php`: limpeza de uploads usa `midia_publica()` para reconhecer arquivos
  válidos em caminhos legados, como a renderização. Antes, ao voltar do salvamento de pacote,
  a limpeza podia anular `promotor.foto_perfil` mesmo com o arquivo ainda presente em uploads.
  A mesma correção preserva referências válidas de banner, galeria e imagem de pacote.
- `Perfil/perfil.php`: banner passa pelo resolvedor compartilhado, incluindo caminhos legados.
- `Tests/pacote-foto-perfil.php`: reproduz handler de criar pacote seguido da limpeza da página
  e resolução do avatar. Caso legado falhou antes da correção; legado, canonical e ausente
  passaram depois. Todos os testes usam ROLLBACK; nenhum pacote ou alteração de conta persistiu.
  Testes de migrações e lint dos arquivos alterados passaram.
- Investigada a identificação da Rosana (`buffet1`): banco permanece como promotor; login,
  home e perfil público mostram Promotor/Meu perfil. Simulação de `usuario_tipo` antigo também
  passou. A mudança transitória relatada deixou de ocorrer após novo login e não foi reproduzida;
  nenhum tipo de conta foi alterado nesta investigação.

### Espaço entre imagem e descrição dos pacotes públicos
- Somente em `Cliente/css/perfil.css`: espaço inferior da imagem ajustado para 16px,
  incluindo o botão de ampliar foto, cujo reset de margem anulava o espaçamento anterior.
  O painel do promotor mantém seu espaçamento atual.

### Pacotes maiores na visão do cliente
- `Cliente/css/perfil.css`: grid dos pacotes passa a `auto-fit` com mínimo fluido de 320px.
  Os cards ocupam a largura disponível sem reservar colunas vazias: dois pacotes ficam
  maiores lado a lado no desktop e em coluna no celular. Restante do perfil mantém a escala compacta.
  Conferido no perfil `id=71`: cards de 503px no desktop e 332px em 390px de viewport, sem
  overflow horizontal. Revisão Better Design indisponível por cota; conferência no navegador.

### Perfil público com a mesma escala compacta do painel
- `Cliente/css/perfil.css`: aplicada a redução de escala também à visão do cliente. Banner
  150px, avatar 96px, nome 24px (22px no celular), texto 14px e Poppins explícita. Reduzidos
  padding das seções, intervalos, tags, preço e botões; ações principais têm raio 6px.
- Pacotes usam grid fluido com mínimo de 240px limitado à coluna e espaçamento de 16px.
  No celular, galeria em duas colunas e ações com altura mínima de 44px.
- Conferência no perfil público `id=71`: apresentação de 426px para 296px de altura no desktop;
  Sobre mim de 349,75px para 269,19px. Verificado em 390px sem overflow horizontal, mantendo
  conteúdo e ações. Better Design indisponível por cota HTTP 402; revisão no navegador local.

### Edição de fotos simplificada a partir da referência do Twitter
- `Includes/js/enquadramento.js` e `Includes/css/enquadramento.css`: substituídos os controles
  técnicos por imagem arrastável, moldura do recorte, uma barra de zoom e Voltar/Aplicar no
  cabeçalho. Imagem fora do recorte fica escurecida; avatares têm moldura circular. Exportação
  mantém apenas o recorte, sem borda/overlay. Setas do teclado também posicionam a imagem.
- `Perfil/perfil.php`: banner vazio mostra só adicionar; preenchido mostra trocar, editar
  visualização e remover sobre a imagem. Avatar mantém somente trocar foto. A mesma janela
  compartilhada continua ativa nos cadastros, conta do cliente, pacotes e galeria, com lápis
  sobre as imagens em lugar de botões de ajuste separados.
- `Perfil/salvar-editar-perfil.php`: remoção do banner pelo campo `remover_banner`, limitada
  ao promotor logado e efetivada ao salvar. Aplicar uma substituição cancela a remoção pendente.
- Câmeras `assets/editar_foto_perfil.svg`, `editar_banner.svg` e `cadastro-camera.svg` agora
  preenchidas e mais encorpadas; novos `assets/midia-*.svg` para voltar, editar, remover e zoom.
  Avatar do painel e da conta usam câmera 24px, com cache-busting no modal da conta.
- Conferidos no navegador: arraste real, zoom, aplicação sem envio do perfil, retorno sem
  aplicar, banner com/sem foto, prévia circular, pacote novo/existente e encaixe móvel 390px.
  Lint dos 64 PHP, sintaxe JS e testes de migrações passaram. Nenhuma alteração de conta foi
  salva durante a conferência. Better Design permanece sem cota (HTTP 402).

### Enquadramento de todas as mídias e acabamento do modal de pacote
- Criados `Includes/enquadramento.php`, `Includes/css/enquadramento.css` e
  `Includes/js/enquadramento.js`: editor compartilhado com arraste, zoom, posição horizontal/
  vertical, centralização e cancelamento. Incluído nos cadastros cliente/promotor, modal da
  conta e perfil do promotor: foto, banner, pacotes e galeria, inclusive imagens já existentes.
- `Perfil/perfil.php`, `Perfil/js/perfil.js` e `Perfil/CSS/perfil.css`: modal de pacote ampliado
  para 600px (limitado à tela), Poppins, campos suaves, rótulos e botões com raio 6px. Preview
  sem compressão pela altura da tela; modal rola quando necessário. Estado do recorte é limpo
  ao mudar de pacote. Controles de banner/foto preservam a sobreposição do avatar.
- Imagens dos pacotes agora têm proporção 16:9 também no perfil público e na solicitação
  (`Cliente/css/perfil.css`, `Cliente/css/solicitacao.css`), para corresponder ao recorte.
- `Includes/imagens.php`: preserva o original validado (até 5 MB) em `uploads/originais/`,
  permitindo novos recortes sem acumular perda; limpeza remove original e recorte. Sem mudança
  no schema do banco. Cadastro do promotor corrigido para limpar a pasta `perfil-promotor`.
- `Perfil/galeria-salvar.php`: permite reenquadrar foto existente, validando seu dono;
  `galeria-apagar.php` usa a limpeza compartilhada. `pacote-salvar.php` valida dono antes do upload.
  Falhas no upload de pacote/galeria retornam mensagem de erro, sem anunciar sucesso.
- Verificações: lint de 64 PHP, sintaxe dos dois JS, testes CLI de migrações; navegador com
  pacote, banner, avatar, galeria e seleção de foto no cadastro, incluindo largura 390px.
  Upload HTTP isolado confirmou dimensões do recorte, preservação do original e remoção de
  ambos, sem gravar contas ou pacotes. Arquivos temporários de teste removidos.
  Revisão Better Design indisponível por cota HTTP 402; conferência visual feita localmente.

### Densidade mais compacta de Meu perfil
- `Perfil/CSS/perfil.css`: reduzida a escala da página inteira após feedback do usuário,
  mantendo Poppins e as cores aprovadas. Banner 150px, avatar 96px, nome 24px, títulos 18px,
  textos 14px e menos padding/espaço entre blocos. Imagens de pacotes 140px e listas mais compactas.
- Cartão de adicionar pacote deixou de esticar até a altura dos pacotes; botões móveis mantêm
  altura mínima de 44px. Modais e topbar preservados.
- Verificação desktop: bloco de apresentação passou de 445px para 371px de altura, sem
  overflow horizontal; escala e encaixe móvel conferidos. Better Design segue sem cota (HTTP 402).

### Acabamento visual da página Meu perfil do promotor
- `Perfil/CSS/perfil.css`: substituída Nunito por Poppins na página e controles, incluindo
  overlays. Títulos, leitura dos textos, espaçamentos e bordas foram alinhados ao modal de edição:
  seções com cantos 20px, superfícies suaves, ações de edição amarelas e botões arredondados.
- Pacotes ganharam grid fluido com mínimo de 260px limitado à coluna, tipografia maior,
  preços com fundo suave e ações consistentes. Galeria mantém fotos quadradas e usa
  área de adição clara. Estados de hover/foco preservam o comportamento simples do projeto.
- `Perfil/perfil.php`: classes visuais dos botões renomeadas para `btn-principal` e
  `btn-secundario`; removidas as antigas variantes azul/roxa sem alterar os handlers.
- Ajustes móveis em 390px: cards sem overflow horizontal, galeria em duas colunas e topbar
  com título e conta acomodados sem corte da seta. Nenhum formulário foi enviado.
- Better Design segue indisponível por HTTP 402; validação visual local e estilos renderizados
  conferidos. Dados, endpoints e regras de negócio não foram alterados.

### Múltiplos campos de atuação no perfil do promotor
- Inspecionado `promotor_campo` no MySQL: a tabela já permite vários campos e contém contas
  com múltiplas áreas; não foi necessária alteração de estrutura do banco.
- `Perfil/perfil.php`: substituídos radios por checkboxes, restaurando todos os campos salvos.
  Campos de atuação vêm antes dos detalhes; especialidades de buffet/organização e capacidade
  do Local aparecem conforme as áreas selecionadas, inclusive juntas.
- `Perfil/salvar-editar-perfil.php`: removido o corte em um item; valores são validados e
  deduplicados. Salva os dois grupos de especialidades selecionados e preserva detalhes das
  áreas desmarcadas. `Perfil/js/perfil.js` oculta/desabilita os grupos inativos no formulário.
- `Perfil/CSS/perfil.css`: ajuda de seleção múltipla e campo numérico no padrão do modal.
- `Tests/perfil-campos.php`: integração local com transação revertida; passaram seleção de
  três áreas (incluindo duplicada/inválida), uma e nenhuma, especialidades combinadas e capacidade.
  Lint PHP, sintaxe JS e seleção simultânea/alternância no navegador conferidos sem salvar a conta.
  Better Design permanece indisponível por cota (HTTP 402).

### Modal do promotor com estilo do cliente
- `Perfil/CSS/perfil.css` e `Perfil/perfil.php`: modal Editar perfil passou a usar Poppins,
  título 26px, descrição curta, campos de fundo lilás suave com raio 12px, largura 640px,
  cantos 20px, scrollbar discreta e botão Salvar amarelo arredondado como no modal do cliente.
  Banner em sangria total e avatar sobreposto de 96px foram preservados.
- Corrigida a camada do modal para ficar acima da navegação inferior móvel, que cobria Salvar.
- Validação: lint PHP e inspeção desktop/390px, sem overflow horizontal. O formulário não foi
  enviado. Better Design continua indisponível por cota (HTTP 402); a referência externa
  NATHUS PROJETINHO não existe neste ambiente, então foi preservado o layout de mídia atual.

### Foto do cliente no modal Editar perfil
- `home/conta-modal.php` e `home/css/vitrine.css`: ocultado o botão nativo de arquivo;
  preview circular clicável de 96px com placeholder visível, overlay escuro e câmera no hover/foco.
  Enter/Espaço abrem o seletor; imagem selecionada aparece no preview e URLs temporárias anteriores
  são liberadas. Upload e gravação continuam pelo formulário existente.
- Verificado no navegador com cliente demo: input oculto, foto carregada, overlay em foco e
  seleção de imagem atualizando o preview sem salvar. Lint PHP sem erros. Better Design segue
  indisponível por cota (HTTP 402); referência externa NATHUS PROJETINHO não foi encontrada.

### Clientes de demonstração no seed
- `Banco/perfis-demo.sql`: adicionadas as contas `cliente1@nathus.com` e
  `cliente2@nathus.com`, senha `123` em bcrypt, com `tipo_conta = 'cliente'` e sem linha
  em `promotor`. Incluídas na limpeza idempotente do seed (agora 17 emails demo).
- Alterado somente o arquivo SQL e a documentação; seed não importado no banco local.

### Auditoria de estrutura e otimização das migrações
- Avaliado o app ativo: 60 PHP, 34 CSS e 8 JS, totalizando 554.124 bytes de código antes
  desta alteração. O volume em disco vem principalmente de mídia e de um ZIP interno de
  172.194.869 bytes; arquivos legados não carregados não foram tratados como custo de runtime.
- `Includes/migracoes.php`: extraídas de `Includes/conexao.php` as funções de compatibilidade,
  preservando os nomes e os chamadores existentes. O módulo pode ser carregado sem abrir conexão.
- `garantir_texto_solicitacoes()`: substituídos três `ALTER TABLE MODIFY` incondicionais por
  inspeção do schema e alteração apenas das colunas incompatíveis. Preserva capacidade maior
  de texto, inclusive ao ajustar nulabilidade, e não conclui a migração em caso de falha.
- `Tests/migracoes.php`: regressão CLI sem banco real; reproduziu 3 ALTERs indevidos antes
  do ajuste e passou nos 5 cenários após a correção (atual, legado, capacidade maior,
  nulabilidade e falha). MySQL local confirmou 0 ALTERs com schema atual.
- Relatório visual temporário: `%TEMP%/architecture-review-1792755293148.html`. Identificados
  para próximas etapas: normalizações globais por sessão, consultas repetidas de identidade,
  falta de índices de proprietário nas solicitações e limpeza opcional de arquivos legados.
  Não houve remoção de backups/uploads, reescrita do app ou alteração visual.
- Verificação HTTP: login, home e as três categorias responderam 200 sem erros PHP no HTML.

### Alinhamento horizontal das topbars
- `Includes/css/layout.css`: padding lateral do painel padronizado em 28px, igual ao da home.
- `home/css/vitrine.css`: aplicada a mesma scrollbar oculta do painel, sem impedir rolagem,
  e largura do botão do sino ajustada para 27px. Antes, padding 28px/36px, scrollbar de 15px
  só na home e botões de sino de larguras diferentes deslocavam os componentes entre páginas.
- Conferência no navegador em 1280px: perfil em x=1004,05 e sino em x=951,05 nas duas páginas,
  ambos com 28px até a borda direita. Better Design continua indisponível por cota (HTTP 402).

### Topbar do painel na altura da home
- `Includes/css/layout.css`: `.topo-painel` passou de 92px para 74px, igual à home,
  com `flex-shrink: 0` para preservar a altura em layouts flex. Aplica-se ao shell compartilhado
  de Dashboard, Perfil, Serviços, Solicitações e Assinatura.

### Menu de perfil simplificado
- Removido o item "Dashboard" dos menus em `home/topo-vitrine.php` e `Includes/header.php`,
  a pedido do usuário: o promotor acessa o painel por "Meu perfil". Convenção atualizada em `AGENTS.md`.

### Notificações nas páginas públicas
- `home/topo-vitrine.php`: sino para cliente e promotor logados, usando o SVG do dashboard.
  Painel lista até 3 solicitações: pendentes recebidas pelo promotor (`id_promotor`) ou pedidos
  recentes do cliente com status (`id_cliente`, sem depender de email de sessão). Indicador de
  pendência quando presente na lista; links de cliente abrem Minhas solicitações, sem acesso ao painel.
- `home/css/vitrine.css`: sino branco sobre a topbar roxa, painel no estilo do dashboard e
  encaixe móvel. Menus alternam entre si, fecham ao clicar fora/Escape e atualizam `aria-expanded`.
- Verificação: lint PHP, painel do promotor no navegador, viewport 390px, Escape e renderização
  PHP com cliente real verificando propriedade e links. Better Design segue indisponível por HTTP 402.

### Conta com o padrão visual do dashboard
- Ajuste posterior em `Includes/css/global.css`: removidos fundo branco, raio e padding externo
  da variante pública, conforme esclarecimento do usuário. O perfil fica diretamente sobre o
  roxo da topbar, com nome/seta brancos e tipo/contorno em lilás claro; layout do dashboard mantido.
- `Includes/css/global.css`: centralizados os estilos do componente `.conta-perfil`, extraídos
  de `Includes/css/layout.css`. Dashboard e páginas públicas compartilham Poppins, nome 20px,
  tipo 15px, avatar 46px com contorno, seta preenchida e o mesmo menu suspenso.
- `Includes/header.php` e `home/topo-vitrine.php`: usam o componente compartilhado; os links
  "Meu perfil" e "Dashboard" dependem de `ehPromotor()`. Clientes mantêm "Editar perfil" e "Sair".
  As guardas existentes das rotas do painel continuam exigindo conta promotor.
- `home/css/vitrine.css`: removido o visual anterior do perfil. A variante pública tem base
  branca sobre a barra roxa e mantém o nome abreviado quando falta espaço no celular.
- Verificação: lint dos PHP, comparação dos estilos renderizados no dashboard e na home,
  encaixe em 390px e menu cliente com sessão desatualizada. Better Design retorna HTTP 402
  (cota esgotada), portanto sua revisão automática permanece indisponível.

### Entrada pela pasta do app
- Corrigido `Nathus - Perfis Demo/index.php`, que ainda apontava para uma subpasta
  antiga inexistente; agora redireciona diretamente para `home/homeAnt.php`.
- Removido o `index.php` recém-adicionado na raiz após esclarecimento do usuário:
  `Nathus` é apenas uma pasta de organização; a entrada é `Nathus - Perfis Demo/`.
- `AGENTS.md`: atualizado o caminho atual do app, agora diretamente em `Nathus - Perfis Demo`,
  e documentada a entrada pela pasta do app.

### Componente de conta da vitrine
- `home/css/vitrine.css`: botão de perfil agora herda a Poppins da página; nome 16px, tipo 13px,
  avatar 44px e seta 16×12px para melhorar a leitura. Preservados cores e layout da vitrine.
- `home/topo-vitrine.php`: "Meu perfil" abre `Perfil/perfil.php` somente para promotores,
  identificados por `ehPromotor()` (banco). Clientes veem apenas "Editar perfil" e "Sair";
  removido "Minhas solicitações" desse menu conforme solicitado. Escape fecha e devolve o foco.
- `AGENTS.md`: corrigido o caminho atual do app para `Nathus18.10/Nathus - Perfis Demo`
  e documentadas as opções do menu e a tipografia do componente.
- Verificação: lint PHP sem erros; login demo de promotor, Escape e abertura da edição conferidos
  no navegador; renderização PHP de cliente com sessão de tipo desatualizado confirmou apenas
  as duas opções. Layout móvel contém o componente na coluna disponível e abrevia nomes longos.
  Better Design indisponível por cota esgotada (HTTP 402) nas consultas de princípios e revisão.

### Adicionado
- `home/home.php` — catálogo público único: `?campo=organizadores|buffet|espaco` (whitelist,
  default `organizadores`) e `?busca=` (substring case-insensitive via `vt_bate()`). Substitui as
  três páginas antigas de listagem. Helpers `vt_esc()`, `vt_bate()`, `vt_menu()`, `vt_iniciais()` e
  `vt_href_perfil()`; inclui `home/conta-modal.php`.
- `home/topo-vitrine.php` — header compartilhado das páginas públicas, configurado antes do include
  por `$base_url`, `$vt_inicio` (default `homeAnt.php`), `$vt_asset`, `$vt_abas`, `$vt_acao`,
  `$vt_voltar` e `$secao_ativa`. As abas `vt-abas` apontam para `home.php?campo=...` e "Minhas
  solicitações" só aparece para quem não é promotor.
- `home/conta-modal.php` + `home/salvar-conta.php` — modal "Minha conta" (`dialog#vt-conta`) só
  renderiza com sessão; o POST valida nome/email/telefone/cpf/senha e responde `?ok=1` ou
  `?erro=email|senha|dados`. O `voltar` é whitelist (`home.php`, `Cliente/perfil.php?id=N&ver_como_cliente=1`,
  `Cliente/solicitacao.php?pacote=N&id=N`), como o `destinoAposLogin()` do `Login/`.
- `home/minhas-solicitacoes.php` — pedidos do cliente logado (`exigirCliente()`,
  `garantir_colunas_solicitacoes()`, helpers `ms_status`/`ms_categoria`/`ms_data`).
- `home/editar-conta.php` — redirect para `homeAnt.php?editar=1` (o modal abre na landing).
- `home/css/vitrine.css` — fonte ativa dos estilos públicos (prefixo `vt-` e cards `pro-`).
- Documentados na memória os arquivos que já existiam sem registro: `Includes/header.php`,
  `Includes/sidebar.php` (shell do painel do promotor), `Includes/perfil_foto.php`,
  `Includes/imagens.php` (`otimizar_e_salvar_imagem()`, `apagar_upload_antigo()`,
  `limpar_uploads_orfaos()`) e `Includes/cidades-rj.php` (`cidades_do_rio()`).

### Alterado
- `home/organizadores.php`, `home/buffet.php` e `home/espaco.php` — deixaram de renderizar listagem
  e viraram stubs de redirect (102–109 B) para `home.php?campo=organizadores|buffet|espaco`, para
  links antigos (ex.: landing e abas) não quebrarem.
- `Cliente/perfil.php` e `Cliente/solicitacao.php` — casca pública agora é
  `home/topo-vitrine.php` + `home/css/vitrine.css` (antes `Includes/topbar.php`/`topbar.css`).
- `AGENTS.md` — corrigido o caminho real do app (`Nathus18.10\nathus 7 de outubro - Copia`, não
  `NathusDoBem`), o mapa de pastas (vitrine, stubs, CSS órfãos, uploads), a tabela de componentes
  compartilhados e as convenções 2/6/7/9/11/13; adicionadas as convenções 14 (conta na vitrine).
  Removida a seção duplicada "Camadas de CSS (cadastro)".

### Obsoleto (mantido no disco, sem include)
- `Includes/topbar.php` + `Includes/css/topbar.css` — nenhum arquivo PHP os referencia.
- `home/css/home.css`, `home/css/catalogo.css`, `home/css/conta.css`, `home/css/org.css`,
  `home/css/style.css`, `home/css/buffet.css` — órfãos; a fonte ativa é só `home/css/vitrine.css`.

### Verificação
- Referências conferidas por busca: `topbar` não é incluído por nenhuma página;
  `topo-vitrine.php` é usado por `home/home.php`, `home/homeAnt.php`, `home/minhas-solicitacoes.php`,
  `Cliente/perfil.php` e `Cliente/solicitacao.php`; os `.css` órfãos não aparecem em nenhum PHP.

### Perfis demo da vitrine (seed)
- **Adicionado** `Banco/perfis-demo.sql` — seed idempotente com 15 promotores completos
  (`buffet1..5`, `local1..5`, `organizador1..5`, login `<usuario>@nathus.com` / senha `123`, hash
  bcrypt). Cada um tem perfil, banner, 2 pacotes, 4 fotos de galeria, campos
  (`buffet`/`local`/`organizacao`), especialidades válidas, cidade/estado, preço mínimo,
  capacidade, nota e nº de avaliações. A limpeza usa uma tabela temporária com os 15 e-mails e
  apaga na ordem galeria → pacotes → especialidades → campos → solicitações → promotor → cliente,
  então rodar de novo não duplica nem toca em contas de terceiros.
- **Adicionado** 60 imagens genéricas do Pexels em `uploads/` (`demo-<slug>-{perfil,banner,pacote-1,pacote-2,galeria-1..4}.jpg`);
  perfil 600×600, banner 1600×600, pacotes e galeria 900×900 (galeria-1 é 600×600), todas quadradas
  exceto o banner para casar com `aspect-ratio: 1` de `.item-galeria`.
- **Verificado**: reimportação mantém 15/30/60 linhas; as três abas da vitrine listam os cards com
  banner, avatar, frase, nota e cidade; filtros de especialidade/tipo de festa/capacidade/faixa/
  busca funcionam; login dos 3 tipos cai no `Dashboard`; `Perfil/perfil.php` e
  `Cliente/perfil.php` resolvem as 8 mídias por perfil; `limpar_uploads_orfaos()` não apaga nada.


## 2026-10-19 — MCPs do FreeBuff + ontologia de design do Nathus

### Adicionado
- `design-ontology.json` (raiz do projeto) — ontologia inicial no schema do pacote `design-ontology@0.1.0`
  (meta + 7 domínios + 1 reasoning chain). Liga a intenção "contratar promotor" à confiança,
  segurança, cultura de eventos e ao card com prova social/preço ancorado; inclui requisito de
  contraste 4.5:1. Serve ao MCP `design-ontology`, que valida e carrega o arquivo via `ONTOLOGY_PATH`.

### Configuração local (fora do repositório)
- `~/.agents/mcp.json` migrado para o formato padrão MCP (`command` string + `args` + `env`), que é o
  formato lido pelo FreeBuff — o formato do `opencode.json` (`command` como array + `environment`)
  causava `"<nome>" is not an MCP server: it needs a command, or a url.`
- Servidores: `memory`, `playwright`, `openrouter_image`, `uxonfly`, `design-ontology` e
  `better-design` (remoto, `url` + `headers`). Backup em `~/.agents/mcp.json.bak-20261019`.
- Skills do Matt Pocock instaladas em `~/.agents/skills` (38 skills, incluindo `grill-me`,
  `grill-with-docs`, `tdd`, `implement`); `mattpocock/skills` **não** é servidor MCP e saiu do `mcp.json`.
- Cópia das 38 skills em `.agents/skills/` (436 KB) — o FreeBuff Desktop carrega skills do
  **projeto** (`.agents/skills`), não do home por padrão, então a cópia é o que faz elas aparecerem
  no app. Todas validadas com `SKILL.md` contendo `name` e `description`.

### Verificação
- JSON de `~/.agents/mcp.json` e `design-ontology.json` válidos.
- Handshake `initialize` OK nos 5 servidores locais (memory, playwright, uxonfly, openrouter_image,
  design-ontology) e no remoto Better Design (HTTP 200, `serverInfo: BetterDesign 0.45.0`).
- `design-ontology` carrega o arquivo do projeto: `7 nodes, 1 chains loaded`.

## 2026-10-11 — Perfil do promotor: localização (cidade/UF) + listagens da home por tipo de atuação

### Adicionado
- `Perfil/perfil.php` — o modal "Editar perfil" ganhou os campos **Cidade** (`name="cidade"`, maxlength 60) e **Estado (UF)** (`name="estado"`, maxlength 2, `text-transform:uppercase`), logo após o Celular.
- `Includes/promotor.php` — novo helper compartilhado `foto_card_promotor($promotor, $base_url)`: resolve a foto do card (upload real → template `assets/home/oN.png` ids 101..121 → `assets/user_dashboard.svg`). Antes essa lógica estava duplicada dentro de `home/organizadores.php`.

### Alterado
- `Perfil/salvar-editar-perfil.php` — passa a sanitizar e persistir `cidade` (máx. 60) e `estado` (máx. 2, sempre maiúsculo, vazio → NULL) no `UPDATE promotor` (bind `"sssdssi"`).
- `home/buffet.php` / `home/espaco.php` — antes renderizavam **apenas** arrays estáticos; agora consultam o banco e listam os promotores reais cujo `promotor_campo.campo` é `buffet` (buffet) / `local` (espaço), como cards linkados ao perfil público, seguidos do fallback estático. Cada um ganhou seu filtro de busca para os cards reais.
- `home/organizadores.php` — passou a filtrar por `promotor_campo.campo = 'organizacao'` (ou por **nenhum** campo, para não esconder contas recém-criadas) e a usar o helper compartilhado `foto_card_promotor()`.
- `home/css/buffet.css` / `home/css/style.css` — mesmo tratamento de altura uniforme dos cards (`.buffet-cartao-link`/`.espaco-cartao-link` com `height: 100%`; card `flex` em coluna; preço `margin-top: auto`; grid `grid-auto-rows: 1fr`).

### Corrigido
- Perfil de promotor que marcava "Buffet"/"Local" não aparecia nas abas correspondentes da home (as páginas simplesmente ignoravam o banco). Agora aparece nas abas marcadas; e promotores buffet-only/local-only deixam de "vazar" para a aba de organizadores.

### Verificação
- `php -l` limpo nos 5 PHP alterados.
- HTTP 200 + contagem de cards reais: buffet → 5 (`buffet-cartao-link`), espaço → 4 (`espaco-cartao-link`), organizadores → 6 reais + 21 fallback (27); `sadf`/`oasdf`/`1asd` (buffet-only) **ausentes** em organizadores.
- `UPDATE promotor ... cidade/estado` testado via script temporário (grava `Rio de Janeiro`/`RJ` e reverte para NULL).
- Alturas medidas (`cards.cjs`): todos os cards com 257px nas 3 listagens (grid uniforme).

## 2026-10-10 — Home/Organizadores: cards com altura uniforme

### Corrigido
- `home/css/org.css` — os cards de organizadores tinham altura variável: `<a class="organizador-cartao-link">` (item do grid) esticava, mas o `<article class="organizador-cartao">` interno não, então o card "Buffet Teste" (sem "frase destaque") ficava ~23px mais baixo que os demais. Agora o link e o article usam `height: 100%`, o card é `flex` em coluna com o preço ancorado na base (`margin-top: auto`) e o grid usa `grid-auto-rows: 1fr`. Resultado: todos os cards com a mesma altura (medido: 257px), inclusive o sem frase.

## 2026-10-10 — Solicitações: botão "Ver detalhes" alinhado à direita

### Corrigido
- `Solicitacoes/CSS/solicitacoes.css` — `.solicitacao-acoes` ganhou `margin-left: auto`. Como `.solicitacao-observacao` tem `max-width: 320px`, o conteúdo do cartão não preenchia a largura toda e sobrava espaço vazio à direita em telas largas, deixando o botão "Ver detalhes" deslocado para o meio. Agora a coluna de ações encosta na borda direita do cartão (verificado: borda direita das ações = borda de conteúdo do cartão).

## 2026-10-10 — Rodapé: links funcionais (navegação + âncoras)

### Alterado
- `Includes/footer-home.php` — links do rodapé agora navegam de verdade:
  - **Home** → `home/organizadores.php`
  - **Dashboard** → `Dashboard/dashboard.php` quando logado como **promotor**; senão `Login/login.php`
  - **Sobre / Serviços / Funcionalidades / Assinaturas** → âncoras da landing (`#sobre`, `#servicos`, `#funcionalidades`, `#planos-lp`)
  - Logo passou a ser link para a Home; `$base_url` com default `'../'`; `session_start()` guardado
- `Landing Page/landingpage.php` — passou a **incluir** o footer compartilhado (`Includes/footer-home.php`) em vez de HTML duplicado; adicionadas âncoras `id="sobre"` (secao01), `id="servicos"` (secao02), `id="funcionalidades"` (secao03) e `id="planos-lp"` (planos-lp — corrige o `href="#planos-lp"` que não tinha destino); `session_start()` no topo.

### Verificação
- `php -l` limpo em `landingpage.php` e `footer-home.php`.
- HTML renderizado: sem sessão, Dashboard→`Login/login.php`; com sessão de promotor, Dashboard→`Dashboard/dashboard.php`; Home e âncoras corretos nas páginas home e na landing.

## 2026-10-10 — Fase 2: migração total das classes para pt-BR (CSS + HTML/PHP em sincronia)

### Alterado
- **Cadastro-geral** (9 arquivos): `conteudo-form`→`conteudo-formulario`; `linha-form`→`linha-formulario`; classe `.submit`→`.enviar`; `funcionalidade-icon`→`funcionalidade-icone`; `input-card`→`campo-cartao`.
- **Consistência entre componentes**: `Includes/footer-home.php`+`footer-home.css` (`footer*`→`rodape*`: `footer-container`→`rodape-caixa`, `footer-bottom`→`rodape-base`, `footer-col`→`rodape-coluna`, `footer-logo`→`rodape-logo`, `footer-links`→`rodape-links`, `footer-social`→`rodape-social`); `Includes/topbar.php`+`topbar.css` (`topo-sino-wrapper`→`topo-sino-envelope`, `topo-info`→`topo-informacao`); `Landing Page/` (`plano-header`→`plano-cabecalho`, inclui `-mensal`/`-anual`); `Cliente/solicitacao.php`+`solicitacao.css` (`error-list`→`lista-erros`).
- **home/** (`organizadores.php`, `buffet.php`, `espaco.php`, `css/org.css`, `css/buffet.css`, `css/style.css`): `hero-content`→`hero-conteudo`; `organizer-card`→`organizador-cartao` (+ `organizer-card-link`→`organizador-cartao-link`); `organizers`→`organizadores`; `buffet-card`→`buffet-cartao`; `space-card`→`espaco-cartao`; `rating-star`→`estrela-avaliacao`; `.spaces`→`.espacos`. Mantidos: `.search-*`, `.buffets`, `.hero`.
- **Sufixos EN→pt-BR** (28+ arquivos): `-card`→`-cartao`, `-btn`→`-botao`, `-page`→`-pagina`, `-info`→`-informacao`; `item--fallback`→`item--reserva`; `galeria-apagar-form`→`galeria-apagar-formulario`; `grade-form`→`grade-formulario`; `form-head`→`form-cabecalho`; `preview-wrap`→`preview-envelope`; `modal-input`→`modal-campo`; `pkg-img`→`pkg-imagem`; `pkg-name`→`pkg-nome`; `pkg-price`→`pkg-preco`; `info-cliente`→`informacao-cliente`; `info-servico`→`informacao-servico`; `servicos-cards`→`servicos-cartoes`.

### Decisões
- **Não renomeados**: variáveis CSS (`--card`, `--sombra-card`, `--borda-card`, `--bg-card`, `--bg-page`, `--cinza-card`, `--info-texto`) e o conjunto sancionado `search-*` (`.search-area/-bar/-field/-btn`) — protegidos no codemod.
- **Mantidos como empréstimos/PT**: `input`, `link`, `container`, `overlay`, `item`, `meta`, `menu`, `logo`, `nav`, `modal`, `social`, `area`, `grid`, `hero`.

### Verificação
- `php -l` limpo em todos os PHP alterados (13 arquivos).
- Verificações HTTP 200: `Landing Page/landingpage.php`, `Perfil/perfil.php`, `Servicos/servicos.php`, `Dashboard/dashboard.php`, `Solicitacoes/solicitacoes_detalhes.php?id=2`, `Assinatura/assinatura.php`, `home/organizadores.php` — novas classes presentes/rendem, antigas ausentes na DOM.
- Grep final: nenhum resíduo de token EN antigo (só falsos positivos por substring dos novos nomes).
- Backups: `backup-cadastro-*`, `backup-en2-*`, `backup-home-*`, `backup-suffix-*` (pasta temporária do opencode).

## 2026-10-10 — Topbar unificada total + CSS naming convention (pt-BR) + persistência localStorage + busca real

### Adicionado
- **`Includes/topbar.php`** — componente único parametrizado substituindo `topbar-home.php` e `Cliente/cabecalho.php`:
  - Parâmetros: `$base_url`, `$secao_ativa` (`organizadores`|`buffet`|`espaco`), `$show_notifications` (bool), `$show_meu_perfil` (bool)
  - 5 páginas migradas: `home/organizadores.php`, `home/buffet.php`, `home/espaco.php`, `Cliente/perfil.php`, `Cliente/solicitacao.php`
  - Notificações reais: promotor (solicitações recebidas) + cliente (suas solicitações) via `$_SESSION['usuario_email']`
  - IDs padronizados para `header.js` (`topo-botao`/`topo-menu`, `topo-sino`/`topo-notificacoes`) — dropdowns funcionando em todas
- **`Includes/css/topbar.css`** — renomeado de `topbar-home.css`, classes em **pt-BR com prefixo `topo-`**:
  - `.topo`, `.topo-logo`, `.topo-nav`, `.topo-link`, `.topo-conta`, `.topo-entrar`, `.topo-perfil`, `.topo-botao`, `.topo-avatar`, `.topo-info`, `.topo-seta`, `.topo-menu`, `.topo-opcao`, `.topo-sino-wrapper`, `.topo-sino`, `.topo-badge`, `.topo-notificacoes`, `.topo-notificacoes-cabecalho`, `.topo-notificacoes-lista`, `.topo-notificacao`, `.topo-notificacoes-vazio`, `.topo-notificacoes-todas`
  - Variáveis CSS no `:root` para cores, sombras, filtros
  - Avatar com outline roxo escuro + glow branco sutil
  - Dropdowns sem animação (simples), caret estático, badge vermelho estático
  - Responsivo 760px/420px
- **`assets/home/`** — pasta criada consolidando todo conteúdo de `home/assets2/` (~80 arquivos), referências atualizadas nos 3 PHP + 3 CSS; `home/assets2/` deletado
- **Persistência localStorage** em `Cliente/solicitacao.php`: auto-save (debounce 300ms), restauração ao carregar, limpeza após envio, expiração 7 dias, botão "Login" força save
- **CSS Naming Convention pt-BR** aplicada em **10 arquivos**:
  - `Includes/css/topbar.css` (topbar unificada)
  - `Cliente/css/perfil.css` (prefixo `card-`, `btn-`, `pkg-`, `modal-`, `gallery-`, `.avatar--empty`, `.foto--vazia`)
  - `Cliente/css/solicitacao.css` (`.form`, `.card-promotor`, `.box`, `.field`, `.btn-submit`, `.pkg-`, `.row`)
  - `home/css/org.css`, `home/css/buffet.css`, `home/css/style.css` (`.search-area`, `.search-bar`, `.search-field`, `.search-btn`, `.hero`, `.organizers/.buffets/.spaces`, `.organizer-card/.buffet-card/.space-card`, `.rating-star`)
  - `Perfil/CSS/perfil.css` (`.section-title`, `.btn-`, `.card-`, `.pkg-`, `.gallery-`, `.modal-`, `.avatar--empty`, `.empty`)
  - `Includes/css/layout.css` (sidebar `.ativo`, `.avatar-vazio`)

### Removido
- `Includes/topbar-home.php` (substituído por `topbar.php`)
- `Cliente/cabecalho.php` (HTML + JS inline + CSS duplicado — ~200 linhas)
- JS inline de `Cliente/cabecalho.php` (substituído por `header.js` compartilhado)
- CSS duplicado de topbar em `Cliente/css/perfil.css` (~185 linhas) e `Cliente/css/solicitacao.css` (~185 linhas)
- `home/assets2/` (pasta legada)
- Regra genérica `.barra-pesquisa input` que conflitava com `.campo-busca` (3 arquivos home)

### Corrigido
- **Dropdown home não abria** — animações CSS (`@keyframes menuFadeIn`) removidas; classes `.aberto` compatíveis com `header.js`
- **Ícones nav tamanhos diferentes** — unificado: `height: 22px; width: auto; max-width: 28px; object-fit: contain`
- **Avatar placeholder filter** — padronizado para `invert(62%) sepia(18%) saturate(540%) hue-rotate(210deg) brightness(92%)` em `perfil.css` + `solicitacao.css` (igual Dashboard)
- **Sino e caret invisíveis no home** — adicionado `filter: brightness(0) invert(1)` (SVGs escuros invisíveis no fundo roxo)
- **Busca quebra linha** — `type="search"` → `type="text"`, remoção de `.barra-pesquisa input` genérico, `!important` + `line-height: 1.2` + `word-break: keep-all` no `.search-field`
- **Busca "fantasma" (cards clicáveis vazios)** — removido `ob_start` + regex; filtrado arrays em PHP antes de renderizar (`array_filter` por nome/cidade/descrição)
- **Notificação no Cliente/perfil.php** — `$show_notifications = true` (era `false`)
- **Notificação em Cliente/solicitacao.php** — `$show_notifications = true` (era `false`); default no topbar.php = `true`
- **Espaçamento sino ↔ avatar** — `gap: 24px` → `32px`
- **Animações removidas** — caret rotate, gap hover, dropdown item slide-left, badge pulse, `@keyframes menuFadeIn`/`pulse`

### Verificação
- `php -l` limpo em 6 arquivos PHP (topbar.php + 5 páginas)
- Servidor embutido `php -S 127.0.0.1:8090`: 5 páginas = 200 OK
- 3 estados testados por página: não logado (só "Entrar"), logado cliente (avatar + dropdown sem sino), logado promotor (avatar + sino + dropdown + Meu Perfil)
- Busca "as" filtra corretamente nas 3 páginas home + Cliente (organizadores, buffets, espaços, solicitacao)

## 2026-10-07 — Topbar da home unificada (Includes/topbar-home.php): perfil igual ao dashboard, sem "Configurações", notificações reais

### Corrigido
- **O conjunto de mini-perfil da home mostrava o nome PRÓPRIO** (era `$_SESSION['usuario_nome']`)
  em vez do **nome fantasia**, e com fonte/espaçamento diferentes do painel do promotor. As 3
  páginas da home (`home/organizadores.php`, `home/buffet.php`, `home/espaco.php`) tinham topbars
  manuais repetidas, divergentes entre si.

### Adicionado
- **`Includes/topbar-home.php`** — o topo inteiro das 3 páginas da home num include único, com a
  MESMA lógica de identidade do painel (como em `Includes/header.php`):
  - promotor vê o **nome fantasia**; rótulo Promotor/Cliente/Visitante sempre deriva do banco
    (`obterPromotorExistente()` + sessão); foto via `resolver_foto_perfil()`;
  - **menu de perfil condicional**: promotor → *Meu Perfil* + *Sair*; cliente → *Sair*;
    visitante → só o botão de conta (sem *Sair*). **O item "Configurações" saiu de todas**
    (apontava para `Dashboard/configuracoes.php`, que nem existe);
  - **menu de notificações real** (`#notificationMenu`, igual ao do dashboard): lista as
    solicitações **pendentes do próprio promotor** e o `.notification-dot` (ponto vermelho)
    só aparece quando elas existem — nas 3 páginas havia um ponto hardcoded que abria nada.
  - Nav do topo com o item ativo por aba (`$aba_sessao` passado por cada página: `organizadores`/
    `buffet`/`espaco`), logo e links com `$base_url` (o include funciona a partir de `home/`).
- **`Includes/footer-home.php`** — footer da Landing Page extraído para include (as 3 páginas
  repetiam o mesmo bloco com caminhos `../assets/...`).
- **`home/css/header-dashboard.css` deixou de ser carregado pelas páginas da home** (a topbar
  agora emana do include; o CSS específico de cada página cuida do resto). O arquivo permanece
  para o painel.

### Alterado
- `home/organizadores.php`: busca de promotores reais agora filtra por `$busca` direto no loop
  (antes havia um `ob_start()` reescrevendo o HTML renderizado, removido); cards estáticos
  mantidos. `home/buffet.php` e `home/espaco.php`: cards movidos para array PHP (menos repetição)
  e busca mstrando/filtrando os cards antes de renderizar (o `ob_start()` antigo fazia o mesmo
  pós-render).

### Verificação
- `php -l` limpo nos 5 arquivos (topbar-home, footer-home, organizadores, buffet, espaco).
- Servidor embutido `php -S 127.0.0.1:8090` + MySQL: promotor `abc@gmail.com` abre organizadores
  com **nome fantasia "bbbbb" + rótulo Promotor**, menu com *Meu Perfil* + *Sair* (sem
  *Configurações*), ponto vermelho **presente** (há pendentes) e o menu de notificações listando
  "Casamento" e "Formatura"; cliente `migel@gmail.com` aparece como **mgel + Cliente**, menu só
  com *Sair*, **sem** ponto vermelho; visitante sem login mostra "Usuário/Visitante" e nenhum dot.
- Servidor encerrado após o teste; porta 8090 liberada.

## 2026-10-07 — Home padronizada: ícones da topbar, conjunto da conta, footer da landing e banner/busca

### Corrigido
- **Ícones do menu da topbar amassados** (`home/css/org.css`, `buffet.css`, `style.css`):
  `.navegacao-principal img` forçava 17×17, mas `org.png` é 37×22 e `buffet.png` é 25×22 —
  distorciam (o de Organizadores era o pior). Agora `height: 17px; width: auto`
  (renderizado: 29×17, 19×17 e 17×17, sem amassar).

### Padronizado
- **Conjunto da conta (sino + avatar + nome/tipo) da home = design do promotor**
  (`home/css/header-dashboard.css`, alinhado ao bloco de `Includes/css/layout.css`):
  - sino com `object-fit: contain` (o PNG 28×31 amassava levemente na caixa 27×29);
  - bolinha de notificação com **anel branco** — antes `var(--purple)`, variável que NÃO
    existe nas páginas da home, então a borda nem aplicava;
  - avatar sem foto usa o MESMO filtro do promotor (`invert(62%) sepia(18%)...`);
  - **seta do menu visível**: o `assets/seta_baixo.svg` é preto e desaparecia na barra roxa —
    `filter: brightness(0) invert(1)` a deixa branca (no promotor ela é preta no fundo
    branco: mesmo design, cor adaptada ao fundo).
- **Footer da home = footer da Landing Page**: criado `home/css/footer.css` com os estilos
  de `Landing Page/css/landingpage.css` (fundo `#140624`, logo 140px, colunas Análise/
  Acessibilidade, faixa de copyright com borda superior) e markup novo nos 3 PHP
  (organizadores/buffet/espaco). Ícones sociais: agora os 5 da landing
  (facebook/twitter/youtube/instagram/tiktok, `../assets/landing-*.png`) e **brancos** via
  `filter: brightness(0) invert(1)` (eram pretos). Regras antigas
  `.rodape`/`#logo-rodape`/`.redes-sociais` removidas dos 3 CSS.

### Alterado
- **Banner antes da busca** nas 3 páginas da home — ordem nova: topbar → banner → barra de
  busca → cards. Seções trocadas de lugar no HTML; `.destaque` com 320px (era 257px) e sem
  margem (encosta na topbar); `.area-pesquisa` com padding 38/20/72 (desktop) e 28/16/44
  (mobile, banner 280px).

### Verificação
- `php -l` limpo nos 3 PHP; DOM medido no navegador (1680×950 e viewport estreita) nas 3
  páginas: ordem destaque→pesquisa→cards, banner encostado na topbar, ícones 29×17/19×17/
  17×17, footer `#140624` com 5 ícones brancos, bolinha com anel branco, seta visível.
  Console e rede sem erros.

## 2026-09-28 — Topbar do cliente idêntica à home + pill amarelo de seção ativa

### Corrigido
- **Troca de banner e foto no modal "Editar perfil"**: bloco `.editar-fotos` de
  `Perfil/CSS/perfil.css` reescrito a partir da versão de referência do projeto
  (`C:\xampp\htdocs\NATHUS PROJETINHO`). O que mudou:
  - **Banner em sangria total** — `.editar-fotos { margin: 0 -24px }` cancela o padding do
    `.modal-sobre` (24px), então a faixa de 160px encosta nas duas bordas do modal. Antes o
    banner era um retângulo de 150×100 centralizado, minúsculo dentro do modal.
  - **Banner vazio é só cor plana** (`#c9c7d1`) — sem imagem e **sem ícone de câmera em
    repouso**; o ícone (`assets/editar_banner.svg`, 30×24) só aparece no hover.
  - **Foto de perfil sobrepõe a borda inferior do banner** e fica alinhada à esquerda
    (`align-self: flex-start` + `margin: -48px 0 0 24px`), 96px, circular, com borda de 3px em
    `var(--roxo)` e anel `0 0 0 4px var(--card)`. Antes ficava centralizada e 150×150.
  - **Hover** — `.editar-foto-botao` cobre a mídia inteira com `var(--overlay-foto)` e
    `opacity: 0 → 1` em `.15s`; a câmera sai centralizada (30×24 no banner, 20×16 no avatar).
    Segue a convenção de hover simples do projeto (sem levantar/escalar).
  - **Sem "Escolher arquivo"** — `#inputBannerModal, #inputFotoModal { display: none }`; os
    `<label for>` continuam abrindo o seletor e o preview atualiza na hora.
  - Foto template do promotor (`assets/user_dashboard.svg`, via `resolver_foto_perfil()`) entra
    a 55% com `object-fit: contain` e filtro de cor quando não há foto salva.
- **Warning `Undefined variable $eh_promotor`** no dropdown da conta (`Cliente/cabecalho.php`
  linha 52): a variável deixou de existir em refatoração anterior. O tipo agora usa o rótulo
  já resolvido pelo banco (`rotuloTipoUsuario()`), que é a fonte correta.
- `.cabecalho` do cliente: `min-height: 62px` → `height: 62px` — igual à `.barra-superior`
  da home. Com `min-height`, os filhos com `height: 70%` (itens do menu) não tinham
  referência de altura e o pill do item ativo ficava 1,4px mais baixo que o da home.

### Adicionado
- **Pill amarelo de seção ativa** no menu da topbar do cliente: `Organizadores` marcado com
  `border: 2px solid #fdbd24; border-radius: 24px` — exatamente como o `.ativo` da home
  (`Cliente/css/perfil.css` + `solicitacao.css`). No perfil público e na solicitação, o item
  "Organizadores" fica marcado (mesma seção); na home, cada página marca a sua.
- **Sino com bolinha vermelha** (`.notificacao-dot`, igual à `.notification-dot` da home) e
  **foto de perfil real no avatar do topo** via `resolver_foto_perfil()` — a mesma fonte da
  home/Dashboard; cliente e visitante caem no fallback SVG (`.foto-vazia`, 55% centralizado).
- Itens do menu com a mesma caixa da home (todos com `height: 70%` + `padding: 0 18px`),
  então o pill fica pixel-idêntico ao da home (medido: 153.31 × 43.39 nas duas).
- "Meu perfil" no dropdown da conta agora só aparece para promotores (clientes seriam
  rebatidos pela guarda `exigirPromotor()` — era um dead-link).

## 2026-09-28 — Isolamento de solicitações por promotor + identidade promotor/cliente

### Descoberta crítica
- **A tabela `solicitacoes` NÃO existia no banco em uso** — qualquer página do módulo
  (Solicitações, Serviços, Dashboard, notificações) quebrava com fatal error de MySQL.
  Foi recriada já com o schema novo. O `Banco/nathus.sql` também já traz a tabela correta
  para imports futuros.
- **A pasta `assets/` do workspace estava VAZIA** (148 imagens do app sumidas — ícones do
  cadastro, fotos de exemplo, logo, avatares; aconteceu antes desta sessão). Restauradas
  a partir da cópia `C:\xampp\htdocs\NATHUS PROJETINHO\assets\` (a versão de
  `faltando o home` é idêntica, exceto `cadastro-camera.svg`, mais antigo lá — mantive o
  novo, refeito no dia 27). Auditadas as 127 referências de imagem em `.php/.css/.js`:
  nenhuma faltando; servidas com 200 pelo servidor embutido.

### Adicionado
- **`solicitacoes.id_promotor`** (dono da solicitação) e `status VARCHAR(30) DEFAULT 'pendente'`
  no `Banco/nathus.sql`; seed de exemplo vinculado ao promotor 1. Comentário no arquivo traz a
  migração para bancos existentes.
- `Includes/conexao.php`: `garantir_colunas_solicitacoes()` — migração automática 1x por sessão
  (cria as colunas que faltarem e aponta solicitações antigas sem dono para o promotor 1).
- `Includes/promotor.php`: `obterPromotorExistente()` — versão **read-only** de
  `obterOuCriarPromotor()`, para páginas que NÃO devem criar registro de promotor (cliente).
- Sessão agora guarda **`usuario_tipo`** (`promotor`/`cliente`): setado no `Login/login.php`,
  `Cadastro-geral/cadastro-cliente/incluircliente.php` e `salvar-promotor.php`.

### Corrigido — mistura de dados entre promotores (feedback dos testes)
- **`Cliente/solicitacao.php`**: o INSERT agora grava o `id_promotor` do perfil visualizado —
  antes a solicitação ficava "sem dono" e aparecia para qualquer promotor.
- **`Solicitacoes/solicitacoes.php`**, **`solicitacoes_detalhes.php`**, **`Servicos/servicos.php`**,
  **`Dashboard/dashboard.php`** e as **notificações do `Includes/header.php`**: todas as consultas
  filtram pelo promotor do usuário logado. Cliente sem perfil de promotor vê as listas vazias.
  Detalhes/serviços só abrem para o dono (legado sem dono fica visível ao promotor 1).
- Cards de solicitação: removido o uso da coluna **inexistente** `foto_perfil` em
  `solicitacoes` (causava warning de PHP + imagem quebrada); avatar com inicial do cliente.
- `observacoes` NULL protegido contra warning na lista e no dashboard.
- Feedbacks: banner de "revertido para pendente" na lista; banner de "aceita com sucesso"
  em Meus Serviços (antes aceitar jogava o usuário numa página sem aviso nenhum).

### Rótulo do topo mostrava "Promotor" para cliente (sessão envenenada)
- O rótulo da conta (topbar) confiava em `$_SESSION["usuario_tipo"]`, que podia estar
  mentindo: sessões abertas ANTES das correções gravaram "promotor" (padrão antigo
  `?? 'promotor'`) ou quando o fantasma ainda existia — depois de apagar o fantasma,
  a sessão continuava exibindo "Promotor" para cliente puro (caso da abc3 às 3h05).
- `Includes/promotor.php`: nova `rotuloTipoUsuario($conexao)` — o rótulo SEMPRE deriva
  do banco e corrige a sessão em seguida (auto-cura nos dois sentidos). Sessões antigas
  se consertam sozinhas na primeira página carregada; nada a fazer manualmente.
- Aplicada em `Includes/header.php`, `Cliente/cabecalho.php` e nas 3 topbars da home
  (antes: `(?? 'promotor') === 'cliente'` em cada uma); sem login mostra "Visitante".
- Verificado com sessões envenenadas nos dois sentidos: tipo salvo "promotor" para
  cliente puro exibe "Cliente" (e corrige a sessão); "cliente" salvo para promotora
  real exibe "Promotor". Arquivo temporário `_preview_rotulo.php` removido após o teste.

### Topbar do modo cliente padronizada com a home (Explorar)
- A topbar de `Cliente/perfil.php` e `Cliente/solicitacao.php` era 72px, com logo diferente
  ("Logo Branca") e avatar 42px sem nome — totalmente diferente da barra da home (62px,
  logo com N colorido, avatar 46px com nome/tipo).
- `cabecalho.php` + `css/perfil.css` + `css/solicitacao.css`: mesma métrica da home
  (`home/css/org.css`) — altura 62px, fundo #2c1d57, logo `assets/LLOgo.png` (byte-idêntica
  ao logo.png da home, com N colorido) a 39px, nav 12px com ícones 17px, conta com avatar
  46px + nome/tipo 20px/15px e menu suspenso (Meu perfil / Sair).
- Bônus: a conta abc3@gmail.com também tinha perfil fantasma de promotor (id 22, vazio,
  mesmo bug antigo) — removido; ela loga como cliente direto no Explorar.
- Verificado no navegador: as duas topbars medem exatamente igual (62px, #2c1d57,
  logo 39px, avatar 46px) e o menu de conta funciona.

### CLIENTE não entra mais no painel de promotor (erro grave relatado)
- **Login** (`Login/login.php`): o destino agora depende do tipo da conta — promotor vai
  pro Dashboard, **CLIENTE vai direto pro "Explorar" (`home/organizadores.php`)**. Antes
  todo mundo caía no dashboard (e a landing leva ao login, então o cliente "do Explorar"
  terminava no painel de promotor).
- **Guarda nova `exigirPromotor()`** (`Includes/promotor.php`): usada em Dashboard,
  Perfil (dono), Solicitações + Detalhes, Serviços e Assinatura. Cliente logado que tentar
  qualquer uma dessas páginas é redirecionado ao "Explorar" — o painel de sidebar agora
  existe APENAS para promotores.
- **Efeito colateral do bug antigo eliminado**: `Assinatura/assinatura.php` e
  `Perfil/perfil.php` usavam `obterOuCriarPromotor()` (que CRIA registro de promotor) —
  era assim que contas puras de cliente ganhavam perfil fantasma e viravam "promotor".
  Agora a assinatura usa a guarda e não cria nada.
- **Dados**: a conta abc2@gmail.com tinha um perfil de promotor fantasma (id 20, 100%
  vazio) criado por esse bug — foi removido do banco; com isso ela loga como cliente
  e cai no Explorar. Verificado no navegador: login cliente → Explorar; 5 páginas do
  painel bloqueadas (voltam ao Explorar); login de promotor real → dashboard normal.

### Cabeçalho do modo cliente religado (estava preso no perfil do promotor)
- `Cliente/cabecalho.php` era todo "morto": logo apontava para `perfil.php` (a própria
  página!), itens do menu (Organizadores/Buffet/Espaço) com `href="#"` e o avatar era um
  `<span>` sem clique — sem sair da conta, sem voltar pra home.
- Agora: logo → `home/organizadores.php`; menu com os 3 links reais; avatar vira botão que
  abre menu da conta (nome + tipo + "Meu perfil" + "Sair"); visitante vê botão "Entrar".
- Avatar corrigido (o "zoom" lavado do print): o SVG de 29×32 era esticado a 42×42 com
  `contain`; agora fica proporcional (55%) e tingido de roxo sobre círculo claro — mesmo
  padrão do `.profile-avatar.foto-vazia` do dashboard. Estilos duplicados em
  `css/perfil.css` e `css/solicitacao.css` (as duas páginas que usam o cabeçalho).
- Verificado no navegador logado: menu abre/fecha, "Sair" encerra a sessão (voltou ao
  login), logo e 3 itens do menu carregam (200), nome "mgel" + tipo "Cliente" no menu.

### Avaliação do promotor (visão cliente) + ícones novos do dashboard
- `Dashboard/dashboard.php`: "Eventos Marcados" e "Avaliações médias" agora usam os novos
  `marcados_dashboard.svg` e `avaliacao_dashboard.svg` (adicionados pelo grupo).
- `Cliente/perfil.php` + `css/perfil.css`: botão "Avaliar Promotor" no design do mockup
  (branco, borda e estrela douradas, ícone `avaliar_perfil.svg`); estrelas do modal de
  avaliação trocadas pelos SVGs `av1_perfil.svg` (cheia, dourada) / `av2_perfil.svg` (vazia),
  controlados pela classe `.ativa` existente do `perfil.php`.
- Modal de avaliação: botões "Enviar" (roxo sólido) e "Cancelar" (contorno vermelho) no
  design do mockup; foto do promotor em círculo — sem foto, mostra o avatar padrão
  (`.foto-modal.foto-vazia` com `content: url(...)`).
- Verificado no navegador logado como cliente: botão 183×41 com ícone carregando; modal com
  5 estrelas SVG trocando cheia/vazia ao clicar; 4 ícones do dashboard carregando.

### Corrigido — desalinhamento vertical nos campos do card de serviço
- No card de "Meus Serviços", o campo largo (local / horário) terminava em x diferente em
  cada linha, porque a coluna da direita era `fit-content` (a data "20/01/2027" é mais larga
  que o nº de convidados) — local e horário (e convidados × data) não batiam verticalmente.
- `Servicos/CSS/servicos.css`: `.linha-evento p:last-child` com largura fixa de 124px
  (comporta a data mais larga) e conteúdo centralizado; as duas linhas agora fecham
  idênticas. Verificado no navegador: bordas direitas alinhadas ao pixel (1251 / 1387).

### Corrigido — ícones quebrados nos cards do Dashboard
- Os cards "Eventos Marcados" e "Avaliações médias" apontavam para `eventos_marcado_dashboard.png`
  (o arquivo é `.svg`) e `ava.png` (nunca existiu) — mostravam o ícone de imagem quebrada.
- `Dashboard/dashboard.php`: "Eventos Marcados" usa `eventos_marcado_dashboard.svg` (que já
  existia em assets) e "Avaliações médias" usa `imagem-avaliacoes.png` (56×56, também órfã
  no código até agora). Verificado no navegador: os 4 ícones dos cards carregam.

### Corrigido — ícone do serviço esticado/borrado (Meus Serviços e galeria)
- As imagens de tipo de evento (`formatura.png`, `casamento.png`, `15_anos.png`) são ÍCONES
  de ~20px; com `object-fit: cover` numa caixa de 76×76 ficavam esticados, cortados e borrados.
- `Servicos/CSS/servicos.css`: `.foto-servico` virou "ladrilho" arredondado (fundo #eceaf5) com
  o ícone em `object-fit: contain` + padding 12px, centralizado.
- `Cliente/css/perfil.css`: mesmo tratamento para os itens de FALLBACK da galeria do perfil
  público (`.item-galeria.item-fallback` com contain + padding 28px). Fotos reais enviadas pelo
  promotor continuam com `cover`, que é o correto para galeria.
- Verificado no navegador: ícone 22×18 renderizado inteiro e nítido na caixa de 76×76;
  6 itens de fallback com contain na galeria.

### Corrigido — layout da página de detalhes (card "bugado" com barra no meio)
- `Solicitacoes/CSS/detalhes.css`: `.main-wrapper` ganhou `flex: 1; min-width: 0` — no
  `body { display: flex }` dessa página ele encolhia à largura do conteúdo (~815px), deixando
  fundo vazio à direita e a barra de rolagem "flutuando" no meio da tela. Agora ocupa toda a
  área à direita da sidebar, como o `.main-content` das outras páginas.
- A scrollbar visível dentro do card também saiu: `.content-area` com `scrollbar-width: none`
  + `::-webkit-scrollbar { display: none }`, mesmo padrão do `Includes/css/layout.css`
  (o app não exibe scrollbars; a rolagem com o scroll do mouse continua funcionando).
- Verificado no navegador (viewport 1680×950): wrapper 1380px, card 1283px, barra de ações
  centralizada, sem overflow horizontal e sem scrollbar visível.

### Corrigido — identidade na interface ("nome do promotor no cliente")
- `Includes/header.php`: rótulo do topo agora é **dinâmico** (Promotor/Cliente) — era fixo
  "Promotor" para todo mundo; nome exibido passa a ser o **nome fantasia** quando existir.
- `Perfil/perfil.php`: título do perfil usa nome fantasia; botão **"Visualizar perfil"**
  (que não fazia nada) agora abre o perfil público `Cliente/perfil.php?id={id_promotor}`
  (handler em `Perfil/js/perfil.js`).
- `home/organizadores.php`: cards dos **promotores reais** do banco (foto, cidade, preço, nota)
  linkando ao perfil público — antes a home era 100% estática e o perfil público era inalcançável;
  rótulo dinâmico também em `buffet.php` e `espaco.php`.
- `Cliente/dados.php`: `?id=` inexistente mostra página amigável "Promotor não encontrado"
  (HTTP 404) em vez de exibir silenciosamente o promotor de exemplo "Maria Lopes Rosa".

### Verificação
- Testado com `php -S 127.0.0.1:8090` + MySQL local, navegando os dois papéis:
  cadastro de promotor (e1→salvar), solicitação pelo cliente, lista/detalhes/aceitar/reverter,
  Meus Serviços, notificações, headers e 404 do perfil público — isolamento confirmado
  (dono vê, outro promotor não vê, cliente não vê nada). Dados de teste removidos do banco
  ao final. Lint `php -l` limpo nos 16 arquivos alterados.
- **Para o grupo:** no banco de vocês basta logar uma vez qualquer página (a migração roda
  sozinha) ou executar no phpMyAdmin:
  `ALTER TABLE solicitacoes ADD COLUMN id_promotor INT NULL AFTER id;`
  `UPDATE solicitacoes SET id_promotor = 1 WHERE id_promotor IS NULL;`

## 2026-09-27 — Correções e padronização do cadastro

### Corrigido
- **Split 80/20 no final do cadastro promotor:** `Includes/css/cadastro.css` agora usa
  `grid-template-columns: minmax(0, 1fr) minmax(0, 1fr)` + `min-width: 0` em `#esquerda`/`#direita`.
  Removidos do `e7/css/e7.css` os overrides `width: 50%`, `flex`, `overflow: hidden`
  (html/body/seções) que quebravam a divisão. Verificado no DOM: colunas 711.5px | 711.5px.
- **Cards grandes/deitados:** e3 (planos) e e4 (métodos) com colunas fluidas
  `repeat(auto-fit, minmax(min(280px, 100%), 310px))`; `.plano-card`/`.metodo-card` com
  `width: 100%; height: auto; min-height: 440px` (antes: card de 320px dentro de coluna de 310px).
  Escolha (`Cadastro-geral/css/escolha.css`) com `flex-wrap: wrap` + `max-width: 100%` nos cartões;
  media query `(max-height: 760px)` solta o `min-height` dos cartões em telas baixas.
- Botões globais do shell com `max-width: 100%`; padding lateral 40px; `.voltar` alinhado.
- Títulos com `clamp()` (`.titulo h1` no shell, e1, e7) para não estourar em telas médias.

### Padronização
- **e1 do promotor = medidas do cadastro de cliente** (`e1/css/e1.css`): container 520px,
  inputs 48px/raio 4px, labels 15px, gap 18px, botão 260×48, título 30px, barra 1px.
- **Fundo dos inputs do e1** = `#E2E4E9` (mesmo `--fundo-form` do cliente); antes roxo translúcido.

### e7 (foto de perfil + descrição)
- **Ícone novo:** `assets/cadastro-camera.svg` refeito (câmera preenchida, lente com aro `#CBC5F0`).
  Compatível com `e7.js` (troca de `src` no preview da foto).
- **Hover simplificado (revertido a pedido do usuário):** apenas overlay escuro
  `rgba(15, 10, 35, .35)` com fade 0.15s — igual ao "editar perfil" (`Perfil/CSS/perfil.css`).
  A versão anterior (anel tracejado, selo "Adicionar foto", levantar/escalar) foi desfeita.

### Notas de verificação
- Testado com `php -S 127.0.0.1:8090` + preview navegando e1, e7, e8 (e6/e7 exigem sessão;
  usado arquivo temporário `_preview_*.php`, removido depois).
