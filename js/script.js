/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: script.js
 * Responsável principal: Eduardo Matheus (Front-end)
 * Suporte: Eduardo Daniel (integração com a API PHP)
 * Descrição: Lógica completa do cardápio dinâmico, carrinho de compras
 *            e checkout integrado ao back-end PHP via fetch().
 */

// ═══════════════════════════════════════════════════════════════════════════
// SELEÇÃO DE ELEMENTOS DO DOM
// ═══════════════════════════════════════════════════════════════════════════

const menuPizzasContainer = document.getElementById('menu-pizzas');
const menuBebidasContainer = document.getElementById('menu-bebidas');
const cartBtn             = document.getElementById('cart-btn');
const cartModal           = document.getElementById('cart-modal');
const cartItemsContainer  = document.getElementById('cart-items');
const cartTotal           = document.getElementById('cart-total');
const checkoutBtn         = document.getElementById('checkout-btn');
const closeModalBtn       = document.getElementById('close-modal-btn');
const cartCounter         = document.getElementById('cart-count');

// Campos do formulário de checkout
const nameInput    = document.getElementById('name');
const cpfInput     = document.getElementById('cpf');
const phoneInput   = document.getElementById('phone');
const addressInput = document.getElementById('address');
const paymentInput = document.getElementById('payment');
const obsInput     = document.getElementById('obs');

// Avisos de validação
const nameWarn    = document.getElementById('name-warn');
const cpfWarn     = document.getElementById('cpf-warn');
const phoneWarn   = document.getElementById('phone-warn');
const addressWarn = document.getElementById('address-warn');


// ═══════════════════════════════════════════════════════════════════════════
// ESTADO DO CARRINHO
// Cada item: { key, pizzaId, nome, tamanho, preco, quantity }
// A key combina pizzaId + tamanho para diferenciar a mesma pizza em tamanhos diferentes
// ═══════════════════════════════════════════════════════════════════════════

let cart = [];


// ═══════════════════════════════════════════════════════════════════════════
// CARREGAMENTO DO CARDÁPIO VIA API
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Busca o cardápio no back-end PHP e renderiza os itens na página.
 * Chamada automaticamente ao carregar o DOM (DOMContentLoaded).
 */
async function carregarCardapio() {
    try {
        const resposta = await fetch('/pizzaria/Pizzaria-main/api/pizzas.php');

        if (!resposta.ok) {
            throw new Error(`HTTP ${resposta.status}`);
        }

        const dados = await resposta.json();

        if (!dados.sucesso) {
            throw new Error(dados.erro || 'Erro desconhecido.');
        }

        // Remove a mensagem "Carregando cardápio..."
        const loadingMsg = document.getElementById('loading-msg');
        if (loadingMsg) loadingMsg.remove();

        // Renderiza as pizzas e bebidas separadamente
        renderizarItens(dados.pizzas,  menuPizzasContainer,  'pizza');
        renderizarItens(dados.bebidas, menuBebidasContainer, 'bebida');

    } catch (erro) {
        // Exibe mensagem de erro no lugar do cardápio
        menuPizzasContainer.innerHTML = `
            <p class="col-span-2 text-center text-red-500 py-12">
                ⚠️ Não foi possível carregar o cardápio.<br>
                Verifique se o XAMPP está rodando e se o banco foi importado.<br>
                <small class="text-gray-400">${erro.message}</small>
            </p>`;
    }
}

/**
 * Renderiza uma lista de itens (pizzas ou bebidas) no container especificado.
 *
 * @param {Array}       itens      - Array de objetos vindos da API
 * @param {HTMLElement} container  - Elemento onde os itens serão inseridos
 * @param {string}      categoria  - 'pizza' ou 'bebida'
 */
function renderizarItens(itens, container, categoria) {
    if (!itens || itens.length === 0) return;

    itens.forEach(item => {
        const elemento = criarElementoItem(item, categoria);
        container.appendChild(elemento);
    });
}

/**
 * Cria e retorna o elemento HTML de um item do cardápio.
 * Mantém exatamente a mesma estrutura visual do HTML original.
 * Pizzas ganham um seletor de tamanho P/M/G; bebidas não.
 *
 * @param   {Object} item      - Dados do item vindos da API
 * @param   {string} categoria - 'pizza' ou 'bebida'
 * @returns {HTMLDivElement}   - Elemento pronto para inserção no DOM
 */
function criarElementoItem(item, categoria) {
    const div = document.createElement('div');
    div.className = 'flex gap-2';

    const ehPizza = categoria === 'pizza';

    // Preço inicial exibido: M para pizzas (selecionado por padrão), único para bebidas
    const precoInicial = parseFloat(item.preco_m);

    // Seletor de tamanho P/M/G — só aparece para pizzas
    const seletorTamanho = ehPizza ? `
        <div class="flex items-center gap-1 mt-2">
            <select class="tamanho-select border border-gray-300 rounded text-sm px-1 py-0.5"
                    data-preco-p="${item.preco_p}"
                    data-preco-m="${item.preco_m}"
                    data-preco-g="${item.preco_g}">
                <option value="P">P — R$ ${parseFloat(item.preco_p).toFixed(2)}</option>
                <option value="M" selected>M — R$ ${parseFloat(item.preco_m).toFixed(2)}</option>
                <option value="G">G — R$ ${parseFloat(item.preco_g).toFixed(2)}</option>
            </select>
        </div>` : '';

    // Nome do arquivo de imagem com espaços encodados para URL válida
    const imagemUrl = './assets/' + encodeURIComponent(item.imagem);

    div.innerHTML = `
        <img
            src="${imagemUrl}"
            alt="${item.nome}"
            class="w-28 h-28 rounded-md hover:scale-110 hover:-rotate-2 duration-300 object-contain"
            onerror="this.src='./assets/Logo_Pizzaria.jpg'"
        />
        <div>
            <p class="font-bold">${item.nome}</p>
            <p class="text-sm">${item.descricao}</p>
            ${seletorTamanho}
            <div class="flex items-center gap-2 mt-3">
                <p class="font-bold text-lg preco-display">R$ ${precoInicial.toFixed(2)}</p>
                <button
                    class="bg-gray-900 px-5 rounded add-to-cart-btn"
                    data-id="${item.id}"
                    data-name="${item.nome}"
                    data-categoria="${categoria}"
                    data-preco-p="${item.preco_p || item.preco_m}"
                    data-preco-m="${item.preco_m}"
                    data-preco-g="${item.preco_g || item.preco_m}"
                >
                    <i class="fa fa-cart-plus text-lg text-white"></i>
                </button>
            </div>
        </div>`;

    // Atualiza o preço exibido quando o usuário muda o tamanho
    if (ehPizza) {
        const select      = div.querySelector('.tamanho-select');
        const precoDisplay = div.querySelector('.preco-display');

        select.addEventListener('change', () => {
            const t     = select.value;
            const preco = t === 'P' ? item.preco_p
                        : t === 'G' ? item.preco_g
                        : item.preco_m;
            precoDisplay.textContent = `R$ ${parseFloat(preco).toFixed(2)}`;
        });
    }

    return div;
}


// ═══════════════════════════════════════════════════════════════════════════
// CARRINHO — ADICIONAR / REMOVER / RENDERIZAR
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Adiciona um item ao carrinho ou incrementa sua quantidade se já existir.
 * A chave única é pizzaId + tamanho, permitindo a mesma pizza em tamanhos diferentes.
 *
 * @param {number} pizzaId   - ID da pizza no banco
 * @param {string} nome      - Nome do item
 * @param {string} tamanho   - 'P', 'M', 'G' ou 'UN' (bebidas)
 * @param {number} preco     - Preço unitário para o tamanho selecionado
 */
function addToCart(pizzaId, nome, tamanho, preco) {
    const chave = `${pizzaId}-${tamanho}`;
    const itemExistente = cart.find(i => i.key === chave);

    if (itemExistente) {
        itemExistente.quantity += 1;
    } else {
        cart.push({
            key:      chave,
            pizzaId:  pizzaId,
            nome:     nome,
            tamanho:  tamanho,
            preco:    preco,
            quantity: 1,
        });
    }

    atualizarModalCarrinho();

    // Feedback visual rápido
    Toastify({
        text: `${nome} (${tamanho}) adicionado! 🛒`,
        duration: 1500,
        gravity: 'bottom',
        position: 'right',
        style: { background: '#1f2937' },
    }).showToast();
}

/**
 * Remove uma unidade do item do carrinho. Se a quantidade chegar a 0, remove o item.
 *
 * @param {string} chave - Chave única do item (pizzaId-tamanho)
 */
function removeItemCart(chave) {
    const index = cart.findIndex(i => i.key === chave);
    if (index === -1) return;

    if (cart[index].quantity > 1) {
        cart[index].quantity -= 1;
    } else {
        cart.splice(index, 1);
    }

    atualizarModalCarrinho();
}

/**
 * Reconstrói o HTML do modal do carrinho com os itens atuais
 * e recalcula o total em tempo real.
 */
function atualizarModalCarrinho() {
    cartItemsContainer.innerHTML = '';
    let total = 0;

    cart.forEach(item => {
        const subtotal = item.preco * item.quantity;
        total += subtotal;

        const div = document.createElement('div');
        div.classList.add('flex', 'justify-between', 'mb-4', 'flex-col');
        div.innerHTML = `
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium">${item.nome}
                        ${item.tamanho !== 'UN' ? `<span class="text-xs text-gray-500">(${item.tamanho})</span>` : ''}
                    </p>
                    <p class="text-sm text-gray-500">Qtd: ${item.quantity} × R$ ${item.preco.toFixed(2)}</p>
                    <p class="font-medium mt-1">R$ ${subtotal.toFixed(2)}</p>
                </div>
                <button class="remove-from-cart-btn text-red-500 hover:text-red-700 text-sm"
                        data-key="${item.key}">
                    Remover
                </button>
            </div>`;
        cartItemsContainer.appendChild(div);
    });

    // Formata o total no padrão brasileiro: R$ 1.234,56
    cartTotal.textContent = total.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });

    // Atualiza o contador do botão flutuante no rodapé
    const totalItens = cart.reduce((acc, i) => acc + i.quantity, 0);
    cartCounter.textContent = totalItens;
}


// ═══════════════════════════════════════════════════════════════════════════
// EVENTOS — CLIQUES NOS MENUS E CARRINHO
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Delegação de eventos: captura clique em qualquer botão "add-to-cart-btn"
 * dentro dos dois containers de menu (pizzas e bebidas).
 * Lê o tamanho selecionado no <select> vizinho (se houver).
 */
[menuPizzasContainer, menuBebidasContainer].forEach(container => {
    container.addEventListener('click', function (event) {
        const btn = event.target.closest('.add-to-cart-btn');
        if (!btn) return;

        const pizzaId   = parseInt(btn.getAttribute('data-id'));
        const nome      = btn.getAttribute('data-name');
        const categoria = btn.getAttribute('data-categoria');

        // Tenta ler o seletor de tamanho do mesmo bloco do botão
        const blocoItem = btn.closest('div > div') || btn.closest('div');
        const select    = blocoItem ? blocoItem.querySelector('.tamanho-select') : null;

        let tamanho, preco;

        if (select && categoria === 'pizza') {
            // Pizza: usa o tamanho selecionado e o preço correspondente
            tamanho = select.value;
            preco = tamanho === 'P' ? parseFloat(btn.getAttribute('data-preco-p'))
                  : tamanho === 'G' ? parseFloat(btn.getAttribute('data-preco-g'))
                  : parseFloat(btn.getAttribute('data-preco-m'));
        } else {
            // Bebida: tamanho único (UN) com preço M
            tamanho = 'UN';
            preco   = parseFloat(btn.getAttribute('data-preco-m'));
        }

        addToCart(pizzaId, nome, tamanho, preco);
    });
});

// Evento de remoção de item dentro do modal do carrinho
cartItemsContainer.addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-from-cart-btn')) {
        const chave = event.target.getAttribute('data-key');
        removeItemCart(chave);
    }
});


// ═══════════════════════════════════════════════════════════════════════════
// EVENTOS — ABRIR / FECHAR MODAL DO CARRINHO
// ═══════════════════════════════════════════════════════════════════════════

cartBtn.addEventListener('click', function () {
    atualizarModalCarrinho();
    cartModal.style.display = 'flex';
});

// Clique fora do modal (no overlay escuro) fecha o modal
cartModal.addEventListener('click', function (event) {
    if (event.target === cartModal) {
        cartModal.style.display = 'none';
    }
});

closeModalBtn.addEventListener('click', function () {
    cartModal.style.display = 'none';
});


// ═══════════════════════════════════════════════════════════════════════════
// VALIDAÇÃO DOS CAMPOS DO CHECKOUT
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Remove a marcação de erro de um campo quando o usuário começa a digitar.
 * Melhora a experiência: o erro some imediatamente, sem precisar submeter de novo.
 */
function limparErro(input, warn) {
    input.classList.remove('border-red-500');
    warn.classList.add('hidden');
    input.addEventListener('input', () => {
        input.classList.remove('border-red-500');
        warn.classList.add('hidden');
    }, { once: true });
}

/**
 * Marca um campo como inválido visualmente.
 *
 * @param {HTMLElement} input - Campo de texto
 * @param {HTMLElement} warn  - Parágrafo de aviso correspondente
 */
function marcarErro(input, warn) {
    input.classList.add('border-red-500');
    warn.classList.remove('hidden');
    input.focus();
}

/**
 * Valida todos os campos do formulário de checkout.
 * Retorna true se tudo estiver válido, false caso contrário.
 *
 * @returns {boolean}
 */
function validarFormulario() {
    let valido = true;

    // Nome: mínimo 3 caracteres
    if (nameInput.value.trim().length < 3) {
        marcarErro(nameInput, nameWarn);
        valido = false;
    }

    // CPF: apenas dígitos, exatamente 11
    const cpfLimpo = cpfInput.value.replace(/\D/g, '');
    if (cpfLimpo.length !== 11) {
        marcarErro(cpfInput, cpfWarn);
        if (valido) cpfInput.focus();
        valido = false;
    }

    // Telefone: mínimo 10 dígitos
    const telLimpo = phoneInput.value.replace(/\D/g, '');
    if (telLimpo.length < 10) {
        marcarErro(phoneInput, phoneWarn);
        if (valido) phoneInput.focus();
        valido = false;
    }

    // Endereço: mínimo 5 caracteres
    if (addressInput.value.trim().length < 5) {
        marcarErro(addressInput, addressWarn);
        if (valido) addressInput.focus();
        valido = false;
    }

    return valido;
}


// ═══════════════════════════════════════════════════════════════════════════
// CHECKOUT — ENVIO DO PEDIDO PARA A API PHP
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Finaliza o pedido:
 *   1. Valida campos do formulário
 *   2. Monta o payload JSON
 *   3. Envia para POST /api/pedido.php via fetch()
 *   4. Exibe número do pedido ao cliente
 *   5. Limpa o carrinho
 */
checkoutBtn.addEventListener('click', async function () {

    // Impede envio se o carrinho estiver vazio
    if (cart.length === 0) {
        Toastify({
            text: 'Seu carrinho está vazio! Adicione itens primeiro. 🍕',
            duration: 3000,
            gravity: 'top',
            position: 'center',
            style: { background: '#ef4444' },
        }).showToast();
        return;
    }

    // Valida todos os campos antes de enviar
    if (!validarFormulario()) return;

    // Monta o corpo da requisição no formato esperado pelo api/pedido.php
    const payload = {
        nome:      nameInput.value.trim(),
        cpf:       cpfInput.value.replace(/\D/g, ''),
        telefone:  phoneInput.value.trim(),
        endereco:  addressInput.value.trim(),
        pagamento: paymentInput.value,
        obs:       obsInput.value.trim(),
        itens:     cart.map(item => ({
            pizza_id:       item.pizzaId,
            tamanho:        item.tamanho,
            quantidade:     item.quantity,
            preco_unitario: item.preco,
        })),
    };

    // Desabilita o botão durante o envio para evitar duplo clique
    checkoutBtn.disabled    = true;
    checkoutBtn.textContent = 'Enviando...';

    try {
        const resposta = await fetch('/pizzaria/Pizzaria-main/api/pedido.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });

        const dados = await resposta.json();

        if (dados.sucesso) {
            // ── Pedido registrado com sucesso ────────────────────────────
            const numeroPedido = dados.numero_pedido;
            const valorTotal   = dados.valor_total;

            // Notificação de sucesso com o número do pedido
            Toastify({
                text: `✅ Pedido ${numeroPedido} recebido! Total: R$ ${valorTotal}`,
                duration: 8000,
                gravity: 'top',
                position: 'center',
                style: { background: '#16a34a', fontSize: '1rem', padding: '14px 20px' },
            }).showToast();

            // Limpa o carrinho e fecha o modal
            cart = [];
            atualizarModalCarrinho();
            cartModal.style.display = 'none';

            // Limpa os campos do formulário para próximo uso
            [nameInput, cpfInput, phoneInput, addressInput, obsInput].forEach(f => f.value = '');

            // ── Stub: integração WhatsApp ────────────────────────────────
            // TODO: O back-end (api/pedido.php) enviará confirmação via WhatsApp API.
            // Para teste manual, descomente a linha abaixo:
            // const msg = encodeURIComponent(`Pedido ${numeroPedido} confirmado! Total: R$ ${valorTotal}`);
            // window.open(`https://wa.me/5511981373769?text=${msg}`, '_blank');

        } else {
            // Erro retornado pela API (validação server-side)
            Toastify({
                text: `⚠️ ${dados.erro}`,
                duration: 5000,
                gravity: 'top',
                position: 'center',
                style: { background: '#ef4444' },
            }).showToast();
        }

    } catch (erro) {
        // Erro de rede ou XAMPP offline
        Toastify({
            text: '❌ Falha ao conectar. Verifique se o XAMPP está rodando.',
            duration: 5000,
            gravity: 'top',
            position: 'center',
            style: { background: '#ef4444' },
        }).showToast();
    } finally {
        // Reabilita o botão independentemente do resultado
        checkoutBtn.disabled    = false;
        checkoutBtn.textContent = 'Finalizar pedido';
    }
});


// ═══════════════════════════════════════════════════════════════════════════
// HORÁRIO DE FUNCIONAMENTO
// Verifica se o restaurante está aberto (18h às 01h) e
// aplica a cor correta ao indicador no header.
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Retorna true se o horário atual estiver dentro do funcionamento (18h–01h).
 * O cruzamento da meia-noite exige verificação separada para cada metade.
 *
 * @returns {boolean}
 */
function checkRestaurantOpen() {
    const hora = new Date().getHours();
    // Aberto das 18:00 até às 00:59 (inclui a madrugada)
    return hora >= 18 || hora < 1;
}

// Aplica cor verde (aberto) ou vermelha (fechado) ao badge do header
const dateSpan = document.getElementById('date-span');
if (dateSpan) {
    if (checkRestaurantOpen()) {
        dateSpan.classList.add('bg-green-600');
        dateSpan.classList.remove('bg-red-500');
    } else {
        dateSpan.classList.add('bg-red-500');
        dateSpan.classList.remove('bg-green-600');
    }
}


// ═══════════════════════════════════════════════════════════════════════════
// INICIALIZAÇÃO — carrega o cardápio ao abrir a página
// ═══════════════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', carregarCardapio);
