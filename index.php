<!--
  Pizzaria TT — Projeto de Extensão
  Arquivo: index.php
  Responsável principal: Eduardo Matheus (Front-end)
  Suporte: Diogo Neves
  Descrição: Página principal — cardápio dinâmico carregado via fetch(/api/pizzas.php)
             e fluxo completo de pedido integrado ao back-end PHP.
-->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=1.0">

    <!-- Toastify: notificações de sucesso/erro no checkout -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css"/>

    <!-- Google Fonts: Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"/>

    <!-- Font Awesome: ícones do carrinho -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
          integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- META OG para compartilhamento em redes sociais -->
    <meta property="og:type"        content="website"/>
    <meta property="og:title"       content="Pizzaria TT"/>
    <meta property="og:description" content="A melhor pizzaria da região — faça seu pedido online!"/>
    <meta property="og:site_name"   content="Pizzaria TT"/>

    <!-- CSS compilado do Tailwind (gerado por: npm run dev) -->
    <link rel="stylesheet" href="./styles/output.css"/>
    <title>PIZZARIA TT</title>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════════════════
     HEADER — foto, nome, Instagram e indicador de horário de funcionamento
     ═══════════════════════════════════════════════════════════════════════════ -->
<header class="w-full h-[420px] bg-zinc-900 bg-home bg-cover bg-center">
    <div class="w-full h-full flex flex-col justify-center items-center">
        <img
            src="./assets/Logo_Pizzaria.jpg"
            alt="Logo Pizzaria TT"
            class="w-32 h-32 rounded-full shadow-lg hover:scale-110 duration-200"
        />
        <div class="flex items-center gap-4 mt-4">
            <h1 class="text-4xl font-bold italic text-white">Pizzaria TT</h1>
            <a
                href="https://www.instagram.com/pizzaria_do_cheeffe/"
                target="_blank"
                class="text-white text-5xl"
                title="Visite nosso Instagram"
            >
                <i class="fab fa-instagram"></i>
            </a>
        </div>
        <span class="text-white font-medium">Rua Santo Amaro, nº 199 — São Paulo-SP</span>

        <!-- Indicador de aberto/fechado — cor aplicada dinamicamente pelo js/script.js -->
        <div class="px-4 py-1 rounded-lg mt-5" id="date-span">
            <span class="text-white font-medium">Terça a Dom — 18:00 às 01:00</span>
        </div>
    </div>
</header>
<!-- ═══════════════════════════════════════════════════════════════════════════
     FIM DO HEADER
     ═══════════════════════════════════════════════════════════════════════════ -->


<!-- Título do cardápio -->
<h2 class="text-5xl md:text-6xl font-bold text-center mt-12 mb-8 italic text-red-600
           drop-shadow-xl tracking-wide hover:scale-105 hover:translate-y-[-2px]
           transition-transform duration-300 ease-in-out">
    Conheça nosso menu
</h2>


<!-- ═══════════════════════════════════════════════════════════════════════════
     MENU PRINCIPAL — conteúdo gerado dinamicamente pelo js/script.js
     via GET /api/pizzas.php. Os containers abaixo são preenchidos via JS.
     ═══════════════════════════════════════════════════════════════════════════ -->
<div id="menu">

    <!-- Container das pizzas — preenchido por renderizarItens() em js/script.js -->
    <main id="menu-pizzas"
          class="grid grid-cols-1 md:grid-cols-2 gap-7 md:gap-10 mx-auto max-w-7xl px-2 mb-16">
        <!-- Estado de carregamento substituído após o fetch() -->
        <p id="loading-msg" class="col-span-2 text-center text-gray-400 py-12 text-lg">
            Carregando cardápio... 🍕
        </p>
    </main>

    <!-- Título das bebidas -->
    <div class="mx-auto max-w-7xl px-2 my-2">
        <h2 class="font-bold text-3xl">Bebidas</h2>
    </div>

    <!-- Container das bebidas — preenchido por renderizarItens() em js/script.js -->
    <div id="menu-bebidas"
         class="grid grid-cols-1 md:grid-cols-2 gap-7 md:gap-10 mx-auto max-w-7xl px-2 mb-16">
    </div>

</div>
<!-- ═══════════════════════════════════════════════════════════════════════════
     FIM DO MENU
     ═══════════════════════════════════════════════════════════════════════════ -->


<!-- ═══════════════════════════════════════════════════════════════════════════
     MODAL DO CARRINHO
     ═══════════════════════════════════════════════════════════════════════════ -->
<div
    class="bg-black/60 w-full h-full fixed top-0 left-0 z-[99] items-center justify-center hidden"
    id="cart-modal"
>
    <div class="bg-white p-5 rounded-md min-w-[90%] md:min-w-[600px] max-h-[90vh] overflow-y-auto">
        <h2 class="text-center font-bold text-2xl mb-2">Meu carrinho</h2>

        <!-- Itens do carrinho — preenchidos dinamicamente por js/script.js -->
        <div id="cart-items" class="flex justify-between mb-2 flex-col"></div>

        <!-- Total calculado em tempo real -->
        <p class="font-bold">Total: <span id="cart-total">R$ 0,00</span></p>

        <!-- ── Formulário de checkout ──────────────────────────────────── -->

        <!-- Nome completo -->
        <p class="font-bold mt-4">Nome completo:</p>
        <input
            type="text"
            placeholder="Digite seu nome completo..."
            id="name"
            class="w-full border-2 p-1 rounded my-1"
        />
        <p class="text-red-500 text-sm hidden" id="name-warn">Digite seu nome completo.</p>

        <!-- CPF -->
        <p class="font-bold mt-4">CPF:</p>
        <input
            type="text"
            placeholder="Somente números: 12345678901"
            id="cpf"
            maxlength="14"
            class="w-full border-2 p-1 rounded my-1"
        />
        <p class="text-red-500 text-sm hidden" id="cpf-warn">CPF inválido. Digite os 11 dígitos.</p>

        <!-- Telefone -->
        <p class="font-bold mt-4">Telefone (com DDD):</p>
        <input
            type="text"
            placeholder="(11) 99999-9999"
            id="phone"
            class="w-full border-2 p-1 rounded my-1"
        />
        <p class="text-red-500 text-sm hidden" id="phone-warn">Telefone inválido.</p>

        <!-- Endereço de entrega -->
        <p class="font-bold mt-4">Endereço de entrega:</p>
        <input
            type="text"
            placeholder="Rua, número, bairro..."
            id="address"
            class="w-full border-2 p-1 rounded my-1"
        />
        <p class="text-red-500 text-sm hidden" id="address-warn">Digite seu endereço completo.</p>

        <!-- Forma de pagamento -->
        <p class="font-bold mt-4">Forma de pagamento:</p>
        <select id="payment" class="w-full border-2 p-1 rounded my-1">
            <option value="Pix">Pix</option>
            <option value="Cartão">Cartão</option>
            <option value="Dinheiro">Dinheiro</option>
        </select>

        <!-- Observações (salvo no banco via api/pedido.php) -->
        <p class="font-bold mt-4">Observações (opcional):</p>
        <textarea
            id="obs"
            placeholder="Ex: sem cebola, troco para R$ 50, campainha não funciona..."
            class="w-full border-2 p-1 rounded my-1 h-16 resize-none text-sm"
        ></textarea>

        <!-- Botões de ação -->
        <div class="flex items-center justify-between mt-5 w-full">
            <button id="close-modal-btn" class="text-gray-500 hover:text-gray-700">Fechar</button>
            <button
                id="checkout-btn"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded font-semibold transition"
            >
                Finalizar pedido
            </button>
        </div>
    </div>
</div>
<!-- ═══════════════════════════════════════════════════════════════════════════
     FIM DO MODAL
     ═══════════════════════════════════════════════════════════════════════════ -->


<!-- ═══════════════════════════════════════════════════════════════════════════
     RODAPÉ FLUTUANTE — botão do carrinho sempre visível
     ═══════════════════════════════════════════════════════════════════════════ -->
<footer class="w-full bg-red-500 py-4 fixed bottom-0 z-40 flex items-center justify-center">
    <button class="flex items-center gap-2 text-white font-bold" id="cart-btn">
        (<span id="cart-count">0</span>)
        Veja meu carrinho
        <i class="fa fa-cart-plus text-lg text-white"></i>
    </button>
</footer>


<!-- Scripts -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="./js/script.js"></script>

</body>
</html>
