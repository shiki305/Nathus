# Nathus 🎉

Plataforma de eventos que conecta **clientes** aos melhores **promotores** — buffets, espaços de
festa e organizadores de eventos. O cliente descobre profissionais, compara pacotes e pede
orçamento; o promotor recebe os pedidos, responde e acompanha os resultados em um painel próprio.

Projeto desenvolvido para o **TCC**.

---

## 📌 Sobre o projeto

O Nathus tem dois lados que conversam entre si:

**Lado do cliente**
- Landing page institucional e vitrine pública de profissionais.
- Busca de **organizadores**, **buffets** e **espaços de festa** por cidade e tipo.
- Perfil público do promotor: sobre, pacotes com preço, galeria de fotos e avaliações.
- Pedido de orçamento direto no perfil e acompanhamento em "Minhas solicitações"
  (com notificações quando o promotor responde).
- Cadastro de cliente em etapas.

**Lado do promotor** (painel com menu lateral)
- **Dashboard** — visão geral: status das solicitações e resumo do negócio.
- **Meu Perfil** — edita "sobre", frase de destaque, pacotes e galeria de fotos.
- **Solicitações** — aceita ou recusa os pedidos recebidos, com detalhes do evento.
- **Meus Serviços** — os serviços oferecidos e seus detalhes.
- **Minha Assinatura** — consulta o plano e compara as opções disponíveis.
- **Relatórios** — solicitações por ano, com gráficos e exportação em PDF.
- Cadastro em **8 etapas** (dados, tipo de promotor, contato, endereço, pagamento, especialidades,
  sobre/fotos), com pagamento por **cartão** ou **PIX**.

---

## 🛠️ Tecnologias

| Camada | O que usa |
|---|---|
| Back-end | **PHP puro** (sem framework e sem Composer), MySQLi |
| Banco de dados | **MySQL** (XAMPP) |
| Front-end | HTML, CSS e JavaScript **vanilla** |
| Fontes | locais (Poppins, Nunito) — sem dependência de CDN |
| Idioma | Interface toda em **pt-BR** |

---

## ✅ Requisitos

- **Windows**.
- **XAMPP** instalado — de preferência em `C:\xampp` (o script também procura em `D:\xampp`,
  `E:\xampp` e nas pastas `Program Files`).
- MySQL do XAMPP com **root sem senha** (o padrão do XAMPP).

> Você **não precisa** ligar o Apache. O site sobe pelo **servidor embutido do PHP** que o próprio
> script de setup inicia.

---

## 🚀 Como rodar (passo a passo)

### 1. Instale o XAMPP
Baixe em <https://www.apachefriends.org/> e instale normalmente (recomendado em `C:\xampp`).

### 2. Coloque o projeto dentro do `htdocs`
Copie a pasta do projeto (a que **contém** a pasta `Projeto`) para dentro do `htdocs`, por exemplo:

```
C:\xampp\htdocs\Nathus\
└─ Projeto\        <- a aplicação fica aqui dentro
```

> O nome da pasta pode ser outro — o script detecta o nome dela automaticamente para montar a URL.

### 3. Abra o `setup.cmd`
Dê **duplo clique** em:

```
C:\xampp\htdocs\Nathus\Projeto\setup.cmd
```

Vai abrir um **menu numerado** no prompt de comando. No topo ele já mostra o estado atual
(XAMPP, PHP, MySQL e site).

### 4. Escolha as opções nesta ordem

| Opção | O que faz |
|:---:|---|
| **`1`** | **Verificar programas** — confere XAMPP, PHP e MySQL e as extensões necessárias. |
| **`2`** | **Subir o MySQL** — inicia o MySQL do XAMPP (não faz nada se já estiver rodando). |
| **`3`** | **Criar o banco (estrutura)** — importa `Banco/nathus.sql` (só se o banco ainda não existir). |
| **`4`** | **Importar perfis demo** — importa `Banco/perfis-demo.sql`: 32 contas de exemplo, com pacotes, galeria e solicitações. |
| **`5`** | **Abrir o site** — sobe o servidor e abre o navegador. |

Ou seja: **`1` → `2` → `3` → `4` → `5`**. Depois de cada ação o menu volta para a lista.

### 5. Acesse o site
A opção `5` abre o navegador em algo como:

```
http://localhost:8000/Nathus/Projeto/
```

A porta `8000` é só um exemplo: o script escolhe a **primeira porta livre** a partir de 8000
(8000, 8001, 8002…). O endereço exato aparece na tela e no menu.

### 6. Para encerrar
Use a opção **`6` (Parar o site)** no próprio menu, ou dê duplo clique em `Projeto\parar.cmd`.

---

## 🔁 Outras opções do menu

| Opção | O que faz |
|:---:|---|
| **`6`** | **Parar o site** — encerra o servidor do site. |
| **`7`** | **Diagnóstico completo** — roda o `verificar.cmd` (programas, portas, banco, contagens e arquivos). |
| **`8`** | **Resetar o banco** — **apaga** o banco e recria tudo do zero, salvando antes um backup em `Projeto/backups/`. Pede confirmação digitando `APAGAR`. |
| **`0`** | **Sair** do menu. |

### Atalhos pela linha de comando

```cmd
setup.cmd                 :: abre o menu
setup.cmd reset           :: reseta o banco direto, sem passar pelo menu
setup.cmd --porta 8090    :: força o site a subir numa porta específica
setup.cmd --sem-navegador :: sobe o site sem abrir o navegador
```

Também existem, soltos na pasta `Projeto`, o **`verificar.cmd`** (diagnóstico) e o
**`parar.cmd`** (encerra o site), que funcionam fora do menu.

---

## 👤 Contas de demonstração

Depois de importar os perfis demo (opção `4`), use qualquer conta abaixo com a senha **`123`**:

| Login | Perfil |
|---|---|
| `buffet1@nathus.com` … `buffet10@nathus.com` | Buffet |
| `local1@nathus.com` … `local10@nathus.com` | Espaço de festa |
| `organizador1@nathus.com` … `organizador10@nathus.com` | Organizador de eventos |
| `cliente1@nathus.com`, `cliente2@nathus.com` | Cliente |

São **32 contas demo** no total. As contas de promotor vêm com pacotes, galeria e solicitações;
as de cliente servem para testar o lado de quem contrata.

> Importar ou resetar os perfis demo **muda os IDs** das contas. Se você já estava logado,
> **saia e entre de novo** para a sessão se atualizar.

---

## 📁 Estrutura do projeto

```
Nathus/
├─ README.md
├─ AGENTS.md                 guia técnico do projeto
├─ CHANGELOG.md              histórico de mudanças
└─ Projeto/                  aplicação
   ├─ index.php              redireciona para home/homeAnt.php
   ├─ setup.cmd              menu de setup     (verificar.cmd / parar.cmd ao lado)
   ├─ Banco/                 nathus.sql (estrutura) + perfis-demo.sql (contas demo)
   ├─ Landing Page/          landing institucional
   ├─ home/                  página inicial + vitrine pública (busca e perfis)
   ├─ Login/                 login
   ├─ Cadastro-geral/        cadastro de cliente e de promotor (etapas e1..e8)
   ├─ Cliente/               perfil público do promotor
   ├─ Dashboard/             painel do promotor
   ├─ Perfil/                "Meu Perfil" do promotor (sobre, pacotes e galeria)
   ├─ Servicos/              "Meus Serviços"
   ├─ Solicitacoes/          solicitações recebidas
   ├─ Assinatura/            planos
   ├─ Relatorios/            relatórios por ano + PDF
   ├─ Includes/              PHP, CSS e JS compartilhados
   ├─ assets/  Fontes/       imagens e fontes locais
   └─ uploads/               imagens enviadas (só as fotos demo-* vão no repositório)
```

---

## 🧩 Como usar o site (visão rápida)

1. Abra a URL do site — você cai na **página inicial**.
2. Sem login, dá para navegar pela **vitrine** e abrir o perfil dos profissionais.
   Os botões **Entrar** e **Cadastre-se** ficam no topo.
3. Faça login como **cliente** (ex.: `cliente1@nathus.com`) para **pedir um orçamento** em um
   perfil e acompanhar em **Minhas solicitações**.
4. Faça login como **promotor** (ex.: `local1@nathus.com`) para ver as **Solicitações** recebidas,
   responder, editar o perfil e conferir os **Relatórios**.

---

## ℹ️ Observações

- **Banco de dados:** conexão configurada em `Projeto/Includes/conexao.php` como
  `localhost`, usuário `root`, **sem senha**, banco `nathus`.
- **Imagens enviadas:** a pasta `Projeto/uploads/` guarda as fotos dos perfis. Ela é recriada
  automaticamente conforme as imagens são enviadas. No repositório vão **apenas as fotos das
  contas de demonstração** (arquivos `demo-*`), para os perfis de exemplo aparecerem completos;
  as demais imagens não são versionadas.
- **Logs:** `Projeto/setup.log` (setup do banco) e `Projeto/php-erros.log` (erros do PHP).
- **Backups:** a opção `8` (resetar) salva um dump antes de apagar, em `Projeto/backups/`.

---

## 📄 Licença

Projeto acadêmico (TCC). Uso livre para fins de estudo e apresentação.
