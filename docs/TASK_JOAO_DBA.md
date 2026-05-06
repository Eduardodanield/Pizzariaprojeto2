# Tarefa — João (Banco de Dados)
**Projeto:** Pizzaria TT | **Tech Lead:** Eduardo Daniel

---

## Arquivos que você vai criar

Você não mexe em nenhum arquivo existente. Vai criar os seguintes dentro da pasta `sql/`:

| Arquivo | O que é |
|---|---|
| `sql/schema.sql` | Seu script com os CREATE TABLE de todas as tabelas |
| `sql/seed.sql` | Dados iniciais para teste (pizzas e usuário admin) |

Opcionalmente, entregue também um diagrama (pode ser imagem ou link do dbdiagram.io).

---

## O que você vai fazer

O banco de dados do sistema já existe e funciona. Sua tarefa é **estudar como ele foi estruturado** e recriar o esquema você mesmo, sem receber o arquivo SQL pronto. O objetivo é você praticar modelagem de banco de dados com um caso real.

---

## O sistema precisa guardar 5 coisas

### 1. Pizzas e bebidas (cardápio)
O que precisa salvar de cada item:
- ID (chave primária)
- Nome (ex: "Mussarela")
- Descrição
- Preço P, preço M e preço G — pizzas têm os três; bebidas só têm um preço (o M)
- Nome do arquivo da imagem
- Categoria: `pizza` ou `bebida`
- Se está ativo ou não (1 = sim, 0 = não)
- Data de cadastro

### 2. Clientes
Coletados no checkout. O que salvar:
- ID
- Nome completo
- CPF (só os 11 números, sem ponto e traço)
- Telefone
- Endereço
- Data de cadastro

### 3. Pedidos
Cada pedido feito no sistema. O que salvar:
- ID
- Número do pedido (ex: `TT-20260506-0001`) — deve ser único
- Qual cliente fez (referência à tabela de clientes)
- Valor total
- Forma de pagamento: só pode ser `Pix`, `Cartão` ou `Dinheiro`
- Status: `recebido`, `preparando`, `saiu_entrega`, `entregue` ou `cancelado`
- Observações do cliente (campo opcional)
- Data/hora do pedido

### 4. Itens do pedido
Cada linha de detalhe do pedido (o que foi pedido):
- ID
- Qual pedido é esse (referência à tabela de pedidos)
- Qual item do cardápio (referência à tabela de pizzas)
- Tamanho: `P`, `M`, `G` ou `UN` (para bebidas)
- Quantidade
- Preço unitário **no momento da compra** (salve aqui, não dependa do preço atual do cardápio)

### 5. Usuários do painel admin
- ID
- Nome de usuário (único)
- Senha em hash (nunca salvar a senha direta — use bcrypt)
- Data de cadastro

---

## Relacionamentos

- Um cliente pode ter vários pedidos
- Um pedido tem vários itens
- Cada item aponta para um produto do cardápio
- Se deletar um pedido, os itens dele devem ser deletados junto (CASCADE)
- Não pode deletar um cliente que tem pedidos vinculados

---

## O que entregar

1. **Um diagrama** mostrando as tabelas e como elas se conectam (pode fazer no [dbdiagram.io](https://dbdiagram.io) — é gratuito e fácil)
2. **`sql/schema.sql`** com os `CREATE TABLE` de todas as tabelas
3. **`sql/seed.sql`** com pelo menos 3 pizzas e 1 usuário admin inseridos para testar

---

## Dica rápida

- Use `DECIMAL(8,2)` para valores monetários, nunca `FLOAT`
- Use `ENUM(...)` para campos com opções fixas (status, categoria, tamanho)
- Use `CHAR(11)` para CPF (tamanho fixo)
- Declare as `FOREIGN KEY` para garantir que não vai salvar dados inválidos

---

## IMPORTANTE — Use exatamente esses nomes

O back-end já está pronto com queries que esperam nomes específicos de tabelas e colunas. Se você usar nomes diferentes, o sistema quebra. Use a tabela abaixo como referência obrigatória:

### Tabela `pizzas`
| Coluna | Tipo sugerido |
|---|---|
| `id` | INT PK AUTO_INCREMENT |
| `nome` | VARCHAR(100) |
| `descricao` | TEXT |
| `preco_p` | DECIMAL(8,2) NULL |
| `preco_m` | DECIMAL(8,2) |
| `preco_g` | DECIMAL(8,2) NULL |
| `imagem` | VARCHAR(255) |
| `categoria` | ENUM('pizza','bebida') |
| `ativa` | TINYINT(1) DEFAULT 1 |
| `criado_em` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP |

### Tabela `clientes`
| Coluna | Tipo sugerido |
|---|---|
| `id` | INT PK AUTO_INCREMENT |
| `nome` | VARCHAR(150) |
| `cpf` | CHAR(11) |
| `telefone` | VARCHAR(20) |
| `endereco` | TEXT |
| `criado_em` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP |

### Tabela `pedidos`
| Coluna | Tipo sugerido |
|---|---|
| `id` | INT PK AUTO_INCREMENT |
| `numero_pedido` | VARCHAR(20) UNIQUE |
| `cliente_id` | INT (FK → clientes.id) |
| `valor_total` | DECIMAL(10,2) |
| `forma_pagamento` | ENUM('Pix','Cartão','Dinheiro') |
| `status` | ENUM('recebido','preparando','saiu_entrega','entregue','cancelado') |
| `obs` | TEXT NULL |
| `criado_em` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP |

### Tabela `itens_pedido`
| Coluna | Tipo sugerido |
|---|---|
| `id` | INT PK AUTO_INCREMENT |
| `pedido_id` | INT (FK → pedidos.id) |
| `pizza_id` | INT (FK → pizzas.id) |
| `tamanho` | ENUM('P','M','G','UN') |
| `quantidade` | INT |
| `preco_unitario` | DECIMAL(8,2) |

### Tabela `usuarios_admin`
| Coluna | Tipo sugerido |
|---|---|
| `id` | INT PK AUTO_INCREMENT |
| `usuario` | VARCHAR(50) UNIQUE |
| `senha_hash` | VARCHAR(255) |
| `criado_em` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP |

> Se tiver dúvida, fala com o Eduardo Daniel antes de criar o arquivo final.
