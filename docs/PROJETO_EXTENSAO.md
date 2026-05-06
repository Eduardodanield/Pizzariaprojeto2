# Documentação do Projeto de Extensão — Pizzaria TT

**Disciplina:** Projeto de Extensão  
**Instituição:** [Nome da Instituição]  
**Semestre:** 2025/1  
**Equipe:** Eduardo Daniel · Eduardo Matheus · Diogo Neves · João  

---

## 1. Estrutura Básica da Empresa

### 1.1 Segmento Principal
Alimentação — food service, segmento de pizzaria delivery.

### 1.2 Área de Atuação
A **Pizzaria TT (Pizzaria Pi)** atua no ramo de entrega de pizzas por delivery na região de São Paulo-SP (Rua Santo Amaro, nº 199). Fundada em março de 2024, é uma empresa de pequeno porte com foco em agilidade no atendimento e qualidade nos produtos.

### 1.3 Produtos e Serviços Oferecidos
- **Pizzas salgadas** em três tamanhos (P, M e G): 18 sabores disponíveis no cardápio, incluindo opções clássicas (Calabresa, Mussarela, Marguerita), premium (Champignon, Queijo Pepperoni, Siciliana Bacon) e especiais (Vegetariana, Escarola, Portuguesa).
- **Pizzas doces:** Chocolate M&M e Chocolate ao leite.
- **Bebidas:** Coca-Cola 2L e Fanta 2L.
- **Serviço:** Delivery com rastreamento de entrega (integração planejada com Glympse).

### 1.4 Público-Alvo
Famílias e jovens adultos da região que buscam praticidade no pedido de pizzas via celular ou computador, sem necessidade de ligação telefônica. Consumidores habituados a plataformas de delivery digital (iFood, Uber Eats) mas que preferem pedir diretamente com a pizzaria.

### 1.5 Forma de Atendimento
- **Canal principal:** Cardápio digital web (acesso pelo navegador, sem instalação de app).
- **Confirmação:** Chatbot via WhatsApp Business (integração planejada).
- **Rastreamento:** Link SMS gerado automaticamente pela API Glympse no momento da saída para entrega.
- **Expansão:** Integração com marketplaces (iFood e Uber Eats) como canais secundários de venda.

### 1.6 Diferenciais Competitivos
| Diferencial | Descrição |
|---|---|
| Cardápio interativo | Seleção de tamanho (P/M/G) com cálculo dinâmico do total |
| Pedido sem app | Funciona direto no navegador, sem instalação |
| Rastreio de entrega | Link Glympse enviado por SMS ao cliente no momento do despacho |
| Chatbot 24h | WhatsApp Business automatiza confirmações e dúvidas fora do horário |
| Painel em tempo real | Admin atualiza status do pedido; cliente pode consultar pelo número |

---

## 2. Levantamento de Dados

### 2.1 Realidade Atual da Empresa
A Pizzaria TT foi fundada em março de 2024 como empresa de pequeno porte. Antes deste sistema, os pedidos eram recebidos exclusivamente via WhatsApp pessoal, sem nenhum registro estruturado. Os problemas identificados foram:

- Pedidos recebidos em mensagens soltas no WhatsApp, sem numeração nem histórico organizado.
- Sem controle de status (o cliente não sabia em que etapa estava seu pedido).
- Sem registro de dados do cliente para fidelização.
- Sem relatório de vendas — impossível saber quais pizzas vendem mais.
- Entregador sem forma de compartilhar localização em tempo real com o cliente.

### 2.2 Necessidades Operacionais Identificadas
1. Registro estruturado de cada pedido com número único rastreável.
2. Dados do cliente (nome, CPF, telefone, endereço) salvos no banco para histórico.
3. Controle de fluxo de produção: recebido → preparando → saiu para entrega → entregue.
4. Interface administrativa para que a cozinha veja os pedidos do dia e atualize status.
5. Canal de comunicação automatizado para confirmação de pedido (WhatsApp).

### 2.3 Recursos Disponíveis
- Computador com XAMPP instalado (Apache + MySQL + PHP).
- Equipe de 4 alunos com conhecimentos em HTML, CSS, JavaScript e PHP.
- Serviço XAMPP para rodar localmente sem custo de servidor.
- APIs públicas e gratuitas (Glympse, WhatsApp Business, iFood) para integrações futuras.

### 2.4 Objetivos a Serem Alcançados
1. Reduzir o tempo médio de recebimento de pedido de ~5 minutos (via WhatsApp manual) para menos de 1 minuto (formulário web direto ao banco).
2. Eliminar pedidos perdidos: todos ficam registrados no banco com data e hora.
3. Dar visibilidade ao cliente sobre o status da entrega.
4. Organizar o fluxo da cozinha com painel de pedidos do dia.
5. Construir base de dados de clientes para ações de marketing futuras.

### 2.5 Processos Internos Mapeados

```
Cliente acessa o cardápio web
         ↓
Seleciona pizza(s), tamanho e quantidade
         ↓
Preenche dados pessoais e endereço no checkout
         ↓
Clica em "Finalizar pedido"
         ↓
Sistema registra cliente + pedido + itens no MySQL (transação)
         ↓
[STUB] Confirmação automática via WhatsApp Business
         ↓
Cozinha vê o pedido no painel admin (status: "Recebido")
         ↓
Admin atualiza status para "Preparando"
         ↓
Ao despachar, admin atualiza para "Saiu para Entrega"
[STUB] Sistema envia link Glympse por SMS ao cliente
         ↓
Entrega realizada → admin marca "Entregue"
```

### 2.6 Dificuldades que o Sistema Resolve

| Dificuldade Anterior | Solução Implementada |
|---|---|
| Pedidos perdidos no WhatsApp | Banco de dados MySQL com número único por pedido |
| Sem histórico de clientes | Tabela `clientes` com CPF, telefone e endereço |
| Falta de controle de status | ENUM de status + painel admin com AJAX |
| Sem noção do que sai mais | Tabela `itens_pedido` permite relatórios futuros |
| Entregador sem rastreio | Stub da API Glympse pronto para integrar |

### 2.7 Requisitos do Sistema

**Funcionais:**
- RF01: Exibir cardápio com imagens, descrição, tamanhos e preços.
- RF02: Permitir seleção de tamanho (P/M/G) com atualização dinâmica do preço.
- RF03: Gerenciar carrinho (adicionar, remover, calcular total).
- RF04: Coletar dados do cliente no checkout (nome, CPF, telefone, endereço, pagamento).
- RF05: Registrar pedido no banco em transação atômica (cliente + pedido + itens).
- RF06: Retornar número do pedido ao cliente após confirmação.
- RF07: Permitir consulta de status pelo número do pedido.
- RF08: Painel administrativo com listagem de pedidos do dia.
- RF09: Admin pode atualizar status de qualquer pedido.
- RF10: Login com autenticação segura (bcrypt) para o painel admin.

**Não-Funcionais:**
- RNF01: Tempo de resposta da API inferior a 2 segundos em rede local.
- RNF02: Interface responsiva (funciona em celular e desktop).
- RNF03: Senhas armazenadas como hash bcrypt (nunca em texto puro).
- RNF04: Prepared statements PDO em 100% das queries (prevenção de SQL Injection).
- RNF05: Mensagens de erro em português, claras para o usuário final.

---

## 3. Solução Proposta

### 3.1 Arquitetura do Sistema

O sistema segue uma arquitetura em **três camadas** clássica:

```
┌─────────────────────────────────────────┐
│  CAMADA DE APRESENTAÇÃO (Front-end)     │
│  HTML5 · CSS3 · Tailwind CSS · JS ES6+ │
│  Rodando no navegador do cliente        │
└────────────────────┬────────────────────┘
                     │  fetch() → JSON
                     ▼
┌─────────────────────────────────────────┐
│  CAMADA DE APLICAÇÃO (Back-end PHP)     │
│  PHP 8+ procedural · Apache (XAMPP)    │
│  api/ · config/ · admin/               │
└────────────────────┬────────────────────┘
                     │  PDO + Prepared Statements
                     ▼
┌─────────────────────────────────────────┐
│  CAMADA DE DADOS (Banco de Dados)       │
│  MySQL · phpMyAdmin · XAMPP            │
│  Banco: pizzaria_tt                    │
└─────────────────────────────────────────┘
```

### 3.2 Telas Desenvolvidas e Funcionalidades

| Tela / Arquivo | Responsável | Funcionalidades |
|---|---|---|
| `index.php` | Eduardo Matheus | Cardápio dinâmico, carrinho, checkout completo |
| `js/script.js` | Eduardo Matheus | fetch API, carrinho JS, validações, Toastify |
| `admin/login.php` | Eduardo Daniel | Autenticação bcrypt com proteção contra session fixation |
| `admin/index.php` | Eduardo Daniel | Painel de pedidos do dia, filtros por status, AJAX update |
| `admin/setup.php` | Eduardo Daniel | Criação do primeiro usuário admin |
| `api/pizzas.php` | Eduardo Daniel | GET — retorna cardápio em JSON |
| `api/pedido.php` | Eduardo Daniel | POST — persiste pedido em transação PDO |
| `api/status.php` | Eduardo Daniel | GET — consulta status pelo número do pedido |
| `sql/pizzaria_tt.sql` | João | DDL + DML completo, índices, FKs, 22 itens de seed |

### 3.3 Estrutura de Pastas do Projeto

```
Pizzaria-main/
├── assets/                  → Imagens do cardápio e logo [Diogo]
├── styles/                  → CSS Tailwind compilado [Matheus/Diogo]
├── index.php               → Página principal [Eduardo Matheus]
├── script.js                → Lógica front-end [Eduardo Matheus]
├── tailwind.config.js       → Configuração do Tailwind
├── package.json             → Dependências npm
├── config/
│   └── db.php               → Conexão PDO [Eduardo Daniel]
├── api/
│   ├── pizzas.php           → GET cardápio [Eduardo Daniel]
│   ├── pedido.php           → POST pedido [Eduardo Daniel]
│   └── status.php           → GET status [Eduardo Daniel]
├── admin/
│   ├── login.php            → Autenticação [Eduardo Daniel]
│   ├── logout.php           → Encerrar sessão [Eduardo Daniel]
│   ├── index.php            → Painel de pedidos [Eduardo Daniel]
│   ├── atualizar_status.php → AJAX status [Eduardo Daniel]
│   └── setup.php            → Setup inicial [Eduardo Daniel]
├── sql/
│   └── pizzaria_tt.sql      → Script do banco [João]
└── docs/
    └── PROJETO_EXTENSAO.md  → Esta documentação [Eduardo Daniel]
```

### 3.4 Modelo de Dados (Banco pizzaria_tt)

```
┌───────────────┐        ┌──────────────────┐        ┌───────────────┐
│   clientes    │        │     pedidos      │        │  itens_pedido │
├───────────────┤        ├──────────────────┤        ├───────────────┤
│ id (PK)       │◄──┐    │ id (PK)          │◄──┐    │ id (PK)       │
│ nome          │   └────│ cliente_id (FK)  │   └────│ pedido_id(FK) │
│ cpf           │        │ numero_pedido    │        │ pizza_id (FK) │
│ telefone      │        │ valor_total      │        │ tamanho       │
│ endereco      │        │ forma_pagamento  │        │ quantidade    │
│ criado_em     │        │ status (ENUM)    │        │ preco_unit.   │
└───────────────┘        │ obs              │        └───────────────┘
                         │ criado_em        │               │
                         └──────────────────┘               │ FK
                                                    ┌────────▼──────┐
                                                    │    pizzas     │
                                                    ├───────────────┤
                                                    │ id (PK)       │
                                                    │ nome          │
                                                    │ descricao     │
                                                    │ preco_p/m/g   │
                                                    │ imagem        │
                                                    │ categoria     │
                                                    │ ativa         │
                                                    └───────────────┘
```

### 3.5 Justificativa das Escolhas Técnicas

| Escolha | Justificativa |
|---|---|
| **PHP 8 procedural** | Familiaridade da equipe; sem curva de aprendizado de framework; perfeito para projeto acadêmico de pequeno porte |
| **XAMPP + MySQL** | Ambiente local gratuito; phpMyAdmin facilita visualização do banco; padrão de mercado para PHP |
| **PDO + Prepared Statements** | Prevenção nativa de SQL Injection; abstração de banco de dados |
| **Tailwind CSS** | Desenvolvimento ágil de interfaces responsivas sem escrever CSS customizado |
| **Fetch API (JS)** | Nativa nos navegadores modernos; sem dependência de jQuery ou Axios |
| **Toastify JS** | Notificações visuais leves e estilizadas sem impacto no layout |
| **Bcrypt (password_hash)** | Padrão recomendado pelo PHP para armazenamento seguro de senhas |
| **Transação PDO** | Garante atomicidade: cliente + pedido + itens são criados juntos ou nenhum é criado |

---

## 4. Equipe e Responsabilidades

| Aluno | Função | Arquivos Principais | Contribuição |
|---|---|---|---|
| **Eduardo Daniel** | Líder Técnico — Back-end PHP | `config/db.php`, `api/*.php`, `admin/*.php`, `docs/` | Arquitetura PHP, endpoints REST, painel admin, segurança (PDO, bcrypt, sessions), documentação |
| **Eduardo Matheus** | Líder de Front-end | `index.php`, `js/script.js` | Interface do cliente, lógica do carrinho, integração fetch com API, validações client-side, UX/UI |
| **Diogo Neves** | Suporte — Front-end | `assets/`, `styles/`, `tailwind.config.js` | Assets visuais, ajustes de Tailwind, responsividade, ícones e imagens |
| **João** | Banco de Dados | `sql/pizzaria_tt.sql` | Modelagem das tabelas, definição de FKs e índices, script de seed com 22 itens do cardápio |

---

## 5. Considerações de Segurança

O sistema aplica princípios básicos da **Tríade CIA**:

- **Confidencialidade:** Senhas armazenadas como hash bcrypt (nunca em texto puro). Sessão administrativa protegida com `session_regenerate_id()` contra session fixation.
- **Integridade:** Prepared statements PDO em 100% das queries (prevenção de SQL Injection). Transação PDO garante consistência dos dados do pedido. `htmlspecialchars()` em toda saída PHP para prevenir XSS.
- **Disponibilidade:** XAMPP roda localmente com Apache e MySQL independentes. Estrutura simples facilita manutenção e recuperação.

---

## 6. Integrações Externas (Planejadas)

As integrações abaixo estão implementadas como **stubs comentados** nos arquivos PHP, indicando exatamente onde cada integração seria conectada:

| Integração | Arquivo | Status |
|---|---|---|
| WhatsApp Business API | `api/pedido.php` | Stub comentado |
| Glympse (rastreio GPS) | `api/pedido.php` | Stub comentado |
| iFood / Uber Eats | `api/pedido.php` | Stub comentado |

---

*Documento gerado para fins acadêmicos — Projeto de Extensão, 2025.*
