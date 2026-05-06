# Tarefa — Diogo (Integração / Suporte Full-stack)
**Projeto:** Pizzaria TT | **Tech Lead:** Eduardo Daniel

---

## Arquivos que você vai mexer

| Arquivo | O que você faz nele |
|---|---|
| `js/script.js` | Adicionar tratamento de erros nas chamadas de API |

> **Não mexa em:** `/api/`, `/admin/`, `/config/` — esses são do back-end.  
> Coordene com o Eduardo Matheus antes de alterar o `js/script.js`, pois ele também mexe nesse arquivo.

---

## O que você vai fazer

O back-end e o front-end estão prontos. Sua tarefa é **garantir que os dois se falam corretamente** e que o sistema não trava quando algo dá errado. Você vai testar, identificar problemas e corrigir a camada de comunicação entre o front-end e a API.

Antes de tudo, leia o `js/script.js` inteiro para entender como as requisições estão feitas hoje.

---

## As APIs que existem

Essas rotas já estão prontas no back-end. Você não muda elas — só as consome e testa.

| Método | URL | O que faz |
|---|---|---|
| GET | `/api/pizzas.php` | Retorna o cardápio (pizzas e bebidas) |
| POST | `/api/pedido.php` | Registra um novo pedido |
| GET | `/api/status.php?numero=TT-...` | Consulta o status de um pedido |

---

## O que você precisa garantir

### 1. O cardápio carrega sem erro
- Abra o site, F12 → aba "Network" → veja se a chamada para `/api/pizzas.php` retorna 200
- Se der erro no console falando em "CORS", reporte ao Eduardo Daniel

### 2. O pedido é enviado corretamente
- Preencha o formulário e finalize um pedido
- Verifique no DevTools (aba Network → clique na requisição para `/api/pedido.php`) se o JSON enviado está completo e correto
- A resposta deve ter `"sucesso": true` e um `numero_pedido`

### 3. Erros aparecem para o usuário (não ficam só no console)
Esses são os cenários que precisam de tratamento no `js/script.js`:

| Situação | O que deve aparecer para o usuário |
|---|---|
| Campo inválido no formulário | Mensagem de erro em português (ex: "CPF inválido") |
| Servidor offline | "Não foi possível conectar. Tente novamente." |
| Erro interno do servidor | "Ocorreu um erro. Tente novamente em instantes." |
| Pedido enviado com carrinho vazio | Não deixar enviar, avisar o usuário |

### 4. Botão de finalizar pedido não pode ser clicado duas vezes
- Enquanto o pedido está sendo enviado, o botão deve ficar desabilitado
- Depois que retornar (sucesso ou erro), habilita de volta
- Isso evita pedidos duplicados

---

## Checklist de testes

Execute cada item e anote se passou ou falhou:

**Fluxo normal:**
- [ ] Abrir o site → cardápio aparece com imagens e preços
- [ ] Adicionar pizzas e bebidas ao carrinho
- [ ] Preencher formulário com dados válidos e finalizar → receber número do pedido
- [ ] Consultar `/api/status.php?numero=TT-...` com o número recebido

**Cenários de erro:**
- [ ] Tentar finalizar com campos em branco → aparece mensagem de erro
- [ ] Colocar CPF com menos de 11 dígitos → aparece mensagem de erro
- [ ] Parar o MySQL no XAMPP e tentar finalizar → aparece mensagem de erro (não trava)
- [ ] Clicar em "Finalizar" duas vezes rápido → só envia um pedido

---

## O que entregar

- `js/script.js` atualizado com os tratamentos de erro
- `docs/TESTES.md` com o resultado do checklist acima (passou / falhou / observação)

---

## Dica

Use o [Hoppscotch](https://hoppscotch.io) para testar os endpoints da API diretamente, sem precisar passar pelo front-end. É gratuito e roda no navegador.
