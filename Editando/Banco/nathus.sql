CREATE DATABASE IF NOT EXISTS nathus;
USE nathus;

CREATE TABLE IF NOT EXISTS cliente(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(30),
    nome VARCHAR(45),
    senha VARCHAR(255),
    email VARCHAR(100),
    telefone VARCHAR(30),
    cpf VARCHAR(20),
    foto VARCHAR(255) NULL,
    tipo_conta VARCHAR(20) NOT NULL DEFAULT 'cliente',
    UNIQUE KEY uniq_cliente_email (email)
);

CREATE TABLE IF NOT EXISTS promotor(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    nome_fantasia VARCHAR(80) NULL,
    cnpj VARCHAR(25) NULL,
    plano VARCHAR(30),
    metodo_pagamento VARCHAR(30),
    status_plano VARCHAR(30),
    foto_perfil VARCHAR(255) NULL,
    descricao TEXT NULL,

    banner VARCHAR(255) NULL,
    frase_destaque VARCHAR(150) NULL,
    cidade VARCHAR(60) NULL,
    estado VARCHAR(2) NULL,
    espaco_logradouro VARCHAR(90) NULL,
    espaco_numero VARCHAR(20) NULL,
    espaco_bairro VARCHAR(40) NULL,
    espaco_complemento VARCHAR(30) NULL,
    espaco_area_m2 DECIMAL(10,2) NULL,
    capacidade_maxima INT NULL,
    nota_media DECIMAL(3,2) NOT NULL DEFAULT 0,
    total_avaliacoes INT NOT NULL DEFAULT 0,

    FOREIGN KEY(id_cliente) REFERENCES cliente(id)
);

CREATE TABLE IF NOT EXISTS promotor_campo(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_promotor INT NOT NULL,
    campo VARCHAR(40),
    FOREIGN KEY(id_promotor) REFERENCES promotor(id)
);

CREATE TABLE IF NOT EXISTS promotor_especialidade(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_promotor INT NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    FOREIGN KEY(id_promotor) REFERENCES promotor(id)
);

-- Especialidades reais (não usar o nome da categoria como tipo):
--   organizador: 15_anos, casamento, formatura (quem atende os três aparece em qualquer um)
--   buffet: massas, infantil, drinks, pratos, geral (geral também entra na busca das outras)
-- Espaços usam promotor.capacidade_maxima (convidados).
-- Bancos já criados:
--   ALTER TABLE promotor ADD COLUMN capacidade_maxima INT NULL;

CREATE TABLE IF NOT EXISTS promotor_pacote(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_promotor INT NOT NULL,
    titulo VARCHAR(80) NOT NULL,
    imagem VARCHAR(255) NULL,
    itens TEXT NULL,
    preco VARCHAR(50) NULL,
    ordem INT NOT NULL DEFAULT 0,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(id_promotor) REFERENCES promotor(id)
);

CREATE TABLE IF NOT EXISTS promotor_galeria(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_promotor INT NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(id_promotor) REFERENCES promotor(id)
);

-- Migração para bancos que já existiam antes dessas colunas (rodar no phpMyAdmin):
--   ALTER TABLE solicitacoes ADD COLUMN id_promotor INT NULL AFTER id;
-- (a coluna status é adicionada automaticamente pelo Includes/conexao.php)

CREATE TABLE IF NOT EXISTS newsletter(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_newsletter_email (email)
);

CREATE TABLE solicitacoes(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_promotor INT NULL,
    id_cliente INT NULL,
    nome_evento VARCHAR(100) NOT NULL,
    nome_cliente VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(30) NOT NULL,
    horario VARCHAR(80) NULL,
    data_festa VARCHAR(80) NULL,
    local_festa VARCHAR(255) NOT NULL,
    numero_convidados INT NOT NULL,
    observacoes TEXT NULL,
    pacote_selecionado VARCHAR(255) NULL,
    pacote_id INT NULL,
    pacote_itens_envio TEXT NULL,
    respondida_em DATETIME NULL,
    resposta_pendente VARCHAR(8) NULL,
    recebida TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(30) NOT NULL DEFAULT 'pendente',
    KEY idx_solicitacoes_promotor_status (id_promotor, status),
    KEY idx_solicitacoes_cliente_recebida (id_cliente, recebida),
    CONSTRAINT fk_solicitacoes_promotor FOREIGN KEY (id_promotor) REFERENCES promotor (id),
    CONSTRAINT fk_solicitacoes_cliente FOREIGN KEY (id_cliente) REFERENCES cliente (id)
);

INSERT INTO cliente(usuario,nome,senha,email,telefone,cpf)
VALUES('mgel','mgel','4040','migel@gmail.com','12312','23423423432');

SELECT * FROM cliente;
SELECT * FROM promotor;
SELECT * FROM promotor_campo;
