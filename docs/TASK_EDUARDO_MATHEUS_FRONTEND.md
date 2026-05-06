# Tarefa — Eduardo Matheus (Front-end)
**Projeto:** Pizzaria TT | **Tech Lead:** Eduardo Daniel

---

## Arquivos que você vai mexer

| Arquivo | O que você faz nele |
|---|---|
| `index.php` | Estrutura HTML da página principal (header, seções, modal) |
| `js/script.js` | Geração dos cards de pizza e lógica visual do carrinho |
| `tailwind.config.js` | Configurar as cores personalizadas da paleta |
| `styles/style.css` | Adicionar estilos globais se precisar de algo além do Tailwind |

> **Não mexa em:** `/api/`, `/admin/`, `/config/` — esses são do back-end.  
> Se precisar mudar a parte de envio do pedido no `js/script.js`, fala com o Diogo antes.

---

## O que você vai fazer

O site já funciona. Sua tarefa é **melhorar a aparência e a experiência visual** dele. Nada de reescrever do zero — você pega o que está pronto e deixa mais bonito e mais fácil de usar.

Antes de mudar qualquer coisa, leia o `index.php` e o `js/script.js` para entender o que já existe.

---

## Melhorias esperadas

### 1. Cores e identidade visual
O site usa as cores padrão do Tailwind sem muita personalidade. Melhore isso:
- Escolha uma paleta que combine com pizzaria (vermelho, laranja, creme, marrom — pesquise referências)
- Aplique as cores escolhidas no header, nos botões principais e no rodapé flutuante
- Dica: configure as cores no `tailwind.config.js` para não ficar colocando hex na mão

### 2. Cards do cardápio
Os cards que mostram as pizzas são gerados pelo JavaScript. Melhore o visual deles:
- Imagem com tamanho fixo (não pode ficar maior ou menor dependendo da foto)
- Nome e descrição bem legíveis
- O seletor de tamanho (P/M/G) precisa deixar claro qual está selecionado
- O preço deve mudar automaticamente quando trocar o tamanho
- O botão "+" deve ter um visual de hover (muda de cor quando passa o mouse)

### 3. Formulário do carrinho
Quando o usuário preenche os dados para finalizar o pedido:
- Se um campo estiver errado, mostre em vermelho com uma mensagem curta (ex: "CPF inválido")
- O botão de finalizar deve ficar desabilitado enquanto o pedido está sendo enviado (evita clicar duas vezes)

### 4. Responsividade (mobile)
A maioria dos clientes vai acessar pelo celular. Verifique:
- No Chrome, aperte F12 → clique no ícone de celular → teste em 390px de largura
- O grid de pizzas deve mostrar 1 coluna no celular e 2-3 no desktop
- O modal do carrinho deve funcionar bem no celular também

---

## O que entregar

- `index.php` e `js/script.js` com as melhorias aplicadas
- `tailwind.config.js` com a paleta de cores configurada
- Screenshots do antes e depois de pelo menos 2 melhorias
- Um parágrafo curto explicando o que você mudou e por que escolheu essas cores/estilos

---

## Referências para se inspirar

- [iFood](https://www.ifood.com.br) — observe como eles mostram os produtos
- [Tailwind CSS Docs](https://tailwindcss.com/docs) — para aprender as classes
- [Coolors.co](https://coolors.co) — para montar a paleta de cores
