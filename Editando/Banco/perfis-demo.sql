-- Contas demo: 30 promotores (10 buffets, 10 locais, 10 organizadores) e 2 clientes.
-- Login: buffet1..10@nathus.com, local1..10@nathus.com,
-- organizador1..10@nathus.com, cliente1@nathus.com e cliente2@nathus.com.
-- Senha de todas: 123.
-- Ao importar, APAGA E RECRIA as 32 contas demo, pacotes, galerias e solicitacoes
-- associadas. Alteracoes dessas contas serao perdidas. Outras contas sao preservadas.
-- Pode reimportar sem duplicar. Os IDs das contas e perfis serao novos.
-- Imagens: uploads/perfil-promotor, banner, pacotes e galeria, slugs demo-<usuario>-*.
-- Importar depois de Banco/nathus.sql.
SET NAMES utf8mb4;
USE nathus;

-- Endereços fictícios para demonstração. Garante as colunas antes de recriar as contas.
ALTER TABLE promotor
    ADD COLUMN IF NOT EXISTS espaco_logradouro VARCHAR(90) NULL,
    ADD COLUMN IF NOT EXISTS espaco_numero VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS espaco_bairro VARCHAR(40) NULL,
    ADD COLUMN IF NOT EXISTS espaco_complemento VARCHAR(30) NULL,
    ADD COLUMN IF NOT EXISTS espaco_area_m2 DECIMAL(10,2) NULL;

START TRANSACTION;

-- Recria SOMENTE as 32 contas demo listadas abaixo e seus registros associados.
CREATE TEMPORARY TABLE IF NOT EXISTS demo_emails (email VARCHAR(100) PRIMARY KEY);
DELETE FROM demo_emails;
INSERT INTO demo_emails (email) VALUES
    ('buffet1@nathus.com'),
    ('buffet2@nathus.com'),
    ('buffet3@nathus.com'),
    ('buffet4@nathus.com'),
    ('buffet5@nathus.com'),
    ('buffet6@nathus.com'),
    ('buffet7@nathus.com'),
    ('buffet8@nathus.com'),
    ('buffet9@nathus.com'),
    ('buffet10@nathus.com'),
    ('local1@nathus.com'),
    ('local2@nathus.com'),
    ('local3@nathus.com'),
    ('local4@nathus.com'),
    ('local5@nathus.com'),
    ('local6@nathus.com'),
    ('local7@nathus.com'),
    ('local8@nathus.com'),
    ('local9@nathus.com'),
    ('local10@nathus.com'),
    ('organizador1@nathus.com'),
    ('organizador2@nathus.com'),
    ('organizador3@nathus.com'),
    ('organizador4@nathus.com'),
    ('organizador5@nathus.com'),
    ('organizador6@nathus.com'),
    ('organizador7@nathus.com'),
    ('organizador8@nathus.com'),
    ('organizador9@nathus.com'),
    ('organizador10@nathus.com'),
    ('cliente1@nathus.com'),
    ('cliente2@nathus.com');

DELETE pg FROM promotor_galeria pg JOIN promotor p ON p.id=pg.id_promotor JOIN cliente c ON c.id=p.id_cliente JOIN demo_emails d ON d.email=c.email;
DELETE pp FROM promotor_pacote pp JOIN promotor p ON p.id=pp.id_promotor JOIN cliente c ON c.id=p.id_cliente JOIN demo_emails d ON d.email=c.email;
DELETE pe FROM promotor_especialidade pe JOIN promotor p ON p.id=pe.id_promotor JOIN cliente c ON c.id=p.id_cliente JOIN demo_emails d ON d.email=c.email;
DELETE pc FROM promotor_campo pc JOIN promotor p ON p.id=pc.id_promotor JOIN cliente c ON c.id=p.id_cliente JOIN demo_emails d ON d.email=c.email;
DELETE s FROM solicitacoes s JOIN cliente c ON c.id=s.id_cliente JOIN demo_emails d ON d.email=c.email;
DELETE s FROM solicitacoes s JOIN promotor p ON p.id=s.id_promotor JOIN cliente c ON c.id=p.id_cliente JOIN demo_emails d ON d.email=c.email;
DELETE p FROM promotor p JOIN cliente c ON c.id=p.id_cliente JOIN demo_emails d ON d.email=c.email;
DELETE c FROM cliente c JOIN demo_emails d ON d.email=c.email;


-- ---------------------------------------------------------------- buffet1
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet1', 'Rosana Almeida', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet1@nathus.com', '(21) 98811-1001', '30110174001', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet1@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='buffet1@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Sabor & Arte Buffet', '12.345.678/0001-01', 'anual', 'pix', 'ativo', 'demo-buffet1-perfil.jpg', 'Cozinha autoral comandada por uma equipe de chefs formados em hotelaria. Trabalhamos com ingredientes frescos de produtores da região e montamos o cardápio junto com o cliente. Atendemos casamentos, aniversários e eventos corporativos em todo o Grande Rio.', 'demo-buffet1-banner.jpg', 'Gastronomia autoral para festas inesquecíveis', 'Rio de Janeiro', 'RJ', 180, 4.90, 127
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'massas'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='massas');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'geral'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='geral');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Buffet Clássico', 'demo-buffet1-pacote-1.jpg', 'Entrada volante com 6 opções\nDois pratos principais quentes\nGuarnição de arroz, farofa e legumes\nSobremesa de mesa\nEquipe de salão e copeiros\nLouça, talheres e toalhas', 95.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Buffet Autoral', 'demo-buffet1-pacote-2.jpg', 'Estação de antepastos montada na hora\nTrês pratos principais\nMassas frescas preparadas na frente do convidado\nMesa de sobremesas com 8 opções\nBar de sucos e águas aromatizadas\nEquipe completa de salão', 148.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet1-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet1-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet1-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet1-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet1-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet1-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet1-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet1-galeria-4.jpg');

-- ---------------------------------------------------------------- buffet2
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet2', 'Cláudio Nogueira', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet2@nathus.com', '(21) 98811-1002', '30110174002', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet2@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='buffet2@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Mesa Farta Eventos', '12.345.678/0001-02', 'mensal', 'cartao', 'ativo', 'demo-buffet2-perfil.jpg', 'Há 18 anos servindo comida brasileira de verdade em festas de família. Nossa cozinha funciona como um restaurante: tudo é feito no dia do evento, com receitas que passam de geração em geração. Levamos o buffet completo, do fogão ao café.', 'demo-buffet2-banner.jpg', 'Comida de verdade, servida com capricho', 'Niterói', 'RJ', 220, 4.70, 84
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'pratos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='pratos');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'geral'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='geral');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Feijoada Completa', 'demo-buffet2-pacote-1.jpg', 'Feijoada tradicional com carnes selecionadas\nArroz branco e arroz com brócolis\nCouve refogada, farofa e laranja\nTorresmo crocante\nCaipirinha de boas-vindas\nEquipe de apoio por 6 horas', 78.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Almoço de Domingo', 'demo-buffet2-pacote-2.jpg', 'Estação de saladas com 8 itens\nDois pratos quentes à escolha\nGuarnições variadas\nMesa de doces caseiros\nSucos naturais e refrigerantes\nMontagem e desmontagem inclusas', 112.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet2-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet2-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet2-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet2-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet2-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet2-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet2-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet2-galeria-4.jpg');

-- ---------------------------------------------------------------- buffet3
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet3', 'Patrícia Lemos', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet3@nathus.com', '(24) 98811-1003', '30110174003', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet3@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='buffet3@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Doce Infância Buffet', '12.345.678/0001-03', 'anual', 'cartao', 'ativo', 'demo-buffet3-perfil.jpg', 'Especialistas em festas infantis em Petrópolis e região serrana. Cuidamos do buffet, da mesa de doces e da recreação com monitores treinados. Cardápio pensado para crianças e aprovado pelos pais, com opções sem açúcar e sem lactose.', 'demo-buffet3-banner.jpg', 'Festas infantis com sabor de casa', 'Petrópolis', 'RJ', 120, 4.85, 213
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'infantil'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='infantil');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'geral'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='geral');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Festa Mirim', 'demo-buffet3-pacote-1.jpg', 'Mini sanduíches e salgados assados\nPipoca e algodão-doce\nBolo decorado tema livre\nMesa de doces com 6 variedades\nSucos e refrigerantes liberados\n2 monitores por 4 horas', 68.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Festa Completa', 'demo-buffet3-pacote-2.jpg', 'Cardápio infantil completo\nEstufa de mini pizzas e hot dog\nBolo de 3 andares\nMesa de doces com 10 variedades\nPintura facial e oficina de slime\n4 monitores por 5 horas', 104.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet3-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet3-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet3-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet3-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet3-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet3-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet3-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet3-galeria-4.jpg');

-- ---------------------------------------------------------------- buffet4
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet4', 'Tiago Barreto', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet4@nathus.com', '(21) 98811-1004', '30110174004', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet4@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='buffet4@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Bar & Coquetelaria Aurora', '12.345.678/0001-04', 'mensal', 'pix', 'ativo', 'demo-buffet4-perfil.jpg', 'Bar móvel com bartenders que preparam drinks na frente dos convidados. Criamos cartas autorais com e sem álcool, feitas sob medida para o estilo do evento. Estrutura completa: balcão, iluminação, gelo e equipe.', 'demo-buffet4-banner.jpg', 'Drinks autorais e bar montado no seu evento', 'Rio de Janeiro', 'RJ', 150, 4.60, 58
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'drinks'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='drinks');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'geral'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='geral');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Bar Essencial', 'demo-buffet4-pacote-1.jpg', '4 drinks autorais no cardápio\n2 bartenders por 4 horas\nBalcão e iluminação de apoio\nGelo, copos e descartáveis\nVersões sem álcool para todas as opções', 58.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Bar Assinatura', 'demo-buffet4-pacote-2.jpg', '8 drinks autorais, incluindo clássicos\n3 bartenders por 6 horas\nBalcão cenográfico com névoa\nEstufa de petiscos volantes\nMenu impresso com o nome do evento', 96.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet4-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet4-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet4-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet4-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet4-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet4-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet4-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet4-galeria-4.jpg');

-- ---------------------------------------------------------------- buffet5
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet5', 'Fernanda Duarte', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet5@nathus.com', '(21) 98811-1005', '30110174005', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet5@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='buffet5@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Cozinha da Villa', '12.345.678/0001-05', 'anual', 'pix', 'ativo', 'demo-buffet5-perfil.jpg', 'Massas frescas produzidas no dia, com molhos preparados lentamente. A cozinha funciona em Nova Iguaçu e atende toda a Baixada Fluminense. Cardápio enxuto e bem executado, com opções vegetarianas em todas as etapas.', 'demo-buffet5-banner.jpg', 'Massas frescas feitas na hora', 'Nova Iguaçu', 'RJ', 200, 4.75, 96
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'massas'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='massas');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'pratos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='pratos');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Noite das Massas', 'demo-buffet5-pacote-1.jpg', '3 massas frescas à escolha\nMolhos bolonhesa, branco e ao pesto\nPão de alho artesanal\nEstação de queijos e frios\nTiramisù de sobremesa\nEquipe de salão por 5 horas', 88.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Mesa Italiana', 'demo-buffet5-pacote-2.jpg', 'Antepasto completo com bruschettas\n4 massas frescas preparadas na hora\nRisoto de cogumelos\nCarré de cordeiro com ervas\nMesa de doces italianos\nSommelier de vinhos por 4 horas', 156.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet5-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet5-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet5-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet5-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet5-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet5-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet5-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet5-galeria-4.jpg');


-- ===========================================================================
-- LOCAIS DE FESTA
-- ===========================================================================

-- ---------------------------------------------------------------- local1
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local1', 'Marcelo Prado', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local1@nathus.com', '(21) 98811-2001', '30110174006', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local1@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='local1@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Espaço Villa Aurora', '12.345.678/0001-06', 'anual', 'pix', 'ativo', 'demo-local1-perfil.jpg', 'Casa de eventos com jardim de 1.200 m² no Alto da Boa Vista. O salão principal tem pé-direito duplo e vista para o verde, com área externa para cerimônias ao ar livre. Estacionamento para 60 carros e cozinha industrial à disposição do buffet contratado.', 'demo-local1-banner.jpg', 'Um jardim para celebrar o que importa', 'Rio de Janeiro', 'RJ', 250, 4.80, 61, 'Rua Jardim da Aurora', '120', 'Alto da Boa Vista', 'Portão principal'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Locação Diurna', 'demo-local1-pacote-1.jpg', 'Uso do salão e jardim por 8 horas\nMesas e cadeiras para 250 convidados\nCozinha industrial liberada\nEstacionamento com manobrista\nSegurança e limpeza inclusas', 3500.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Locação Completa', 'demo-local1-pacote-2.jpg', 'Uso do espaço por 12 horas\nCerimônia ao ar livre com arco\nMesas, cadeiras e louça para 250\nCamarim para noivos\nGerador de energia e plano B para chuva', 5800.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local1-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local1-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local1-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local1-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local1-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local1-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local1-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local1-galeria-4.jpg');

-- ---------------------------------------------------------------- local2
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local2', 'Denise Carvalho', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local2@nathus.com', '(21) 98811-2002', '30110174007', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local2@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='local2@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Salão Cristal Recepções', '12.345.678/0001-07', 'mensal', 'cartao', 'ativo', 'demo-local2-perfil.jpg', 'Salão clássico no Centro de Niterói, a cinco minutos da barca. Lustres de cristal, piso de mármore e capacidade para 300 pessoas sentadas. Estrutura pronta para receber qualquer buffet, com copa independente e gerador próprio.', 'demo-local2-banner.jpg', 'Elegância clássica no coração da cidade', 'Niterói', 'RJ', 300, 4.65, 44, 'Rua dos Lustres', '48', 'Centro', 'Salão térreo'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Salão Principal', 'demo-local2-pacote-1.jpg', 'Salão por 6 horas\nMesas redondas e cadeiras para 300\nCopa para buffet com geladeiras\nSom ambiente e microfone\nRecepcionista e segurança', 2800.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Salão + Mezanino', 'demo-local2-pacote-2.jpg', 'Salão e mezanino por 10 horas\nÁrea VIP para 40 pessoas\nCamarim com espelho e ar-condicionado\nIluminação cênica ajustável\nManobrista por 5 horas', 4600.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local2-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local2-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local2-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local2-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local2-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local2-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local2-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local2-galeria-4.jpg');

-- ---------------------------------------------------------------- local3
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local3', 'Eduardo Salles', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local3@nathus.com', '(24) 98811-2003', '30110174008', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local3@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='local3@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Quinta das Palmeiras', '12.345.678/0001-08', 'anual', 'cartao', 'ativo', 'demo-local3-perfil.jpg', 'Quinta de 4.000 m² em Itaipava, cercada por palmeiras centenárias e jardins. Recebe até 400 convidados em cerimônias ao ar livre, com área coberta de reserva para dias de chuva. Hospedagem para 20 pessoas em chalés no terreno.', 'demo-local3-banner.jpg', 'Área verde ampla para festas ao ar livre', 'Petrópolis', 'RJ', 400, 4.90, 73, 'Estrada das Palmeiras do Vale', '410', 'Itaipava', 'Entrada pela alameda'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Cerimônia no Jardim', 'demo-local3-pacote-1.jpg', 'Jardim e área coberta por 8 horas\nCadeiras para 400 convidados\nArco de cerimônia e tapete\nCozinha de apoio\nIluminação externa e gerador', 4200.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Quinta Completa', 'demo-local3-pacote-2.jpg', 'Espaço inteiro por 2 dias\nHospedagem em 5 chalés\nCerimônia, jantar e brunch\nPiscina liberada para convidados\nEquipe de manutenção no local', 9800.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local3-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local3-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local3-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local3-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local3-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local3-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local3-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local3-galeria-4.jpg');

-- ---------------------------------------------------------------- local4
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local4', 'Bianca Rocha', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local4@nathus.com', '(21) 98811-2004', '30110174009', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local4@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='local4@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Terraço Maré', '12.345.678/0001-09', 'mensal', 'pix', 'ativo', 'demo-local4-perfil.jpg', 'Terraço na orla da Barra com vista aberta para o mar e pôr do sol garantido. Ambiente climatizado com paredes de vidro, varanda externa e bar central. Ideal para festas de 15 anos, casamentos intimistas e eventos corporativos.', 'demo-local4-banner.jpg', 'Vista para o mar e pé na areia', 'Rio de Janeiro', 'RJ', 180, 4.70, 39, 'Avenida Horizonte da Maré', '205', 'Barra da Tijuca', 'Terraço'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Terraço Sunset', 'demo-local4-pacote-1.jpg', 'Terraço por 6 horas a partir das 16h\nMesas e cadeiras para 180 convidados\nBar central equipado\nSom com playlist personalizada\nEquipe de apoio e limpeza', 5200.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Terraço Premium', 'demo-local4-pacote-2.jpg', 'Terraço e salão por 10 horas\nMobiliário assinado\nIluminação cênica para o pôr do sol\nCozinha equipada para o buffet\nCamarim privativo com vista', 7900.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local4-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local4-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local4-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local4-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local4-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local4-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local4-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local4-galeria-4.jpg');

-- ---------------------------------------------------------------- local5
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local5', 'Rodrigo Menezes', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local5@nathus.com', '(21) 98811-2005', '30110174010', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local5@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='local5@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Galpão Norte Eventos', '12.345.678/0001-10', 'anual', 'pix', 'ativo', 'demo-local5-perfil.jpg', 'Galpão de 900 m² com pé-direito de 9 metros, em Duque de Caxias. Estrutura industrial reformada, com mezanino, palco e som profissional embutido. Recebe feiras, formaturas e festas de grande porte com até 600 convidados.', 'demo-local5-banner.jpg', 'Espaço industrial para festas grandes', 'Duque de Caxias', 'RJ', 600, 4.55, 27, 'Rua do Galpão Norte', '72', 'Centro', 'Galpão 2'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Galpão Base', 'demo-local5-pacote-1.jpg', 'Galpão por 8 horas\nMesas e cadeiras para 400 convidados\nPalco de 40 m² com som\nEstacionamento para 120 carros\nSegurança e brigada de incêndio', 2200.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Galpão Produção', 'demo-local5-pacote-2.jpg', 'Galpão e mezanino por 12 horas\nEstrutura para 600 convidados\nPalco, iluminação e telão de LED\nCamarins e área de apoio\nLimpeza durante e após o evento', 4900.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local5-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local5-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local5-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local5-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local5-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local5-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local5-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local5-galeria-4.jpg');


-- ===========================================================================
-- ORGANIZADORES DE EVENTOS
-- ===========================================================================

-- ---------------------------------------------------------------- organizador1
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador1', 'Camila Lima', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador1@nathus.com', '(21) 98811-3001', '30110174011', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador1@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='organizador1@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Ateliê de Festas Lima', '12.345.678/0001-11', 'anual', 'pix', 'ativo', 'demo-organizador1-perfil.jpg', 'Assessoria e cerimonial com foco em festas de 15 anos, casamentos e formaturas. Assumimos a produção do convite à desmontagem: fornecedores, roteiro, decoração e equipe no dia. Trabalhamos com um número limitado de eventos por mês.', 'demo-organizador1-banner.jpg', 'Cada detalhe pensado para emocionar', 'Rio de Janeiro', 'RJ', 200, 4.95, 154
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, '15_anos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='15_anos');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'casamento'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='casamento');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'formatura'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='formatura');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Assessoria no Dia', 'demo-organizador1-pacote-1.jpg', 'Reunião de alinhamento 30 dias antes\nCronograma do dia minuto a minuto\nCoordenação de fornecedores\n2 coordenadores no evento\nMontagem e desmontagem supervisionadas', 4500.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Produção Completa', 'demo-organizador1-pacote-2.jpg', 'Planejamento desde o início\nCuradoria e contratação de fornecedores\nProjeto de decoração assinado\nEnsaio e cerimonial\nEquipe de 6 pessoas no dia', 12500.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador1-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador1-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador1-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador1-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador1-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador1-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador1-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador1-galeria-4.jpg');

-- ---------------------------------------------------------------- organizador2
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador2', 'Juliana Freitas', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador2@nathus.com', '(21) 98811-3002', '30110174012', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador2@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='organizador2@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Roteiro 15', '12.345.678/0001-12', 'mensal', 'cartao', 'ativo', 'demo-organizador2-perfil.jpg', 'Produtora dedicada exclusivamente a festas de 15 anos. Criamos um roteiro personalizado para a aniversariante, com valsas, entrada, vídeos e surpresas. Atendemos Niterói, São Gonçalo e a região oceânica.', 'demo-organizador2-banner.jpg', 'Especialistas em festas de 15 anos', 'Niterói', 'RJ', 150, 4.80, 112
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, '15_anos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='15_anos');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Roteiro Essencial', 'demo-organizador2-pacote-1.jpg', 'Reunião de briefing e moodboard\nRoteiro da festa escrito\nCoordenação de 4 fornecedores\n2 produtoras no dia\nPlaylist e ordem das valsas', 3800.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Roteiro Completo', 'demo-organizador2-pacote-2.jpg', 'Produção do convite ao pós-festa\nDecoração e mobiliário contratados\nEnsaio da valsa com coreógrafo\nVídeo de entrada e retrospectiva\nEquipe de 5 pessoas no dia', 9200.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador2-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador2-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador2-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador2-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador2-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador2-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador2-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador2-galeria-4.jpg');

-- ---------------------------------------------------------------- organizador3
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador3', 'Rafael Andrade', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador3@nathus.com', '(24) 98811-3003', '30110174013', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador3@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='organizador3@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Cerimonial Vale', '12.345.678/0001-13', 'anual', 'cartao', 'ativo', 'demo-organizador3-perfil.jpg', 'Cerimonialista especializado em casamentos ao ar livre na serra. Cuidamos da cerimônia, do jantar e da festa, com atenção especial a planos de chuva e logística de convidados. Parceria fixa com fotógrafos e floristas da região.', 'demo-organizador3-banner.jpg', 'Casamentos ao ar livre, do início ao fim', 'Petrópolis', 'RJ', 250, 4.90, 87
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'casamento'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='casamento');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Cerimônia', 'demo-organizador3-pacote-1.jpg', 'Projeto da cerimônia\nEscolha de celebrante e músicos\nEnsaio dos noivos e padrinhos\nCoordenação do cortejo\nPlano de chuva documentado', 6200.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Casamento Completo', 'demo-organizador3-pacote-2.jpg', 'Cerimônia e recepção\nCuradoria de todos os fornecedores\nDecoração e flores inclusas\nTransporte de convidados\nEquipe de 8 pessoas por 12 horas', 18900.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador3-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador3-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador3-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador3-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador3-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador3-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador3-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador3-galeria-4.jpg');

-- ---------------------------------------------------------------- organizador4
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador4', 'Larissa Monteiro', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador4@nathus.com', '(21) 98811-3004', '30110174014', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador4@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='organizador4@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Formatura & Cia', '12.345.678/0001-14', 'mensal', 'pix', 'ativo', 'demo-organizador4-perfil.jpg', 'Produtora de formaturas do ensino médio e superior, com experiência em comissões de alunos. Organizamos a colação de grau, o baile e a viagem de formatura, com prestação de contas transparente para a turma.', 'demo-organizador4-banner.jpg', 'Formaturas com produção completa', 'Rio de Janeiro', 'RJ', 300, 4.65, 64
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'formatura'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='formatura');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Colação de Grau', 'demo-organizador4-pacote-1.jpg', 'Locação de beca e capelo\nCerimonial da colação\nSom, palco e iluminação\nFotografia oficial\nEnsaio dos formandos', 2900.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Baile de Formatura', 'demo-organizador4-pacote-2.jpg', 'Produção do baile completo\nDecoração temática\nBuffet e bar contratados\nDJ, banda e pista de dança\nGestão financeira da turma', 8600.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador4-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador4-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador4-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador4-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador4-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador4-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador4-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador4-galeria-4.jpg');

-- ---------------------------------------------------------------- organizador5
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador5', 'Gustavo Teixeira', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador5@nathus.com', '(21) 98811-3005', '30110174015', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador5@nathus.com');
SET @c = (SELECT id FROM cliente WHERE email='organizador5@nathus.com' AND tipo_conta='promotor' LIMIT 1);
INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Studio Festa Viva', '12.345.678/0001-15', 'anual', 'cartao', 'ativo', 'demo-organizador5-perfil.jpg', 'Estúdio que reúne decoração, buffet e cerimonial em um contrato só. A equipe própria executa a festa do começo ao fim, o que reduz custos e evita desencontros entre fornecedores. Atende a Baixada Fluminense e o Grande Rio.', 'demo-organizador5-banner.jpg', 'Decoração, buffet e cerimonial em um só lugar', 'Nova Iguaçu', 'RJ', 180, 4.70, 98
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);
SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);
INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, '15_anos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='15_anos');
INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'casamento'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='casamento');
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Festa Essencial', 'demo-organizador5-pacote-1.jpg', 'Decoração de mesa principal e convidados\nBuffet com 2 pratos quentes\nBolo e mesa de doces\nCerimonial no dia\nEquipe própria de 6 pessoas', 3400.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);
INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Festa Assinatura', 'demo-organizador5-pacote-2.jpg', 'Projeto de decoração exclusivo\nBuffet completo com estações\nBar de drinks e mesa de doces\nDJ e iluminação cênica\nCerimonial e equipe de 10 pessoas', 7800.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador5-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador5-galeria-1.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador5-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador5-galeria-2.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador5-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador5-galeria-3.jpg');
INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador5-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador5-galeria-4.jpg');


-- ---------------------------------------------------------------------------
-- 2) Clientes (sem registro de promotor)
-- ---------------------------------------------------------------------------
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'cliente1', 'Cliente Demo 1', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'cliente1@nathus.com', '', '', 'cliente'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='cliente1@nathus.com');
INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'cliente2', 'Cliente Demo 2', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'cliente2@nathus.com', '', '', 'cliente'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='cliente2@nathus.com');

-- ---------------------------------------------------------------------------

-- Mais cinco promotores por categoria
-- buffet6: Celebra Buffet

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet6', 'Ana Carvalho', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet6@nathus.com', '(21) 98844-1006', '40110174106', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet6@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='buffet6@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Celebra Buffet', '12.345.678/0002-06', 'anual', 'pix', 'ativo', 'demo-buffet6-perfil.jpg', 'Cozinha autoral comandada por uma equipe de chefs formados em hotelaria. Trabalhamos com ingredientes frescos de produtores da região e montamos o cardápio junto com o cliente. Atendemos casamentos, aniversários e eventos corporativos em todo o Grande Rio.', 'demo-buffet6-banner.jpg', 'Gastronomia autoral para festas inesquecíveis', 'Rio de Janeiro', 'RJ', 180, 4.90, 127
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'massas'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='massas');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'geral'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='geral');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Buffet Clássico', 'demo-buffet6-pacote-1.jpg', 'Entrada volante com 6 opções\nDois pratos principais quentes\nGuarnição de arroz, farofa e legumes\nSobremesa de mesa\nEquipe de salão e copeiros\nLouça, talheres e toalhas', 95.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Buffet Autoral', 'demo-buffet6-pacote-2.jpg', 'Estação de antepastos montada na hora\nTrês pratos principais\nMassas frescas preparadas na frente do convidado\nMesa de sobremesas com 8 opções\nBar de sucos e águas aromatizadas\nEquipe completa de salão', 148.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet6-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet6-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet6-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet6-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet6-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet6-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet6-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet6-galeria-4.jpg');


-- buffet7: Temperos da Festa

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet7', 'Ricardo Azevedo', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet7@nathus.com', '(21) 98844-1007', '40110174107', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet7@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='buffet7@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Temperos da Festa', '12.345.678/0002-07', 'mensal', 'cartao', 'ativo', 'demo-buffet7-perfil.jpg', 'Há 18 anos servindo comida brasileira de verdade em festas de família. Nossa cozinha funciona como um restaurante: tudo é feito no dia do evento, com receitas que passam de geração em geração. Levamos o buffet completo, do fogão ao café.', 'demo-buffet7-banner.jpg', 'Comida de verdade, servida com capricho', 'Niterói', 'RJ', 220, 4.70, 84
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'pratos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='pratos');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'geral'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='geral');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Feijoada Completa', 'demo-buffet7-pacote-1.jpg', 'Feijoada tradicional com carnes selecionadas\nArroz branco e arroz com brócolis\nCouve refogada, farofa e laranja\nTorresmo crocante\nCaipirinha de boas-vindas\nEquipe de apoio por 6 horas', 78.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Almoço de Domingo', 'demo-buffet7-pacote-2.jpg', 'Estação de saladas com 8 itens\nDois pratos quentes à escolha\nGuarnições variadas\nMesa de doces caseiros\nSucos naturais e refrigerantes\nMontagem e desmontagem inclusas', 112.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet7-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet7-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet7-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet7-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet7-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet7-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet7-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet7-galeria-4.jpg');


-- buffet8: Mesa Viva Gastronomia

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet8', 'Marina Rocha', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet8@nathus.com', '(21) 98844-1008', '40110174108', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet8@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='buffet8@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Mesa Viva Gastronomia', '12.345.678/0002-08', 'anual', 'cartao', 'ativo', 'demo-buffet8-perfil.jpg', 'Especialistas em festas infantis em Petrópolis e região serrana. Cuidamos do buffet, da mesa de doces e da recreação com monitores treinados. Cardápio pensado para crianças e aprovado pelos pais, com opções sem açúcar e sem lactose.', 'demo-buffet8-banner.jpg', 'Festas infantis com sabor de casa', 'Petrópolis', 'RJ', 120, 4.85, 213
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'infantil'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='infantil');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'geral'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='geral');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Festa Mirim', 'demo-buffet8-pacote-1.jpg', 'Mini sanduíches e salgados assados\nPipoca e algodão-doce\nBolo decorado tema livre\nMesa de doces com 6 variedades\nSucos e refrigerantes liberados\n2 monitores por 4 horas', 68.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Festa Completa', 'demo-buffet8-pacote-2.jpg', 'Cardápio infantil completo\nEstufa de mini pizzas e hot dog\nBolo de 3 andares\nMesa de doces com 10 variedades\nPintura facial e oficina de slime\n4 monitores por 5 horas', 104.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet8-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet8-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet8-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet8-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet8-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet8-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet8-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet8-galeria-4.jpg');


-- buffet9: Quintal dos Sabores

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet9', 'Paulo Martins', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet9@nathus.com', '(21) 98844-1009', '40110174109', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet9@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='buffet9@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Quintal dos Sabores', '12.345.678/0002-09', 'mensal', 'pix', 'ativo', 'demo-buffet9-perfil.jpg', 'Bar móvel com bartenders que preparam drinks na frente dos convidados. Criamos cartas autorais com e sem álcool, feitas sob medida para o estilo do evento. Estrutura completa: balcão, iluminação, gelo e equipe.', 'demo-buffet9-banner.jpg', 'Drinks autorais e bar montado no seu evento', 'Rio de Janeiro', 'RJ', 150, 4.60, 58
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'drinks'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='drinks');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'geral'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='geral');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Bar Essencial', 'demo-buffet9-pacote-1.jpg', '4 drinks autorais no cardápio\n2 bartenders por 4 horas\nBalcão e iluminação de apoio\nGelo, copos e descartáveis\nVersões sem álcool para todas as opções', 58.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Bar Assinatura', 'demo-buffet9-pacote-2.jpg', '8 drinks autorais, incluindo clássicos\n3 bartenders por 6 horas\nBalcão cenográfico com névoa\nEstufa de petiscos volantes\nMenu impresso com o nome do evento', 96.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet9-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet9-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet9-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet9-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet9-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet9-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet9-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet9-galeria-4.jpg');


-- buffet10: Doce Encontro Buffet

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'buffet10', 'Beatriz Fonseca', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'buffet10@nathus.com', '(21) 98844-1010', '40110174110', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='buffet10@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='buffet10@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Doce Encontro Buffet', '12.345.678/0002-10', 'anual', 'pix', 'ativo', 'demo-buffet10-perfil.jpg', 'Massas frescas produzidas no dia, com molhos preparados lentamente. A cozinha funciona em Nova Iguaçu e atende toda a Baixada Fluminense. Cardápio enxuto e bem executado, com opções vegetarianas em todas as etapas.', 'demo-buffet10-banner.jpg', 'Massas frescas feitas na hora', 'Nova Iguaçu', 'RJ', 200, 4.75, 96
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'buffet'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='buffet');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'massas'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='massas');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'pratos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='pratos');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Noite das Massas', 'demo-buffet10-pacote-1.jpg', '3 massas frescas à escolha\nMolhos bolonhesa, branco e ao pesto\nPão de alho artesanal\nEstação de queijos e frios\nTiramisù de sobremesa\nEquipe de salão por 5 horas', 88.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Mesa Italiana', 'demo-buffet10-pacote-2.jpg', 'Antepasto completo com bruschettas\n4 massas frescas preparadas na hora\nRisoto de cogumelos\nCarré de cordeiro com ervas\nMesa de doces italianos\nSommelier de vinhos por 4 horas', 156.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet10-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet10-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet10-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet10-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet10-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet10-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-buffet10-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-buffet10-galeria-4.jpg');


-- local6: Espaço Jardim do Sol

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local6', 'Camila Andrade', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local6@nathus.com', '(21) 98844-2006', '40110174206', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local6@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='local6@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Espaço Jardim do Sol', '12.345.678/0003-06', 'anual', 'pix', 'ativo', 'demo-local6-perfil.jpg', 'Casa de eventos com jardim de 1.200 m² no Alto da Boa Vista. O salão principal tem pé-direito duplo e vista para o verde, com área externa para cerimônias ao ar livre. Estacionamento para 60 carros e cozinha industrial à disposição do buffet contratado.', 'demo-local6-banner.jpg', 'Um jardim para celebrar o que importa', 'Rio de Janeiro', 'RJ', 250, 4.80, 61, 'Rua Jardim do Sol Nascente', '164', 'Alto da Boa Vista', 'Entrada lateral'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Locação Diurna', 'demo-local6-pacote-1.jpg', 'Uso do salão e jardim por 8 horas\nMesas e cadeiras para 250 convidados\nCozinha industrial liberada\nEstacionamento com manobrista\nSegurança e limpeza inclusas', 3500.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Locação Completa', 'demo-local6-pacote-2.jpg', 'Uso do espaço por 12 horas\nCerimônia ao ar livre com arco\nMesas, cadeiras e louça para 250\nCamarim para noivos\nGerador de energia e plano B para chuva', 5800.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local6-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local6-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local6-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local6-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local6-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local6-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local6-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local6-galeria-4.jpg');


-- local7: Casa das Palmeiras

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local7', 'André Ribeiro', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local7@nathus.com', '(21) 98844-2007', '40110174207', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local7@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='local7@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Casa das Palmeiras', '12.345.678/0003-07', 'mensal', 'cartao', 'ativo', 'demo-local7-perfil.jpg', 'Salão clássico no Centro de Niterói, a cinco minutos da barca. Lustres de cristal, piso de mármore e capacidade para 300 pessoas sentadas. Estrutura pronta para receber qualquer buffet, com copa independente e gerador próprio.', 'demo-local7-banner.jpg', 'Elegância clássica no coração da cidade', 'Niterói', 'RJ', 300, 4.65, 44, 'Rua das Palmeiras de Cristal', '91', 'Centro', 'Casa principal'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Salão Principal', 'demo-local7-pacote-1.jpg', 'Salão por 6 horas\nMesas redondas e cadeiras para 300\nCopa para buffet com geladeiras\nSom ambiente e microfone\nRecepcionista e segurança', 2800.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Salão + Mezanino', 'demo-local7-pacote-2.jpg', 'Salão e mezanino por 10 horas\nÁrea VIP para 40 pessoas\nCamarim com espelho e ar-condicionado\nIluminação cênica ajustável\nManobrista por 5 horas', 4600.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local7-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local7-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local7-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local7-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local7-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local7-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local7-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local7-galeria-4.jpg');


-- local8: Salão Vista Alegre

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local8', 'Juliana Castro', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local8@nathus.com', '(21) 98844-2008', '40110174208', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local8@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='local8@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Salão Vista Alegre', '12.345.678/0003-08', 'anual', 'cartao', 'ativo', 'demo-local8-perfil.jpg', 'Quinta de 4.000 m² em Itaipava, cercada por palmeiras centenárias e jardins. Recebe até 400 convidados em cerimônias ao ar livre, com área coberta de reserva para dias de chuva. Hospedagem para 20 pessoas em chalés no terreno.', 'demo-local8-banner.jpg', 'Área verde ampla para festas ao ar livre', 'Petrópolis', 'RJ', 400, 4.90, 73, 'Estrada da Vista Alegre', '305', 'Itaipava', 'Salão e jardim'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Cerimônia no Jardim', 'demo-local8-pacote-1.jpg', 'Jardim e área coberta por 8 horas\nCadeiras para 400 convidados\nArco de cerimônia e tapete\nCozinha de apoio\nIluminação externa e gerador', 4200.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Quinta Completa', 'demo-local8-pacote-2.jpg', 'Espaço inteiro por 2 dias\nHospedagem em 5 chalés\nCerimônia, jantar e brunch\nPiscina liberada para convidados\nEquipe de manutenção no local', 9800.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local8-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local8-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local8-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local8-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local8-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local8-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local8-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local8-galeria-4.jpg');


-- local9: Quinta dos Ipês

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local9', 'Felipe Barros', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local9@nathus.com', '(21) 98844-2009', '40110174209', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local9@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='local9@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Quinta dos Ipês', '12.345.678/0003-09', 'mensal', 'pix', 'ativo', 'demo-local9-perfil.jpg', 'Terraço na orla da Barra com vista aberta para o mar e pôr do sol garantido. Ambiente climatizado com paredes de vidro, varanda externa e bar central. Ideal para festas de 15 anos, casamentos intimistas e eventos corporativos.', 'demo-local9-banner.jpg', 'Vista para o mar e pé na areia', 'Rio de Janeiro', 'RJ', 180, 4.70, 39, 'Avenida dos Ipês da Orla', '223', 'Barra da Tijuca', 'Pavilhão dos Ipês'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Terraço Sunset', 'demo-local9-pacote-1.jpg', 'Terraço por 6 horas a partir das 16h\nMesas e cadeiras para 180 convidados\nBar central equipado\nSom com playlist personalizada\nEquipe de apoio e limpeza', 5200.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Terraço Premium', 'demo-local9-pacote-2.jpg', 'Terraço e salão por 10 horas\nMobiliário assinado\nIluminação cênica para o pôr do sol\nCozinha equipada para o buffet\nCamarim privativo com vista', 7900.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local9-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local9-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local9-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local9-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local9-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local9-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local9-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local9-galeria-4.jpg');


-- local10: Espaço Brisa da Serra

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'local10', 'Renata Costa', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'local10@nathus.com', '(21) 98844-2010', '40110174210', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='local10@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='local10@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes, espaco_logradouro, espaco_numero, espaco_bairro, espaco_complemento)
SELECT @c, 'Espaço Brisa da Serra', '12.345.678/0003-10', 'anual', 'pix', 'ativo', 'demo-local10-perfil.jpg', 'Galpão de 900 m² com pé-direito de 9 metros, em Duque de Caxias. Estrutura industrial reformada, com mezanino, palco e som profissional embutido. Recebe feiras, formaturas e festas de grande porte com até 600 convidados.', 'demo-local10-banner.jpg', 'Espaço industrial para festas grandes', 'Duque de Caxias', 'RJ', 600, 4.55, 27, 'Rua da Brisa da Serra', '116', 'Centro', 'Pavilhão 1'
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'local'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='local');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Galpão Base', 'demo-local10-pacote-1.jpg', 'Galpão por 8 horas\nMesas e cadeiras para 400 convidados\nPalco de 40 m² com som\nEstacionamento para 120 carros\nSegurança e brigada de incêndio', 2200.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Galpão Produção', 'demo-local10-pacote-2.jpg', 'Galpão e mezanino por 12 horas\nEstrutura para 600 convidados\nPalco, iluminação e telão de LED\nCamarins e área de apoio\nLimpeza durante e após o evento', 4900.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local10-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local10-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local10-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local10-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local10-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local10-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-local10-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-local10-galeria-4.jpg');


-- organizador6: Laços Produção de Eventos

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador6', 'Isabela Nunes', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador6@nathus.com', '(21) 98844-3006', '40110174306', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador6@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='organizador6@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Laços Produção de Eventos', '12.345.678/0004-06', 'anual', 'pix', 'ativo', 'demo-organizador6-perfil.jpg', 'Assessoria e cerimonial com foco em festas de 15 anos, casamentos e formaturas. Assumimos a produção do convite à desmontagem: fornecedores, roteiro, decoração e equipe no dia. Trabalhamos com um número limitado de eventos por mês.', 'demo-organizador6-banner.jpg', 'Cada detalhe pensado para emocionar', 'Rio de Janeiro', 'RJ', 200, 4.95, 154
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, '15_anos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='15_anos');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'casamento'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='casamento');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'formatura'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='formatura');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Assessoria no Dia', 'demo-organizador6-pacote-1.jpg', 'Reunião de alinhamento 30 dias antes\nCronograma do dia minuto a minuto\nCoordenação de fornecedores\n2 coordenadores no evento\nMontagem e desmontagem supervisionadas', 4500.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Produção Completa', 'demo-organizador6-pacote-2.jpg', 'Planejamento desde o início\nCuradoria e contratação de fornecedores\nProjeto de decoração assinado\nEnsaio e cerimonial\nEquipe de 6 pessoas no dia', 12500.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador6-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador6-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador6-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador6-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador6-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador6-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador6-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador6-galeria-4.jpg');


-- organizador7: Celebre Assessoria

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador7', 'Bruno Reis', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador7@nathus.com', '(21) 98844-3007', '40110174307', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador7@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='organizador7@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Celebre Assessoria', '12.345.678/0004-07', 'mensal', 'cartao', 'ativo', 'demo-organizador7-perfil.jpg', 'Produtora dedicada exclusivamente a festas de 15 anos. Criamos um roteiro personalizado para a aniversariante, com valsas, entrada, vídeos e surpresas. Atendemos Niterói, São Gonçalo e a região oceânica.', 'demo-organizador7-banner.jpg', 'Especialistas em festas de 15 anos', 'Niterói', 'RJ', 150, 4.80, 112
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, '15_anos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='15_anos');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Roteiro Essencial', 'demo-organizador7-pacote-1.jpg', 'Reunião de briefing e moodboard\nRoteiro da festa escrito\nCoordenação de 4 fornecedores\n2 produtoras no dia\nPlaylist e ordem das valsas', 3800.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Roteiro Completo', 'demo-organizador7-pacote-2.jpg', 'Produção do convite ao pós-festa\nDecoração e mobiliário contratados\nEnsaio da valsa com coreógrafo\nVídeo de entrada e retrospectiva\nEquipe de 5 pessoas no dia', 9200.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador7-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador7-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador7-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador7-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador7-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador7-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador7-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador7-galeria-4.jpg');


-- organizador8: Detalhes & Momentos

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador8', 'Patrícia Gomes', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador8@nathus.com', '(21) 98844-3008', '40110174308', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador8@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='organizador8@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Detalhes & Momentos', '12.345.678/0004-08', 'anual', 'cartao', 'ativo', 'demo-organizador8-perfil.jpg', 'Cerimonialista especializado em casamentos ao ar livre na serra. Cuidamos da cerimônia, do jantar e da festa, com atenção especial a planos de chuva e logística de convidados. Parceria fixa com fotógrafos e floristas da região.', 'demo-organizador8-banner.jpg', 'Casamentos ao ar livre, do início ao fim', 'Petrópolis', 'RJ', 250, 4.90, 87
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'casamento'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='casamento');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Cerimônia', 'demo-organizador8-pacote-1.jpg', 'Projeto da cerimônia\nEscolha de celebrante e músicos\nEnsaio dos noivos e padrinhos\nCoordenação do cortejo\nPlano de chuva documentado', 6200.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Casamento Completo', 'demo-organizador8-pacote-2.jpg', 'Cerimônia e recepção\nCuradoria de todos os fornecedores\nDecoração e flores inclusas\nTransporte de convidados\nEquipe de 8 pessoas por 12 horas', 18900.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador8-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador8-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador8-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador8-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador8-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador8-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador8-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador8-galeria-4.jpg');


-- organizador9: Encontro Produções

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador9', 'Daniel Oliveira', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador9@nathus.com', '(21) 98844-3009', '40110174309', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador9@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='organizador9@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Encontro Produções', '12.345.678/0004-09', 'mensal', 'pix', 'ativo', 'demo-organizador9-perfil.jpg', 'Produtora de formaturas do ensino médio e superior, com experiência em comissões de alunos. Organizamos a colação de grau, o baile e a viagem de formatura, com prestação de contas transparente para a turma.', 'demo-organizador9-banner.jpg', 'Formaturas com produção completa', 'Rio de Janeiro', 'RJ', 300, 4.65, 64
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'formatura'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='formatura');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Colação de Grau', 'demo-organizador9-pacote-1.jpg', 'Locação de beca e capelo\nCerimonial da colação\nSom, palco e iluminação\nFotografia oficial\nEnsaio dos formandos', 2900.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Baile de Formatura', 'demo-organizador9-pacote-2.jpg', 'Produção do baile completo\nDecoração temática\nBuffet e bar contratados\nDJ, banda e pista de dança\nGestão financeira da turma', 8600.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador9-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador9-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador9-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador9-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador9-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador9-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador9-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador9-galeria-4.jpg');


-- organizador10: Essência Eventos

INSERT INTO cliente (usuario, nome, senha, email, telefone, cpf, tipo_conta)
SELECT 'organizador10', 'Sofia Mendes', '$2y$10$LXMb5rUlR2MOS2dv8xXJj.Af9QaP82Jjz.DAmskuwieEAvJBWViwK', 'organizador10@nathus.com', '(21) 98844-3010', '40110174310', 'promotor'
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE email='organizador10@nathus.com');

SET @c = (SELECT id FROM cliente WHERE email='organizador10@nathus.com' AND tipo_conta='promotor' LIMIT 1);

INSERT INTO promotor (id_cliente, nome_fantasia, cnpj, plano, metodo_pagamento, status_plano, foto_perfil, descricao, banner, frase_destaque, cidade, estado, capacidade_maxima, nota_media, total_avaliacoes)
SELECT @c, 'Essência Eventos', '12.345.678/0004-10', 'anual', 'cartao', 'ativo', 'demo-organizador10-perfil.jpg', 'Estúdio que reúne decoração, buffet e cerimonial em um contrato só. A equipe própria executa a festa do começo ao fim, o que reduz custos e evita desencontros entre fornecedores. Atende a Baixada Fluminense e o Grande Rio.', 'demo-organizador10-banner.jpg', 'Decoração, buffet e cerimonial em um só lugar', 'Nova Iguaçu', 'RJ', 180, 4.70, 98
WHERE @c IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor WHERE id_cliente=@c);

SET @p = (SELECT id FROM promotor WHERE id_cliente=@c LIMIT 1);

INSERT INTO promotor_campo (id_promotor, campo)
SELECT @p, 'organizacao'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_campo WHERE id_promotor=@p AND campo='organizacao');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, '15_anos'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='15_anos');

INSERT INTO promotor_especialidade (id_promotor, tipo)
SELECT @p, 'casamento'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_especialidade WHERE id_promotor=@p AND tipo='casamento');

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Festa Essencial', 'demo-organizador10-pacote-1.jpg', 'Decoração de mesa principal e convidados\nBuffet com 2 pratos quentes\nBolo e mesa de doces\nCerimonial no dia\nEquipe própria de 6 pessoas', 3400.00, 1
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=1);

INSERT INTO promotor_pacote (id_promotor, titulo, imagem, itens, preco, ordem)
SELECT @p, 'Festa Assinatura', 'demo-organizador10-pacote-2.jpg', 'Projeto de decoração exclusivo\nBuffet completo com estações\nBar de drinks e mesa de doces\nDJ e iluminação cênica\nCerimonial e equipe de 10 pessoas', 7800.00, 2
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_pacote WHERE id_promotor=@p AND ordem=2);

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador10-galeria-1.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador10-galeria-1.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador10-galeria-2.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador10-galeria-2.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador10-galeria-3.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador10-galeria-3.jpg');

INSERT INTO promotor_galeria (id_promotor, arquivo)
SELECT @p, 'demo-organizador10-galeria-4.jpg'
WHERE @p IS NOT NULL AND NOT EXISTS (SELECT 1 FROM promotor_galeria WHERE id_promotor=@p AND arquivo='demo-organizador10-galeria-4.jpg');



-- Usa as metragens já informadas nas descrições desses espaços demo.
UPDATE promotor p
JOIN cliente c ON c.id = p.id_cliente
JOIN promotor_campo pc ON pc.id_promotor = p.id AND pc.campo = 'local'
SET p.espaco_area_m2 = CASE
    WHEN c.email IN ('local1@nathus.com', 'local6@nathus.com') THEN 1200.00
    WHEN c.email IN ('local3@nathus.com', 'local8@nathus.com') THEN 4000.00
    WHEN c.email IN ('local5@nathus.com', 'local10@nathus.com') THEN 900.00
    WHEN c.email IN ('local2@nathus.com', 'local7@nathus.com') THEN 700.00
    WHEN c.email IN ('local4@nathus.com', 'local9@nathus.com') THEN 480.00
END
WHERE p.espaco_area_m2 IS NULL
  AND c.tipo_conta = 'promotor'
  AND c.email IN ('local1@nathus.com', 'local3@nathus.com', 'local5@nathus.com',
                  'local6@nathus.com', 'local8@nathus.com', 'local10@nathus.com',
                  'local2@nathus.com', 'local7@nathus.com',
                  'local4@nathus.com', 'local9@nathus.com');

DROP TEMPORARY TABLE IF EXISTS demo_emails;
COMMIT;
