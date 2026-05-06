<div align="center">
  <h1>🍕 Pizzaria TT — Sistema Full Stack</h1>
  <img src="assets/Logo_Pizzaria.jpg" alt="Logo Pizzaria TT" width="160px" style="border-radius: 50%;">
  <br/><br/>
  <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?style=flat&logo=php" alt="PHP 8+"/>
  <img src="https://img.shields.io/badge/MySQL-XAMPP-4479A1?style=flat&logo=mysql" alt="MySQL"/>
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat&logo=tailwindcss" alt="Tailwind"/>
  <img src="https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?style=flat&logo=javascript" alt="JavaScript"/>
</div>

---

## Sobre o Projeto

A **Pizzaria TT (Pizzaria Pi)** é um sistema Full Stack acadêmico desenvolvido como **Projeto de Extensão**. Permite que clientes façam pedidos via cardápio digital interativo, e que a equipe da pizzaria gerencie os pedidos em tempo real por um painel administrativo.

**Back-end:** PHP 8 procedural + PDO + MySQL (XAMPP)  
**Front-end:** HTML5 + Tailwind CSS + JavaScript ES6+ (sem framework)  
**Banco de dados:** MySQL via phpMyAdmin

---

## Equipe

| Aluno | Função | Responsabilidade principal |
|---|---|---|
| **Eduardo Daniel** | Líder Técnico | Back-end PHP, APIs, painel admin, banco |
| **Eduardo Matheus** | Líder Front-end | index.php, script.js, UX/UI |
| **Diogo Neves** | Front-end (suporte) | Assets, Tailwind, componentes visuais |
| **João** | Banco de Dados | sql/pizzaria_tt.sql, modelagem, seeds |

---

## Instalação no XAMPP (passo a passo)

### Pré-requisitos
- [XAMPP](https://www.apachefriends.org/) instalado com **Apache** e **MySQL** iniciados.
- O projeto já deve estar em `c:\xampp\htdocs\pizzaria\Pizzaria-main\`.

---

### Passo 1 — Importar o banco de dados

1. Inicie o **Apache** e o **MySQL** no XAMPP Control Panel.
2. Abra o navegador em: `http://localhost/phpmyadmin`
3. Clique em **"Importar"** na barra superior.
4. Clique em **"Escolher arquivo"** e selecione:
   ```
   c:\xampp\htdocs\pizzaria\Pizzaria-main\sql\pizzaria_tt.sql
   ```
5. Clique em **"Executar"**.
6. O banco `pizzaria_tt` será criado com todas as tabelas e **22 itens do cardápio** já inseridos.

---

### Passo 2 — Criar o usuário administrador

Acesse no navegador:

```
http://localhost/pizzaria/Pizzaria-main/admin/setup.php
```

Isso criará o usuário admin com as credenciais padrão:

| Campo | Valor |
|---|---|
| Usuário | `admin` |
| Senha | `admin123` |

> ⚠️ **Segurança:** Após executar o setup, delete o arquivo `admin/setup.php` para evitar redefinição não autorizada da senha.

---

### Passo 3 — Acessar o sistema

| O que acessar | URL |
|---|---|
| 🍕 Cardápio do cliente | `http://localhost/pizzaria/Pizzaria-main/` |
| 🔧 Painel administrativo | `http://localhost/pizzaria/Pizzaria-main/admin/` |
| 📊 API — cardápio (JSON) | `http://localhost/pizzaria/Pizzaria-main/api/pizzas.php` |
| 📦 API — status do pedido | `http://localhost/pizzaria/Pizzaria-main/api/status.php?numero=TT-AAAAMMDD-0001` |

---

### (Opcional) Passo 4 — Recompilar o Tailwind CSS

Necessário apenas se editar as classes CSS. Requer Node.js instalado.

```bash
# Instala as dependências
npm install

# Inicia o watcher (compila ao salvar)
npm run dev
```

---

## Estrutura de Pastas

```
Pizzaria-main/
├── assets/                  # Imagens do cardápio, logo, background [Diogo]
├── styles/
│   ├── style.css            # CSS fonte com diretivas Tailwind [Diogo/Matheus]
│   └── output.css           # CSS compilado (não editar diretamente) [Diogo/Matheus]
├── index.php                # Página principal — cardápio + checkout [Eduardo Matheus]
├── js/
│   └── script.js            # Lógica do carrinho e integração API [Eduardo Matheus]
├── tailwind.config.js       # Configuração do Tailwind CSS
├── package.json             # Dependências npm
│
├── config/
│   └── db.php               # Conexão PDO com MySQL [Eduardo Daniel]
│
├── api/
│   ├── pizzas.php           # GET  /api/pizzas.php  — retorna cardápio JSON [Eduardo Daniel]
│   ├── pedido.php           # POST /api/pedido.php  — registra pedido [Eduardo Daniel]
│   └── status.php           # GET  /api/status.php  — consulta status [Eduardo Daniel]
│
├── admin/
│   ├── login.php            # Login do painel [Eduardo Daniel]
│   ├── logout.php           # Encerrar sessão [Eduardo Daniel]
│   ├── index.php            # Painel de pedidos do dia [Eduardo Daniel]
│   ├── atualizar_status.php # Endpoint AJAX para atualizar status [Eduardo Daniel]
│   └── setup.php            # ⚠️ Deletar após primeiro uso! [Eduardo Daniel]
│
├── sql/
│   └── pizzaria_tt.sql      # Script completo do banco de dados [João]
│
└── docs/
    └── PROJETO_EXTENSAO.md  # Documentação acadêmica completa [Eduardo Daniel]
```

---

## Endpoints da API

Todos retornam `Content-Type: application/json` com CORS liberado para `localhost`.

### `GET /api/pizzas.php`
Retorna o cardápio completo separado por categoria.

```json
{
  "sucesso": true,
  "pizzas": [
    { "id": 1, "nome": "4 Queijos", "preco_p": 30.0, "preco_m": 35.0, "preco_g": 40.0, ... }
  ],
  "bebidas": [
    { "id": 21, "nome": "Coca-Cola 2L", "preco_m": 10.0, ... }
  ]
}
```

### `POST /api/pedido.php`
Registra um novo pedido. Body JSON:

```json
{
  "nome": "João Silva",
  "cpf": "12345678901",
  "telefone": "11987654321",
  "endereco": "Rua das Flores, 123",
  "pagamento": "Pix",
  "obs": "Sem cebola",
  "itens": [
    { "pizza_id": 1, "tamanho": "M", "quantidade": 2, "preco_unitario": 35.00 }
  ]
}
```

Resposta de sucesso:
```json
{
  "sucesso": true,
  "numero_pedido": "TT-20250429-0001",
  "status": "recebido",
  "valor_total": "70,00"
}
```

### `GET /api/status.php?numero=TT-20250429-0001`
Retorna o status atual e os itens de um pedido.

---

## Banco de Dados

**Nome:** `pizzaria_tt`  
**Credenciais XAMPP padrão:** `root` / *(sem senha)*

| Tabela | Descrição |
|---|---|
| `pizzas` | Cardápio completo (20 pizzas + 2 bebidas) com preços P/M/G |
| `clientes` | Dados dos clientes coletados no checkout |
| `pedidos` | Registro de cada pedido com status rastreável |
| `itens_pedido` | Itens de cada pedido (FK para pizzas e pedidos) |
| `usuarios_admin` | Usuários do painel (senha em hash bcrypt) |

---

## Credenciais Padrão

| Sistema | Usuário | Senha |
|---|---|---|
| Painel Admin | `admin` | `admin123` |
| MySQL (XAMPP) | `root` | *(vazia)* |

> Altere a senha do admin em produção atualizando `admin/setup.php` e re-executando.

---

## Tecnologias Utilizadas

### Front-end
- **HTML5** — Estrutura semântica
- **Tailwind CSS 3** — Estilização responsiva
- **JavaScript ES6+** — Carrinho, fetch API, validações
- **Toastify JS** — Notificações de feedback ao usuário
- **Font Awesome 6** — Ícones

### Back-end
- **PHP 8+** procedural (sem framework)
- **PDO** com prepared statements
- **Apache** via XAMPP

### Banco de Dados
- **MySQL** via XAMPP
- **phpMyAdmin** para administração

### Integrações (stubs planejados)
- **WhatsApp Business API** — confirmação de pedido
- **Glympse API** — rastreio de entrega por SMS
- **iFood / Uber Eats** — sincronização de pedidos de marketplace

---

## Autores

- **Eduardo Daniel Alves Sampaio** — [GitHub](https://github.com/Eduardodanield) · [LinkedIn](https://linkedin.com/in/eduardo-daniel-alves-sampaio-a52133106)
- **Eduardo Matheus** — Front-end
- **Diogo Neves** — Front-end / Assets
- **João** — Banco de Dados

---

*Pizzaria TT &copy; 2025 — Projeto de Extensão Acadêmico*
