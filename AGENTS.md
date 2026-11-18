# AGENTS.md — Nathus (plataforma de eventos)

> Guia para agentes de IA (e humanos) neste projeto. **Leia antes de alterar qualquer coisa.**
> Mudanças recentes: veja [CHANGELOG.md](CHANGELOG.md).

## O que é

App **PHP puro** (sem framework, sem Composer) de plataforma de eventos: clientes contratam
**promotores** (buffet, salões de festa, equipes). Roda no **XAMPP** (Apache + MySQL).
Front-end HTML/CSS/JS vanilla, fontes locais (Poppins, Nunito; a Magica foi removida).
Conteúdo em **pt-BR**.

## Como rodar

- **Código do app:** `C:\xampp\htdocs\Nathus\Projeto\`.
  As pastas antigas `Nathus - Perfil Promotor`, `Nathus - Perfis Demo` e `NathusDoBem` **não
  existem mais**; estes `.md` ficam na raiz `C:\xampp\htdocs\Nathus\`, um nível acima do app.
- Com Apache: `http://localhost/Nathus/Projeto/` — o `index.php`
  da pasta do app redireciona para `home/homeAnt.php`.
  A raiz `Nathus` serve apenas para organização e não tem `index.php` do app.
- Ou servidor embutido do PHP apontando para a pasta do app:
  ```powershell
  & C:\xampp\php\php.exe -S localhost:8090 -t "C:\xampp\htdocs"
  ```
  (docroot na pasta pai mantém as URLs `/Nathus/Projeto/...`).
- **Atalho sem Apache:** `setup.cmd` (dentro de `Projeto`, a raiz do projeto) abre um **menu
  numerado** no cmd: `1` verificar programas, `2` subir o MySQL, `3` criar o banco,
  `4` importar perfis demo, `5` abrir o site, `6` parar o site, `7` diagnóstico completo,
  `8` resetar o banco, `0` sair. O menu mostra o estado (MySQL/site) no topo e volta para a
  lista depois de cada ação. A leitura da opção usa `choice /c 123456780`: só as teclas 0-8
  valem (Enter, espaço e tecla errada são ignorados sem redesenhar a tela — era isso que
  fazia o menu "piscar" com o `set /p`). `setup.cmd reset` faz o reset direto, sem menu.
  O site sobe no servidor embutido do PHP na primeira porta livre a partir de 8000, com a
  pasta `Nathus` como docroot — as URLs ficam `http://localhost:<porta>/Nathus/Projeto/...`.
  `parar.cmd` e `verificar.cmd` ficam na mesma pasta e funcionam fora do menu; o reset salva
  backup em `Projeto/backups/`. Logs em `Projeto/setup.log` e `Projeto/php-erros.log`.
- Área do espaço (m²) é **obrigatória** em contas de local de festas (campo, JS e
  `salvar-editar-perfil.php`); criar pacote exige endereço e área.
- Banco: importar `Banco/nathus.sql` no MySQL do XAMPP; conexão em `Includes/conexao.php`
  (`localhost`, `root`, sem senha, db `nathus`).
- Perfis demo da vitrine: importar `Banco/perfis-demo.sql` (idempotente). Cria 30 promotores
  (`buffet1..10`, `local1..10`, `organizador1..10`) com login `<usuario>@nathus.com` e senha `123`,
  pacotes, galeria e imagens já em `uploads/`.
  O mesmo seed cria também `cliente1@nathus.com` e `cliente2@nathus.com`, senha `123`,
  com tipo cliente e sem perfil de promotor. São 32 contas demo no mesmo SQL, sem clientes extras.
  Ao importar, o seed apaga e recria SOMENTE essas 32 contas demo e seus pacotes, galerias,
  especialidades e solicitações associadas. Edições dessas contas são descartadas; contas fora
  da lista são preservadas. Não há arquivo separado de contas adicionais. Os IDs mudam:
  depois de importar, sair e entrar novamente nas contas demo para renovar a sessão.
  Sessão com id que não existe mais é descartada em `Includes/conexao.php` (evita o topo
  mostrar o nome antigo com o tipo "Cliente"); `setup.cmd reset` também apaga as sessões do PHP.

## Mapa do projeto

```
Cadastro-geral/           Cadastro
  geral.php               Escolha cliente x promotor (css/escolha.css)
  cadastro-promotor/      Fluxo promotor — shell topo.php/fim.php; etapas e1..e8; e5-pix/, e5-cartao/
  cadastro-cliente/       Fluxo cliente — HTML/CSS PRÓPRIOS (etapa1.php, css/etapa1.css)
Includes/                 PHP compartilhado; css/global.css (reset + variáveis); css/cadastro.css (shell)
                          header.php + sidebar.php (shell do painel do promotor: Dashboard/, Perfil/,
                          Servicos/, Solicitacoes/, Assinatura/); js/header.js (dropdowns do painel)
                          conexao.php; promotor.php; perfil_foto.php; imagens.php (upload/otimização);
                          migracoes.php (compatibilidade de schema, carregada por conexao.php)
                          cidades-rj.php; footer-home.php
                          topbar.php + css/topbar.css = LEGADO MORTO (nenhum arquivo inclui)
Login/, Perfil/ (CSS/perfil.css), Dashboard/, Servicos/, Solicitacoes/, Assinatura/
Cliente/                  Perfil PÚBLICO do promotor (perfil.php, solicitacao.php, dados.php)
                          usa a casca da vitrine: home/topo-vitrine.php + home/css/vitrine.css
                          + css/perfil.css | css/solicitacao.css
home/                     Vitrine (fluxo principal): home.php = catálogo com ?campo=organizadores|buffet|espaco
                          e ?busca=...; topo-vitrine.php = header das páginas públicas;
                          homeAnt.php = landing antiga (destino do index.php); conta-modal.php +
                          salvar-conta.php = modal "Minha conta"; minhas-solicitacoes.php = pedidos do cliente;
                          editar-conta.php = redirect para homeAnt.php?editar=1
                          organizadores.php / buffet.php / espaco.php = stubs de redirect (102–109 B)
                          para home.php?campo=...
                          css/vitrine.css = ATIVO (prefixo vt-); css/{home,catalogo,conta,org,style,buffet}.css
                          = órfãos (sem referência; não usar como base)
                          Fontes/ (Poppins, Magica)
assets/                   SVGs e imagens (subpasta home/ para assets da home)
Banco/nathus.sql          Schema (cliente, promotor, promotor_campo, promotor_pacote, solicitacoes, ...)
Banco/perfis-demo.sql     Seed de 30 promotores (10 buffet, 10 local, 10 organizador) e 2 clientes;
                          apaga/recria apenas essas 32 contas e registros associados; senha 123
Banco/perfis-demo-imagens.json Fontes, licença e hashes das 120 fotos novas dos perfis 6..10
Tests/migracoes.php       Teste CLI das migrações, sem conexão ou alteração do banco real
Tests/perfil-campos.php   Integração local com buffet1 demo; testa campos/especialidades em transação
                          revertida ao final (todos|unico|nenhum), sem uploads
Tests/pacote-foto-perfil.php Criação de pacote e limpeza após redirecionamento; casos canonical,
                          legado e ausente, incluindo resolução do avatar; usa transação revertida
design-ontology.json      Ontologia de design (schema design-ontology@0.1.0) usada pelo MCP
                          design-ontology; liga intenção do cliente a propriedades visuais
.agents/skills/           Skills do agente (Matt Pocock) — carregadas pelo FreeBuff; 38 skills,
                          cópia de ~/.agents/skills (atualize com npx skills update + recopiar)
uploads/                  Arquivos enviados (banner, buffet, galeria, org, pacotes, perfil-cliente,
                          perfil-promotor)
_antigo/                  Versões antigas — NÃO usar como referência
```

> Os `.md` de memória ficam **na raiz** `C:\xampp\htdocs\Nathus\`; o código fica na subpasta do app.
> Não é repositório git (sem `git status`/commits).

## Componentes Compartilhados

| Arquivo | Responsabilidade |
|---------|------------------|
| `home/topo-vitrine.php` | Header das páginas públicas — usado por `home/home.php`, `home/homeAnt.php`, `home/minhas-solicitacoes.php`, `Cliente/perfil.php`, `Cliente/solicitacao.php`. Substituiu a `topbar.php` (código morto) |
| `Includes/footer-home.php` | Footer compartilhado — hoje só `Landing Page/landingpage.php` inclui; links: Home, Dashboard (promotor→`Dashboard/`, senão `Login/`) e âncoras da landing |
| `home/css/vitrine.css` | Estilos da vitrine/páginas públicas (prefixo `vt-`, variáveis no `:root`) |
| `Includes/css/footer-home.css` | Estilos do footer |
| `Includes/header.php` + `Includes/sidebar.php` | Shell do painel do promotor (Dashboard, Perfil, Serviços, Solicitações, Assinatura); `js/header.js` cuida dos dropdowns |
| `Includes/card-solicitacao.php` + `Includes/css/card-solicitacao.css` | Cards de Solicitações, do resumo de pendências no Dashboard e de Meus Serviços |
| `Includes/buscar-detalhe-solicitacao.php` + `Includes/detalhe-solicitacao.php` + `Includes/css/detalhe-solicitacao.css` | Consulta e layout comuns ao detalhe da solicitação e ao detalhe do serviço |
| `Includes/css/icones-solicitacao.css` | Ícones de data, horário, local, convidados e mensagem. O ícone de convidados também é usado no histórico do cliente, no filtro da vitrine, no perfil e no relatório |
| `Includes/agenda-solicitacoes.php` | Dias reservados por serviços aceitos, checagem de sobreposição e trava por promotor durante envio e aceite |
| `Includes/conexao.php` | Abre conexão MySQL e carrega/invoca as migrações de compatibilidade |
| `Includes/migracoes.php` | Funções `garantir_*()` e `normalizar_campos_promotor()`, sem abrir conexão ao carregar. A migração de texto inspeciona o schema antes de alterar colunas |
| `Includes/promotor.php` | `obterPromotorExistente()`, `obterTipoConta()`, `definirTipoConta()`, `ehPromotor()`, `resolverPromotorId()`, `rotuloTipoUsuario()`, `foto_card_promotor()` |
| `Includes/perfil_foto.php` | `resolver_foto_perfil()` — foto real ou template SVG com filter |
| `Includes/imagens.php` | `otimizar_e_salvar_imagem()`, `apagar_upload_antigo()`, `limpar_uploads_orfaos()` |
| `Includes/enquadramento.php` + `css/enquadramento.css` + `js/enquadramento.js` | Editor compartilhado de recorte com arraste, zoom e posição; usado nos cadastros, conta do cliente e mídias do perfil do promotor |
| `Includes/cidades-rj.php` | `cidades_do_rio()` — alimenta o filtro de cidade da vitrine |
| `Includes/topbar.php` + `Includes/css/topbar.css` | **Legado morto** — mantido no disco, sem nenhum include. Não usar |

### Uso da `home/topo-vitrine.php` (definir ANTES do include)
```php
$base_url = '../';
$vt_inicio = '../home/homeAnt.php';   // link do logo/Início (default: 'homeAnt.php')
$vt_asset  = '../';                   // prefixo dos assets (default: '../')
$vt_abas   = true;                    // mostra a nav de abas (organizadores/buffet/espaco)
$vt_acao   = '../home/salvar-conta.php'; // action do modal de conta
$vt_voltar = '../Cliente/perfil.php?id=' . (int) $id_promotor;
$secao_ativa = 'organizadores';       // 'organizadores' | 'buffet' | 'espaco'
include __DIR__ . '/topo-vitrine.php';
```

## Camadas de CSS (cadastro)

1. `Includes/css/global.css` — reset + variáveis (--botao, --cor-principal etc.) e componente
   de conta compartilhado (`.conta-perfil`), com o visual padrão do dashboard.
2. `Includes/css/cadastro.css` — shell split-screen (`main.divisao` → `#esquerda` | `#direita`)
3. `Includes/css/cadastro-campos.css` — largura, contraste e raio dos campos usados no cadastro
   do cliente e do promotor.
4. CSS da etapa (ex.: `e3/css/e3.css`) — só o conteúdo da etapa

O fluxo do **cliente** (`cadastro-cliente/`) tem HTML e CSS próprios e **não** usa o shell compartilhado.

## CSS Naming Convention (pt-BR + prefixo simples)

| Tipo | Prefixo | Exemplos |
|------|---------|----------|
| **Vitrine (público)** | `vt-` | `.vt`, `.vt-barra`, `.vt-menu`, `.vt-abas`, `.vt-lista`, `.vt-btn` |
| **Cards da vitrine** | `pro-` | `.pro-card`, `.pro-capa`, `.pro-avatar`, `.pro-frase`, `.pro-local`, `.pro-nota` |
| **Componentes** | `card-`, `btn-`, `modal-`, `pkg-`, `pacote-`, `galeria-`, `hero-` | `.card-principal`, `.btn-salvar-pacote`, `.modal-pacote`, `.pkg-imagem`, `.pacote-cartao`, `.galeria-item`, `.hero-conteudo` |
| **Estados** | `--vazio`, `--aberto`, `.aberto`, `.ativo`, `.salvando` | `.topo-avatar--vazio`, `.estrela--vazia`, `.aberto`, `.ativo`, `.salvando` |
| **Estrutura** | `topo-`, `lateral-`, `search-`, `hero-`, `secao-` | `.topo`, `.topo-nav`, `.barra-lateral`, `.search-bar`, `.hero`, `.secao01` |
| **Formulários** | `form-`, `field-`, `btn-`, `rotulo-` | `.form`, `.field`, `.btn-submit`, `.rotulo` |
| **Utilitários** | `.vazio`, `.ativo`, `.aberto`, `.invalido` | `.vazio`, `.ativo`, `.aberto`, `.campo-invalido` |

Arquivos atualizados:
- `home/css/vitrine.css` — **fonte ativa** das páginas públicas (`vt-`, `vt-menu*`, `vt-barra*`, `vt-abas`, `vt-lista`, `vt-profissionais`, `pro-card`, `pro-capa`, `pro-avatar`, `pro-*`); inclui o modal de conta
- `Cliente/css/perfil.css` (`.card-`, `.btn-`, `.modal-`, `.avatar-vazio`, `.foto--vazia`, `.tag-vazia`)
- `Cliente/css/solicitacao.css` (`.form`, `.card-promotor`, `.box`, `.field`, `.btn-submit`, `.pkg-`, `.row`, `.lista-erros`)
- `Perfil/CSS/perfil.css` (`.titulo-secao`, `.btn-`, `.card-`, `.pacote-`, `.galeria-`, `.modal-`, `.foto-vazia`, `.vazio`)
- `Includes/css/layout.css` + `Includes/footer-home.css` (pt-BR: `.barra-lateral`, `.rodape-*`)
- Demais páginas migradas na fase 2 (ver CHANGELOG 2026-10-10): `Landing Page/`, `Cadastro-geral/`, `Assinatura/`, `Dashboard/`, `Servicos/`, `Solicitacoes/`
- **Órfãos (não usar):** `Includes/css/topbar.css`, `home/css/home.css`, `home/css/catalogo.css`, `home/css/conta.css`, `home/css/org.css`, `home/css/style.css`, `home/css/buffet.css` — nenhum arquivo PHP os referencia

## Convenções e decisões (respeitar!)

- **Endereço do espaço em Projeto:** cada promotor do campo `local` tem um endereço em
  `promotor.espaco_*`, com cidade e UF do perfil. O endereço completo é requisito para
  salvar o perfil como Local, criar pacotes e receber novas solicitações. Complemento e
  área são opcionais. A solicitação grava uma cópia do endereço
  em `local_festa`, preservando o local de pedidos antigos após edições do perfil.
  A área em m² é opcional e fica em `promotor.espaco_area_m2`. O card Sobre mim
  tem um modal próprio para a frase de destaque, descrição e especialidades.

- **Cadastro:** a foto do cliente é opcional. Se o usuário selecionar uma imagem, o
  cadastro só avança depois que o arquivo for gravado. A descrição do promotor tem
  limite de 1.000 caracteres na etapa final e no servidor, como em Editar perfil.

- **Amarelo da marca:** dar mais presença ao amarelo da paleta nas telas do Nathus.
  Usá-lo em ações principais e destaques da interface, além dos ícones.
  O botão Salvar do modal Editar perfil do promotor usa texto e ícone brancos sobre o amarelo.
  Quando o usuário fala "amarelo", é o **amarelo claro** `#F5B600` (`--ft02-amarelo`),
  nunca o tom amarromado `#946B00`. O amarelo de ação (`--ft02-dourado-acao`) usa
  `#F5B600` e aparece em "Mudar para este plano" e "Ver detalhes".

- **Navegação do painel em Projeto:** `Includes/sidebar.php` usa a referência
  do Figma com ícones de contorno e destaque lilás na página ativa. O selo de
  Solicitações conta apenas as pendências do promotor logado. A navegação usa
  barra inferior no celular.

- **Paleta FT-02 em Projeto:** `Includes/css/global.css` guarda os valores
  compartilhados pelo painel e pelo perfil público. Os ícones de especialidade
  usam a cor do texto da etiqueta nas duas visões. Os botões do topo do perfil usam
  fundo transparente. Editar perfil usa amarelo `#F5B600` no contorno,
  no texto e no ícone.

- **Migrações:** as funções vivem em `Includes/migracoes.php`; páginas continuam incluindo
  `Includes/conexao.php`. Ao alterar migrações, executar
  `& C:\xampp\php\php.exe "Nathus - Perfil Promotor\Tests\migracoes.php"` da raiz.
  Schemas já compatíveis não devem receber `ALTER TABLE MODIFY`; preservar colunas maiores
  e marcar a migração de texto como concluída somente após sucesso.

- **Rodapé (`Includes/footer-home.php`):** "Sobre", "Serviços", "Funcionalidades"
  e "Assinaturas" apontam para as âncoras da landing page
  (`Landing%20Page/landingpage.php#sobre|#servicos|#funcionalidades|#planos-lp`),
  nunca para âncoras da home da vitrine. "Dashboard" só é renderizado para conta
  promotor; cliente e visitante não veem o item. Copyright em 2026.

- **Altura das topbars:** `.topo-painel` em `Includes/css/layout.css` usa 74px,
  igual à `.vt-topo` da home no desktop; o ajuste vale para todas as páginas do painel do promotor.
  Ambas têm padding lateral de 28px no desktop e ocultam a barra de rolagem sem bloquear
  a rolagem. Sino de 27px e gap de 26px até o perfil mantêm o conjunto alinhado entre páginas.

- **Meu perfil do promotor:** `Perfil/CSS/perfil.css` usa Poppins na página, controles e
  overlays. O acabamento segue o modal Editar perfil: seções com raio 20px, superfícies claras,
  botões arredondados e ações de edição amarelas. Preservar os grids fluidos dos pacotes e
  a galeria quadrada; os ajustes de topbar móvel desse CSS valem apenas para esta página.
  Manter a densidade compacta solicitada: banner 150px, avatar 96px, nome 24px, títulos 18px,
  texto 14px e imagens de pacote em 220px, tanto aqui quanto no perfil público do cliente;
  o botão Adicionar pacote não estica à altura dos cards.
   Pacotes com muitos itens rolam por dentro (`Includes/itens-pacote.php`, lista com
   `max-height: 11rem` + `overflow-y: auto`); o antigo "Ver todos os itens" (`details`)
   esticava os cards e não deve voltar. Pacote sem foto usa a moldura tracejada
   "Sem imagem" tanto no painel do promotor quanto no perfil público do cliente.

1. **Split 50/50:** `.divisao` usa `grid-template-columns: minmax(0, 1fr) minmax(0, 1fr)` e as
   seções têm `min-width: 0`. **Nunca** forçar `width: 50%`, `overflow: hidden` ou flex nas seções —
   isso causou o bug da tela 80/20 e foi removido.
2. **Cards fluidos:** grids de cards (planos e3, métodos e4, escolha) usam
   `repeat(auto-fit, minmax(min(280px, 100%), 310px))` / `flex-wrap: wrap` — cards ficam lado a
   lado só quando cabem. Sem largura fixa maior que a coluna.
   Cards de listagem (`.pro-card` em `home/home.php`) devem ter **altura uniforme**: o item do
   grid estica, então o card é `flex` em coluna com o preço/nota ancorado na base
   (`margin-top: auto`) e o grid usa `grid-auto-rows: 1fr`. Bug corrigido na versão antiga
   (`organizador-cartao`): card sem "frase destaque" ficava mais baixo que os vizinhos.
3. **Padrão de formulário em Projeto:** os dois fluxos usam Poppins em títulos,
   rótulos e controles. Os formulários de dados pessoais e de cartão têm largura
   fluida de até 680px, campos de 48px com fundo `#E2E4E9`, raio de 8px e rótulos grafite de 15px.
   A descrição do perfil usa o mesmo raio e estilo de rótulo.
   O foco dos campos usa dourado `#F5B600`; o menu de débito/crédito usa
   fundo amarelo claro e texto grafite na opção ativa.
   O login (`Login/`) segue o mesmo padrão de campo e usa "Continuar" com
   apenas a inicial maiúscula.
   O bloco "Seu evento dos sonhos" é um só (`Includes/painel-texto.php` +
   `Includes/css/painel-texto.css`), usado pelo login, pela escolha do tipo de
   usuário e pelas etapas dos dois cadastros. O botão sob o texto existe apenas
   nos pontos de entrada (login → cadastro, tipo de usuário → login); dentro do
   fluxo o painel mostra só o texto. Não recriar `.e-texto`/`.d-texto` locais.
4. **Hovers simples:** estilo do "editar perfil" (`Perfil/CSS/perfil.css`): overlay escuro
   `rgba(15, 10, 35, .55)` no avatar com fade 0.15s. SEM levantar/escalar/anel/selo — uma versão elaborada
   foi feita e **revertida por decisão do usuário**.
5. **Cache-busting:** assets carregam com `?v=<?php echo filemtime(...); ?>` — manter o padrão.
6. **Sessão e identidade:** cadastro guarda em `$_SESSION["cadastro"]`; etapas e6/e7 exigem
   `$_SESSION["usuario_id"]`. A fonte da verdade cliente×promotor é **`cliente.tipo_conta`**
   (+ existir linha em `promotor`), lida por `obterTipoConta()`/`ehPromotor()`/`resolverPromotorId()`;
   **nunca** inferir pelo que está na sessão (bug antigo: promotor "virava cliente do nada").
   `$_SESSION["usuario_tipo"]` é derivado em `Includes/header.php`/vitrine apenas para o rótulo do topo.
   Transições entre etapas salvam o POST na sessão e redirecionam com HTTP 303
   antes de renderizar, para manter Voltar/Avançar do navegador funcionais.
   Os botões Voltar dos cadastros, dos detalhes de solicitações e serviços, do
   perfil público do cliente, da solicitação e da listagem da vitrine
   (`home/home.php`, os três campos) usam `Includes/css/botao-voltar.css`;
   o botão tem `width: fit-content` para não esticar em colunas flex.
   Não criar variações locais como `.voltar` ou `.vt-voltar`.
7. **Solicitações têm dono:** toda solicitação grava `id_promotor`; consultas de
   Solicitações/Serviços/Dashboard/notificações filtram pelo promotor logado
   (`obterPromotorExistente()`). `Includes/conexao.php` migra o banco automaticamente
   (`garantir_colunas_solicitacoes()`), então bancos antigos se ajustam ao primeiro acesso.
   Pedidos legados sem destinatário verificado ficam com `id_promotor` nulo até revisão.
   O schema inclui chaves para cliente e promotor e índices para agenda e histórico.
   Para um banco existente, aplicar uma vez `Projeto/Banco/integridade_solicitacoes.sql`
   após conferir as referências.
   Só conta de cliente envia pedido: `Cliente/solicitacao.php` bloqueia conta promotor e manda
   visitante para `Login/login.php?voltar=...`.
8. **Preview de mídia do modal "Editar perfil"** (`Perfil/perfil.php` + `Perfil/CSS/perfil.css`):
   o banner é **sangria total** — `.editar-fotos { margin: 0 -24px }` cancela o padding de 24px
   do `.modal-sobre`; nunca dimensionar o banner por `width` fixa. Vazio = cor plana
   (`#c9c7d1`) com um botão de adicionar foto. Preenchido = trocar, editar visualização e remover,
   centralizados sobre o banner (ver "Enquadramento de imagens"). A foto fica sobreposta à borda inferior do
   banner, alinhada à esquerda (`align-self: flex-start` + `margin: -48px 0 0 24px`), 96px,
   circular, borda 3px `var(--roxo)` + anel `0 0 0 4px var(--card)`. O `.editar-foto-botao` cobre
   a mídia com `var(--overlay-foto)` e só aparece no hover (`opacity 0 → 1`), centralizado —
   32px de largura no avatar, como no modal do cliente. Os `<input type="file">` ficam
   `display: none`; o avatar abre o seletor pelo
   `<label for>` e o banner pelos botões. Foto template
   do promotor: `resolver_foto_perfil()` → `assets/user_dashboard.svg`, a 55% `contain` com
   filtro de cor. A referência antiga `C:\xampp\htdocs\NATHUS PROJETINHO` não está disponível;
   preservar a geometria atual e as referências do usuário de 2026-10-23 para os controles de mídia.
9. **Vitrine (`home/topo-vitrine.php` + `home/css/vitrine.css`):** header de todas as páginas
   públicas — `home/home.php`, `home/homeAnt.php`, `home/minhas-solicitacoes.php`,
   `Cliente/perfil.php`, `Cliente/solicitacao.php`. As abas `vt-abas` apontam para
   `home.php?campo=organizadores|buffet|espaco` (pill amarela `#fdbd24` na ativa) e "Minhas
   solicitações" só aparece para conta que **não** é promotor. Configurar antes do include:
   `$vt_inicio` (default `homeAnt.php`), `$vt_asset` (default `../`), `$vt_abas`, `$vt_acao`,
   `$vt_voltar` e `$secao_ativa`. `Includes/topbar.php` + `Includes/css/topbar.css` são
   **legado morto** (nenhum include) — não reintroduzir.
10. **Assets home:** pasta `assets/home/` consolidada (ex-`home/assets2/`); referências `../assets/home/...`
11. **Filtros da vitrine (`home/home.php`):** `campo` passa por whitelist
    (`organizadores|buffet|espaco`, default `organizadores`); `busca` é substring
    case-insensitive (`vt_bate()`) sobre nome/frase/cidade, sem `ob_start`/regex e sem card fantasma.
    Os selects são o componente `vt_menu()` (`button` + `input[hidden]` + `ul role="listbox"`),
    **não** `<select>`. Cards `pro-card` (`pro-capa`, `pro-avatar`, `pro-frase`, `pro-meta`,
    `pro-nota`) têm altura uniforme. Em `Projeto`, `pro-preco` mostra o
    preço mínimo por evento ou “Sob consulta” quando o valor está ausente.
    A vitrine, o filtro e as duas visões do perfil calculam esse mínimo pelo
    pacote com o menor preço cadastrado, sem edição manual no perfil.
    Projeto e Atual compartilham o banco local e não usam coluna de preço
    mínimo em `promotor`. Perfis sem pacotes mostram o estado vazio.
    O filtro de preço de `Projeto` oferece só o teto “Até”. Os campos de
    dinheiro usam `data-mascara="moeda"` e validação em `Includes/moeda.php`.
12. **Formulário de solicitação em Projeto:** `localStorage` guarda só os dados do evento
    com debounce de 300ms e validade de 7 dias. O rascunho é limpo após a confirmação do envio.
    O formulário usa datas em DD/MM/AAAA, com calendário opcional, e horários
    em 24 horas. O campo de convidados é obrigatório e rejeita valores acima do limite do
    promotor na digitação e na colagem. A mensagem tem limite de 500 caracteres
    no formulário e no servidor. A caixa começa maior e cresce conforme o conteúdo
    em navegadores com suporte a `field-sizing`. O detalhe mantém mensagens antigas completas
    numa área de altura limitada. A opção de terminar em outro dia é
    automática se o horário final for anterior ao inicial e exige uma data
    final posterior à inicial. Pedidos de um dia mantêm o formato existente,
    e eventos de vários dias gravam as duas datas em `data_festa`. Os campos de contato usam
    `Includes/js/mascaras.js` e validação em `Includes/validar-contato.php`.
    O calendário marca todos os dias dos serviços aceitos pelo promotor como
    reservados. O formulário e o aceite no painel rejeitam qualquer sobreposição,
    incluindo dias entre o início e o término.
    O tipo de festa é opcional na configuração do promotor. Organizadores sem
    tipos aceitam texto livre. Espaços sem tipos e buffets dispensam o campo.
    As opções cadastradas são validadas no servidor. Pedidos sem tipo usam o
    pacote como título em `nome_evento`.
    Novos pedidos guardam os itens do pacote em `pacote_itens_envio`.
    O detalhe e o histórico usam essa cópia quando presente.
13. **Campos de atuação e listagens da vitrine:**
    Um promotor pode ter vários campos. O modal usa checkboxes `campos[]`, restaura todas as
    seleções e grava os valores válidos sem duplicação. Especialidades de buffet e organização
    aparecem juntas quando ambas as áreas estão marcadas; capacidade aparece para Local.
    Grupos desmarcados ficam ocultos/desabilitados e seus detalhes existentes são preservados.
    A estrutura de `promotor_campo` já suporta múltiplas linhas, sem migração adicional.
    `home/home.php` filtra `promotor_campo.campo` pelos valores
    (`organizacao` / `buffet` / `local`); a aba organizadores também inclui promotores **sem**
    nenhum campo (contas recém-criadas), para não desaparecerem. Cards reais linkam para
    `Cliente/perfil.php?id=N&ver_como_cliente=1&campo=...`; a foto vem de `foto_card_promotor()`
   (`Includes/promotor.php`). A capa vem de `banner_publico_promotor()` e usa
   a mesma arte padrão da página de perfil quando falta um banner próprio.
   `home/organizadores.php`, `buffet.php` e `espaco.php` são só stubs
    de redirect para `home.php?campo=...`. A localização fica em `promotor.cidade`/`promotor.estado`
    (editável no modal de `Perfil/perfil.php`).
14. **Conta na vitrine:** `home/conta-modal.php` (`dialog#vt-conta`) só renderiza com sessão.
    Foto do cliente: input de arquivo oculto; label do preview circular de 96px abre o seletor.
    Overlay escuro `rgba(15, 10, 35, .55)` e câmera de 32px aparecem no hover/foco, com fade
    de 0,15s. Enter/Espaço também abrem o seletor; a imagem escolhida atualiza o preview antes de salvar.
    O componente `.conta-perfil` usa o mesmo CSS do dashboard em `Includes/css/global.css`:
    Poppins, nome 20px/peso 400, tipo 15px/peso 300, avatar 46px com contorno e seta preenchida 14px.
    Na vitrine, o fundo é transparente sobre o topo roxo, sem card ou padding externo;
    nome e seta brancos, tipo e contorno do avatar em lilás claro. Em telas pequenas o nome é abreviado.
    O sino `vt-sino` aparece para ambos os tipos logados. Promotores veem até 3 solicitações
    pendentes próprias (`id_promotor`); clientes veem até 3 pedidos (`id_cliente`),
    com prioridade para respostas não lidas de aceite ou recusa. O aviso abre o card
    correspondente e é marcado como lido ao acessar o pedido pelo sino.
    Os links levam a Solicitações do painel para promotores e `home/minhas-solicitacoes.php`
    para clientes. Notificações e menu de conta são exclusivos entre si e fecham com Escape/clique fora.
    Em `Projeto`, o menu usa `ehPromotor()`: promotores têm "Meu perfil",
    "Dashboard" e "Sair" na vitrine e no painel, com as mesmas cores.
    Clientes têm "Editar perfil", "Solicitações enviadas" e "Sair" na vitrine.
    O modal e a rota de salvamento de conta da vitrine são exclusivos de clientes.
    O modal
    envia para `home/salvar-conta.php`, que valida nome/email/telefone/cpf/senha e responde
    `?ok=1` ou `?erro=email|senha|dados`. O `voltar` pós-salvar é whitelist (`home.php`,
    `Cliente/perfil.php?id=N&ver_como_cliente=1`, `Cliente/solicitacao.php?pacote=N&id=N`),
    no mesmo espírito do `destinoAposLogin()` do `Login/`. `home/editar-conta.php` apenas
    redireciona para `homeAnt.php?editar=1`.
15. **Mídia dos perfis demo:** os arquivos do seed seguem `demo-<slug>-{perfil,banner,pacote-1,pacote-2,galeria-1..4}.jpg`
    em `uploads/perfil-promotor`, `uploads/banner`, `uploads/pacotes` e `uploads/galeria`. O banco
    guarda **só o basename** (padrão de `otimizar_e_salvar_imagem()`), e `midia_publica()` resolve
    pela pasta certa. Todas as imagens de galeria são quadradas porque `.item-galeria` usa
    `aspect-ratio: 1` + `object-fit: cover`. Reimportar recria as linhas demo; os arquivos no disco
    são reutilizados por nome. Os perfis 6..10 usam 120 fotografias novas do Pexels, com
    arquivos próprios `demo-<slug>-*`, diferentes dos perfis 1..5. Fontes e hashes estão em
    `Banco/perfis-demo-imagens.json`; manter esse registro sincronizado ao substituir essas fotos.
    Imagens estáticas foram comprimidas mantendo nomes e formatos: JPEG/WebP com qualidade
    80 e PNG com transparência preservada e otimização de paleta limitada a qualidade 90–100.
    Reduzir resoluções excessivas conforme o uso (avatar 600px, pacote 1200px, galeria 1400px,
    banner 1600px); não ampliar imagens pequenas nem recomprimir originais de enquadramento.
16. **Escala dos perfis:** manter o painel (`Perfil/CSS/perfil.css`) e a visão pública
    (`Cliente/css/perfil.css`) compactos: Poppins, banner 150px, avatar 96px, nome 24px
    (22px no celular), títulos 18px, leitura 14px e padding de seção 20px 24px no desktop.
    No celular, reduzir o padding e preservar ações de pelo menos 44px de altura. Ajustes de
    escala solicitados para o perfil devem ser conferidos nas duas páginas.
    Exceção: os pacotes públicos usam `auto-fit` com mínimo fluido de 320px, preenchendo
    a largura disponível para dar mais destaque às imagens e aos serviços.
    Em `Projeto`, o perfil público segue a largura e os espaçamentos do painel,
    com capa de até 220px e avatar de 118px no desktop. As duas telas usam
    etiquetas roxas com cantos de 8px. No perfil público, o título usa o nome
    fantasia quando cadastrado, seguido do nome próprio se for diferente.

## Enquadramento de imagens

- Ao verificar uploads órfãos, usar `midia_publica()` também na limpeza. Há caminhos legados
  válidos em `uploads/` e subpastas; conferir apenas `uploads/perfil-promotor/<basename>`
  pode apagar a referência de uma foto existente ao voltar do salvamento de um pacote.
  A limpeza e a renderização devem aceitar os mesmos caminhos. Teste de regressão:
  `php Tests/pacote-foto-perfil.php legado` (mais `canonical` e `ausente`).

- Todo upload de foto/imagem usa `Includes/enquadramento.php` (definir `$enquadramento_base`
  relativo à página antes do include). Ao selecionar arquivo abre o editor compartilhado:
  imagem arrastável, moldura do recorte, uma barra de zoom e cabeçalho com Voltar/Aplicar.
  As setas do teclado movem a imagem quando o canvas tem foco. Sem controles visíveis de
  posição horizontal/vertical. Formatos: JPEG/PNG/WebP, original até 5 MB.
- Avatar e galeria usam recorte quadrado; pacote 16:9 (mesma proporção no painel, perfil
  público e solicitação); banner 8:3. O banner continua responsivo com `cover` nos seus containers.
  O editor permite voltar sem aplicar, sem enviar o formulário principal.
  Galeria usa "Aplicar e salvar" e atualiza somente a foto pertencente ao promotor logado.
- O arquivo recortado segue o fluxo normal de upload. O campo auxiliar `<campo>__original`
  guarda o original validado em `uploads/originais/<pasta>/<arquivo>.original`, sem coluna nova
  no banco. `apagar_upload_antigo()` remove as duas versões. Imagens antigas sem original
  partem da imagem disponível; novos ajustes passam a preservar essa fonte.
- Banner vazio tem só adicionar foto; preenchido tem trocar, editar visualização e remover,
  em `.eq-banner-acoes` sobre a imagem. Remoção é persistida apenas ao salvar o perfil pelo
  campo `remover_banner`; escolher/aplicar uma substituta cancela a remoção pendente.
  Avatar tem somente a câmera para trocar foto e usa moldura circular no editor. Pacotes e
  galeria usam um lápis para editar a visualização; sem botão técnico abaixo das imagens.
- `NathusEnquadramento.reset(input)` limpa o estado ao trocar de pacote no modal. Preservar
  a sobreposição entre banner e avatar em `.editar-fotos`. Botões normais têm raio 6px;
  ações sobre fotos são circulares de 44px. Câmeras usam os SVGs preenchidos de `assets/`;
  navegação, edição, remoção e zoom usam `assets/midia-*.svg`, com traço de 2,5px.

## Manutenção da memória (obrigatório a cada alteração)

A memória deste projeto vive nestes dois arquivos — nenhum agente pode fazer mudanças "silenciosas":

1. **Sempre** que criar/alterar/remover arquivos, atualize o `CHANGELOG.md`: adicione a mudança na
   seção da data corrente (crie `## AAAA-MM-DD — resumo` se não existir), dizendo o quê, onde e por quê.
2. Se a mudança afetar **estrutura de pastas, forma de rodar, camadas de CSS ou convenções**,
   atualize também a seção correspondente deste `AGENTS.md` ("Mapa do projeto" ou "Convenções").
3. Ao concluir a tarefa, confira se os dois arquivos refletem o estado atual do projeto.
