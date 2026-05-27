-- =====================================================
-- Pizzaria TT — Projeto de Extensão
-- Arquivo: sql/pizzaria_tt.sql
-- Responsável principal: João (Banco de Dados)
-- Suporte: Eduardo Daniel (Back-end PHP)
-- Descrição: Script de criação completo do banco de dados pizzaria_tt.
--            Importe diretamente pelo phpMyAdmin do XAMPP.
--
-- Como usar:
--   1. Inicie o Apache e MySQL no XAMPP Control Panel
--   2. Abra http://localhost/phpmyadmin
--   3. Clique em "Importar" > selecione este arquivo > "Executar"
--   4. Acesse http://localhost/pizzaria/Pizzaria-main/admin/setup.php
--      para criar o usuário administrador padrão (admin / admin123)
-- =====================================================

-- Remove e recria o banco — permite importação limpa sem erros
DROP DATABASE IF EXISTS pizzaria_tt;

CREATE DATABASE pizzaria_tt
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pizzaria_tt;

-- =====================================================
-- TABELA: pizzas
-- Armazena o cardápio completo (pizzas e bebidas).
--
-- Decisões de modelagem:
--   • 'ativa' permite desativar itens sem precisar deletar
--     (histórico de itens_pedido continua íntegro)
--   • preco_p / preco_g são NULL para bebidas (tamanho único)
--   • 'categoria' separa pizzas de bebidas sem tabela extra
-- =====================================================
CREATE TABLE pizzas (
    id          INT AUTO_INCREMENT  PRIMARY KEY,
    nome        VARCHAR(100)        NOT NULL,
    descricao   TEXT                NOT NULL,
    preco_p     DECIMAL(8,2)        DEFAULT NULL  COMMENT 'Tamanho P — NULL para bebidas',
    preco_m     DECIMAL(8,2)        NOT NULL      COMMENT 'Tamanho M ou preço único (bebidas)',
    preco_g     DECIMAL(8,2)        DEFAULT NULL  COMMENT 'Tamanho G — NULL para bebidas',
    imagem      VARCHAR(255)        NOT NULL DEFAULT 'Logo_Pizzaria.jpg',
    categoria   ENUM('pizza','bebida') NOT NULL DEFAULT 'pizza',
    ativa       TINYINT(1)          NOT NULL DEFAULT 1,
    criado_em   TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: clientes
-- Dados do cliente coletados no checkout.
-- Um mesmo CPF pode aparecer várias vezes (pedidos distintos).
-- =====================================================
CREATE TABLE clientes (
    id        INT AUTO_INCREMENT  PRIMARY KEY,
    nome      VARCHAR(150)        NOT NULL,
    cpf       CHAR(11)            NOT NULL  COMMENT 'Apenas dígitos: 12345678901',
    telefone  VARCHAR(20)         NOT NULL,
    endereco  TEXT                NOT NULL,
    criado_em TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Índice para buscas por CPF (ex: histórico do cliente no admin)
    INDEX idx_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: pedidos
-- Registro principal de cada pedido.
-- Vinculado ao cliente via FK para garantir integridade.
--
-- Decisões de modelagem:
--   • numero_pedido gerado pelo PHP (TT-AAAAMMDD-NNNN)
--     para ser legível pelo cliente e pelo entregador
--   • valor_total calculado/verificado no PHP para evitar
--     manipulação de preços pelo cliente
--   • status como ENUM força valores controlados
-- =====================================================
CREATE TABLE pedidos (
    id              INT AUTO_INCREMENT   PRIMARY KEY,
    numero_pedido   VARCHAR(20)          NOT NULL UNIQUE COMMENT 'Ex: TT-20250429-0001',
    cliente_id      INT                  NOT NULL,
    valor_total     DECIMAL(10,2)        NOT NULL,
    forma_pagamento ENUM('Pix','Cartão','Dinheiro') NOT NULL,
    status          ENUM('recebido','preparando','saiu_entrega','entregue','cancelado')
                    NOT NULL DEFAULT 'recebido',
    obs             TEXT                 DEFAULT NULL,
    criado_em       TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- FK garante que todo pedido tem um cliente válido
    CONSTRAINT fk_pedido_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    -- Índice para listagem por data (painel admin filtra por dia)
    INDEX idx_criado_em (criado_em),
    -- Índice para busca rápida pelo número (rota /api/status.php)
    INDEX idx_numero_pedido (numero_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: itens_pedido
-- Detalha quais produtos compõem cada pedido.
--
-- Decisão importante: preco_unitario é registrado no
-- momento da venda — se o cardápio mudar amanhã, o
-- histórico continua correto.
-- =====================================================
CREATE TABLE itens_pedido (
    id             INT AUTO_INCREMENT  PRIMARY KEY,
    pedido_id      INT                 NOT NULL,
    pizza_id       INT                 NOT NULL,
    tamanho        ENUM('P','M','G','UN') NOT NULL DEFAULT 'M' COMMENT 'UN = tamanho único (bebidas)',
    quantidade     INT                 NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(8,2)        NOT NULL COMMENT 'Preço no momento da venda',

    CONSTRAINT fk_item_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_item_pizza
        FOREIGN KEY (pizza_id) REFERENCES pizzas(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    -- Índice para JOIN pedidos ↔ itens no painel admin
    INDEX idx_pedido_id (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: usuarios_admin
-- Usuários do painel administrativo.
-- Senha armazenada como hash bcrypt via password_hash().
-- Crie o primeiro admin com: admin/setup.php
-- =====================================================
CREATE TABLE usuarios_admin (
    id         INT AUTO_INCREMENT  PRIMARY KEY,
    usuario    VARCHAR(50)         NOT NULL UNIQUE,
    senha_hash VARCHAR(255)        NOT NULL COMMENT 'Hash bcrypt gerado por password_hash()',
    criado_em  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- DADOS: Cardápio completo (20 pizzas + 2 bebidas)
-- Preços: P = médio − R$5 / G = médio + R$5
-- =====================================================
INSERT INTO pizzas (nome, descricao, preco_p, preco_m, preco_g, imagem, categoria) VALUES

-- Pizzas salgadas clássicas
('4 Queijos',        'Molho, mussarela, catupiry, provolone e orégano.',                        30.00, 35.00, 40.00, '1-_4_queijos.png',                                     'pizza'),
('Alho e Óleo',      'Molho, mussarela, alho dourado, azeite de oliva e orégano.',              30.00, 35.00, 40.00, '2-Alho_e_Óleo-removebg-preview.png',                   'pizza'),
('Atum',             'Molho, mussarela, atum, cebola roxa e orégano.',                          30.00, 35.00, 40.00, '3-atum-removebg-preview.png',                          'pizza'),
('Brasileira',       'Mussarela, champignon, ervilha e calabresa fatiada.',                     30.00, 35.00, 40.00, '4-brasileira-removebg-preview.png',                    'pizza'),
('Caipira',          'Frango desfiado temperado, milho verde e catupiry.',                      30.00, 35.00, 40.00, '5-caipira-removebg-preview.png',                       'pizza'),
('Calabresa',        'Calabresa artesanal fatiada, cebola e orégano.',                          30.00, 35.00, 40.00, '6-calabresa-removebg-preview.png',                     'pizza'),
('Champignon',       'Champignon fresco, cebola, mussarela e fio de azeite.',                   35.00, 40.00, 45.00, '7-Champignon_PIzza_Crisp-removebg-preview.png',        'pizza'),
('Escarola',         'Escarola refogada, alho frito, champignon e bacon crocante.',             30.00, 35.00, 40.00, '11-escarola-removebg-preview.png',                     'pizza'),
('Frango Catupiry',  'Frango desfiado temperado coberto com catupiry cremoso.',                 30.00, 35.00, 40.00, '13-frango_catupiry-removebg-preview.png',               'pizza'),
('Lombo Catupiry',   'Lombo suíno fatiado, cebola caramelizada e catupiry.',                    30.00, 35.00, 40.00, '15-lombo_catupiry-removebg-preview.png',                'pizza'),
('Marguerita',       'Mussarela fresca, tomate, manjericão e queijo parmesão.',                 30.00, 35.00, 40.00, '16-marguerita-removebg-preview.png',                   'pizza'),
('Mussarela',        'Molho de tomate artesanal, mussarela e orégano. O clássico!',             28.00, 33.00, 38.00, '17-mussarela.png',                                     'pizza'),
('Siciliana Bacon',  'Molho, mussarela, bacon crocante, pimentões e azeitona preta.',           35.00, 40.00, 45.00, '18-Pizza estilo Califórnia Pizza siciliana Bacon.png',  'pizza'),
('Paulista',         'Molho, mussarela, presunto, ervilha e milho verde.',                      30.00, 35.00, 40.00, '19-pizza paulista.png',                                'pizza'),
('Portuguesa',       'Molho, mussarela, presunto, ovo cozido, azeitona, pimentão e cebola.',   30.00, 35.00, 40.00, '20-Portuguesa.png',                                    'pizza'),
('Queijo com Bacon', 'Molho, mussarela generosa, bacon crocante e cheddar derretido.',          32.00, 37.00, 42.00, '21-queijo com bacon.png',                              'pizza'),
('Queijo Pepperoni', 'Molho, mussarela, pepperoni fatiado e blend de queijos especiais.',       35.00, 40.00, 45.00, '22-Queijo pepperoni.png',                              'pizza'),
('Vegetariana',      'Molho, mussarela, abobrinha, pimentão colorido, cebola e champignon.',   30.00, 35.00, 40.00, '23-vegetariana.png',                                   'pizza'),

-- Pizzas doces
('Chocolate M&M',    'Chocolate ao leite derretido coberto com M&M colorido.',                 25.00, 30.00, 35.00, '8-chocolate_M_M-removebg-preview.png',                 'pizza'),
('Chocolate',        'Chocolate ao leite, granulado e fio de leite condensado.',               25.00, 30.00, 35.00, '9-chocolate-removebg-preview.png',                     'pizza'),

-- Bebidas (preco_p e preco_g são NULL — tamanho único)
('Coca-Cola 2L',     'Refrigerante gelado Coca-Cola 2 litros.',                                NULL,  10.00, NULL,  '10-coca_cola_2l-removebg-preview.png',                 'bebida'),
('Fanta 2L',         'Refrigerante gelado Fanta Laranja 2 litros.',                            NULL,   8.00, NULL,  '12-fanta_2l-removebg-preview.png',                     'bebida');


-- =====================================================
-- Usuário admin: criado via admin/setup.php
-- Acesse: http://localhost/pizzaria/Pizzaria-main/admin/setup.php
-- Credenciais padrão: usuário = admin | senha = admin123
-- =====================================================
