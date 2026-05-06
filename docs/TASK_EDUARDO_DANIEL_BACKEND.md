# Tarefa — Eduardo Daniel (Back-end Lead)
**Projeto:** Pizzaria TT | **Papel:** Tech Lead + Back-end

---

## Arquivos sob sua responsabilidade

| Arquivo | O que é |
|---|---|
| `config/db.php` | Conexão com o banco de dados (PDO) |
| `api/pizzas.php` | GET — retorna o cardápio em JSON |
| `api/pedido.php` | POST — registra novo pedido |
| `api/status.php` | GET — consulta status de um pedido |
| `admin/login.php` | Tela e lógica de login do painel |
| `admin/logout.php` | Encerra a sessão do admin |
| `admin/index.php` | Painel de pedidos do dia |
| `admin/atualizar_status.php` | AJAX — atualiza status do pedido |
| `admin/setup.php` | Script de criação do usuário admin inicial |

> Esses arquivos não devem ser alterados pelo restante da equipe sem sua aprovação.

---

## O que está pronto

O back-end está finalizado e funcional. As responsabilidades já implementadas são:

**API Pública**
- Cardápio retornado em JSON separando pizzas e bebidas (`/api/pizzas.php`)
- Registro de pedido com validação completa no servidor, transação PDO e rollback em caso de erro (`/api/pedido.php`)
- Consulta de status com dados do cliente e itens do pedido (`/api/status.php`)

**Painel Administrativo**
- Login com verificação de senha em bcrypt e proteção contra session fixation (`admin/login.php`)
- Dashboard com pedidos do dia, filtro por status e cards de cada pedido (`admin/index.php`)
- Atualização de status via AJAX sem recarregar a página (`admin/atualizar_status.php`)
- Auto-refresh do painel a cada 60 segundos
- Setup inicial do usuário admin (`admin/setup.php`)

**Segurança implementada**
- Prepared statements PDO em todas as queries (sem SQL injection)
- Senhas em bcrypt
- Session regeneration no login
- Validação server-side de todos os campos do pedido
- CORS configurado nos endpoints da API

---

## Suas responsabilidades durante o projeto

### 1. Ser o ponto de referência técnico
A equipe vai ter dúvidas sobre como a API funciona, quais campos são obrigatórios e o que cada endpoint retorna. Você é quem responde.

### 2. Revisar o schema do João
Quando o João entregar o `schema.sql`, você precisa verificar se:
- Os nomes das tabelas e colunas batem com o que o back-end já usa nas queries
- Os tipos de dado são compatíveis (ex: `DECIMAL` para preços, `CHAR(11)` para CPF)
- As constraints fazem sentido para o sistema

### 3. Validar a integração do Diogo
Quando o Diogo disser que a integração está pronta, faça um teste de ponta a ponta:
- Abra o site → adicione itens → finalize um pedido → veja no painel admin se apareceu
- Verifique se os erros estão sendo exibidos corretamente no front-end

### 4. Orientar o Eduardo Matheus se ele travar
Se o Eduardo Matheus não souber onde está a lógica de renderização dos cards ou como o fetch funciona, você explica — é o `js/script.js`, função que faz GET em `/api/pizzas.php` e monta os elementos na DOM.

---

## Referência rápida dos endpoints (para repassar à equipe)

### GET `/api/pizzas.php`
Sem parâmetros. Retorna:
```json
{
  "sucesso": true,
  "pizzas": [ { "id": 1, "nome": "Mussarela", "preco_p": "29.90", "preco_m": "39.90", "preco_g": "49.90", ... } ],
  "bebidas": [ { "id": 21, "nome": "Coca-Cola 2L", "preco_m": "12.00", ... } ]
}
```

### POST `/api/pedido.php`
Body JSON esperado:
```json
{
  "nome": "Maria Silva",
  "cpf": "12345678901",
  "telefone": "11987654321",
  "endereco": "Rua das Flores, 123",
  "pagamento": "Pix",
  "obs": "Sem cebola",
  "itens": [
    { "pizzaId": 1, "tamanho": "M", "quantidade": 2, "preco": 39.90 }
  ]
}
```
Retorna: `{ "sucesso": true, "numero_pedido": "TT-20260506-0001", "status": "recebido", "valor_total": "R$ 79,80" }`

### GET `/api/status.php?numero=TT-20260506-0001`
Retorna os dados completos do pedido com status atual e itens.

---

## Credenciais de acesso (ambiente local)

| O quê | Valor |
|---|---|
| Banco de dados | `pizzaria_tt` |
| Usuário MySQL | `root` (sem senha — padrão XAMPP) |
| Painel admin | `http://localhost/pizzaria/Pizzaria-main/admin/` |
| Login admin | `admin` / `admin123` (gerado pelo `setup.php`) |
| Cardápio | `http://localhost/pizzaria/Pizzaria-main/` |
