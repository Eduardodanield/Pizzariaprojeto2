<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: admin/index.php
 * Responsável principal: Eduardo Daniel RA: 2224104694  
 * Suporte: — Eduardo Matheus Correia Santos  RA: 2224107415 
 * Descrição: Painel administrativo principal.
 *            Lista os pedidos do dia com resumo por status,
 *            filtros por aba e botões de atualização de status via AJAX.
 *            Requer sessão admin ativa — redireciona para login se não autenticado.
 */

session_start();

// Proteção: apenas admins logados acessam o painel
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// ── Consultas ao banco ───────────────────────────────────────────────────────
try {
    $pdo = obterConexao();

    // Filtro de status selecionado via query string (?status=preparando)
    $filtroStatus    = $_GET['status'] ?? 'todos';
    $statusPermitidos = ['recebido', 'preparando', 'saiu_entrega', 'entregue', 'cancelado'];

    // Conta pedidos de hoje agrupados por status — usado nos cards de resumo
    $stmtContagem = $pdo->query(
        "SELECT status, COUNT(*) AS total
         FROM pedidos
         WHERE DATE(criado_em) = CURDATE()
         GROUP BY status"
    );
    $contagemPorStatus = [];
    foreach ($stmtContagem->fetchAll() as $row) {
        $contagemPorStatus[$row['status']] = (int) $row['total'];
    }
    $totalHoje = array_sum($contagemPorStatus);

    // Query principal: pedidos do dia com filtro opcional
    if ($filtroStatus !== 'todos' && in_array($filtroStatus, $statusPermitidos, true)) {
        $stmtPedidos = $pdo->prepare(
            "SELECT p.id, p.numero_pedido, p.valor_total, p.forma_pagamento,
                    p.status, p.obs, p.criado_em,
                    c.nome AS cliente_nome, c.telefone AS cliente_telefone
             FROM pedidos p
             JOIN clientes c ON p.cliente_id = c.id
             WHERE DATE(p.criado_em) = CURDATE() AND p.status = ?
             ORDER BY p.criado_em DESC"
        );
        $stmtPedidos->execute([$filtroStatus]);
    } else {
        $stmtPedidos = $pdo->query(
            "SELECT p.id, p.numero_pedido, p.valor_total, p.forma_pagamento,
                    p.status, p.obs, p.criado_em,
                    c.nome AS cliente_nome, c.telefone AS cliente_telefone
             FROM pedidos p
             JOIN clientes c ON p.cliente_id = c.id
             WHERE DATE(p.criado_em) = CURDATE()
             ORDER BY p.criado_em DESC"
        );
    }
    $pedidos = $stmtPedidos->fetchAll();

    // Para cada pedido, busca os itens com nome da pizza
    $stmtItens = $pdo->prepare(
        "SELECT pz.nome AS pizza, ip.tamanho, ip.quantidade, ip.preco_unitario
         FROM itens_pedido ip
         JOIN pizzas pz ON ip.pizza_id = pz.id
         WHERE ip.pedido_id = ?
         ORDER BY ip.id ASC"
    );
    foreach ($pedidos as &$pedido) {
        $stmtItens->execute([$pedido['id']]);
        $pedido['itens'] = $stmtItens->fetchAll();
    }
    unset($pedido); // Remove referência ao último elemento para não corrompê-lo

} catch (PDOException $e) {
    die("Erro ao carregar painel: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// ── Mapeamentos de status ────────────────────────────────────────────────────
$statusLabels = [
    'recebido'     => 'Recebido',
    'preparando'   => 'Preparando',
    'saiu_entrega' => 'Saiu p/ Entrega',
    'entregue'     => 'Entregue',
    'cancelado'    => 'Cancelado',
];

// Classes Tailwind para o badge colorido de cada status
$statusCores = [
    'recebido'     => 'bg-blue-100 text-blue-800 border-blue-300',
    'preparando'   => 'bg-yellow-100 text-yellow-800 border-yellow-300',
    'saiu_entrega' => 'bg-orange-100 text-orange-800 border-orange-300',
    'entregue'     => 'bg-green-100 text-green-800 border-green-300',
    'cancelado'    => 'bg-red-100 text-red-800 border-red-300',
];

// Ícones para cada status (emoji — sem dependência externa)
$statusIcones = [
    'recebido'     => '📋',
    'preparando'   => '👨‍🍳',
    'saiu_entrega' => '🛵',
    'entregue'     => '✅',
    'cancelado'    => '❌',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel — Pizzaria TT</title>
    <!-- Tailwind CDN: suficiente para o painel interno -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Animação de fade ao atualizar badge de status */
        .fade-update {
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to   { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- ── Cabeçalho ──────────────────────────────────────────────────────── -->
    <header class="bg-red-600 text-white py-4 px-6 flex justify-between items-center shadow-lg sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🍕</span>
            <div>
                <h1 class="font-bold text-lg leading-tight">Pizzaria TT</h1>
                <p class="text-xs text-red-200">Painel de Pedidos — <?= date('d/m/Y') ?></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm hidden sm:block">
                Olá, <strong><?= htmlspecialchars($_SESSION['admin_usuario'], ENT_QUOTES, 'UTF-8') ?></strong>
            </span>
            <a href="cardapio.php"
               class="bg-white text-red-600 px-3 py-1 rounded-lg text-sm font-semibold
                      hover:bg-red-50 transition">
                🍕 Cardápio
            </a>
            <a href="logout.php"
               class="bg-red-800 text-white px-3 py-1 rounded-lg text-sm font-semibold
                      hover:bg-red-900 transition">
                Sair
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">

        <!-- ── Cards de resumo ────────────────────────────────────────────── -->
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mb-6">

            <!-- Card total geral -->
            <div class="bg-white rounded-xl p-4 shadow text-center col-span-3 sm:col-span-1">
                <p class="text-3xl font-extrabold text-gray-800"><?= $totalHoje ?></p>
                <p class="text-xs text-gray-500 mt-1 font-medium">Total Hoje</p>
            </div>

            <!-- Cards por status -->
            <?php foreach ($statusLabels as $key => $label): ?>
            <div class="bg-white rounded-xl p-4 shadow text-center">
                <p class="text-2xl"><?= $statusIcones[$key] ?></p>
                <p class="text-xl font-bold text-gray-800"><?= $contagemPorStatus[$key] ?? 0 ?></p>
                <p class="text-xs text-gray-500 mt-1"><?= $label ?></p>
            </div>
            <?php endforeach; ?>

        </div>

        <!-- ── Filtros por aba de status ──────────────────────────────────── -->
        <div class="flex flex-wrap gap-2 mb-5">
            <a href="?status=todos"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition
                      <?= $filtroStatus === 'todos'
                            ? 'bg-red-600 text-white shadow'
                            : 'bg-white text-gray-600 hover:bg-gray-200 shadow-sm' ?>">
                Todos (<?= $totalHoje ?>)
            </a>
            <?php foreach ($statusLabels as $key => $label): ?>
            <a href="?status=<?= $key ?>"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition
                      <?= $filtroStatus === $key
                            ? 'bg-red-600 text-white shadow'
                            : 'bg-white text-gray-600 hover:bg-gray-200 shadow-sm' ?>">
                <?= $statusIcones[$key] ?> <?= $label ?> (<?= $contagemPorStatus[$key] ?? 0 ?>)
            </a>
            <?php endforeach; ?>
        </div>

        <!-- ── Lista de pedidos ───────────────────────────────────────────── -->
        <?php if (empty($pedidos)): ?>
            <div class="bg-white rounded-xl p-10 text-center text-gray-400 shadow">
                <p class="text-4xl mb-3">📭</p>
                <p class="font-semibold">Nenhum pedido encontrado para este filtro.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($pedidos as $pedido): ?>

                <div class="bg-white rounded-xl shadow p-5" id="card-pedido-<?= $pedido['id'] ?>">

                    <!-- Linha 1: número, cliente, hora e valor -->
                    <div class="flex flex-wrap justify-between items-start gap-2">
                        <div>
                            <p class="font-extrabold text-lg text-gray-800">
                                <?= htmlspecialchars($pedido['numero_pedido'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="text-gray-600 text-sm mt-0.5">
                                <?= htmlspecialchars($pedido['cliente_nome'], ENT_QUOTES, 'UTF-8') ?>
                                —
                                <?= htmlspecialchars($pedido['cliente_telefone'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="text-gray-400 text-xs mt-0.5">
                                🕐 <?= date('H:i', strtotime($pedido['criado_em'])) ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-extrabold text-green-600 text-xl">
                                R$ <?= number_format((float)$pedido['valor_total'], 2, ',', '.') ?>
                            </p>
                            <p class="text-gray-500 text-sm"><?= htmlspecialchars($pedido['forma_pagamento'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                    <!-- Linha 2: itens do pedido como tags -->
                    <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-1">
                        <?php foreach ($pedido['itens'] as $item): ?>
                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">
                                <?= htmlspecialchars($item['pizza'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($item['tamanho'] !== 'UN'): ?>
                                    (<?= $item['tamanho'] ?>)
                                <?php endif; ?>
                                × <?= (int)$item['quantidade'] ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <!-- Observações (se houver) -->
                    <?php if (!empty($pedido['obs'])): ?>
                        <p class="text-xs text-amber-700 bg-amber-50 rounded px-2 py-1 mt-2 italic">
                            📝 <?= htmlspecialchars($pedido['obs'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>

                    <!-- Linha 3: badge de status atual + controle de atualização -->
                    <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-3">

                        <!-- Badge dinâmico — atualizado pelo JS após AJAX -->
                        <span id="badge-<?= $pedido['id'] ?>"
                              class="px-3 py-1 rounded-full text-xs font-bold border
                                     <?= $statusCores[$pedido['status']] ?? 'bg-gray-100 text-gray-700' ?>">
                            <?= $statusIcones[$pedido['status']] ?? '' ?>
                            <?= $statusLabels[$pedido['status']] ?? $pedido['status'] ?>
                        </span>

                        <!-- Seletor de novo status -->
                        <select id="select-<?= $pedido['id'] ?>"
                                class="border border-gray-300 rounded-lg px-3 py-1 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-red-400">
                            <?php foreach ($statusLabels as $key => $label): ?>
                                <option value="<?= $key ?>"
                                        <?= $pedido['status'] === $key ? 'selected' : '' ?>>
                                    <?= $statusIcones[$key] ?> <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Botão de confirmação — chama atualizarStatus() via JS -->
                        <button
                            onclick="atualizarStatus(<?= $pedido['id'] ?>)"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-1 rounded-lg
                                   text-sm font-semibold transition">
                            Atualizar
                        </button>

                    </div>
                </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main><!-- ── JavaScript: atualização de status via AJAX ────────────────────────── -->
<script>
    /**
     * Mapas PHP exportados para JS — evita duplicar as strings de status
     * Os dados são embutidos pelo PHP no momento da renderização da página.
     */
    const STATUS_LABELS = <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE) ?>;
    const STATUS_CORES  = <?= json_encode($statusCores,  JSON_UNESCAPED_UNICODE) ?>;
    const STATUS_ICONES = <?= json_encode($statusIcones, JSON_UNESCAPED_UNICODE) ?>;

    /**
     * Envia requisição AJAX para atualizar o status de um pedido.
     * Atualiza o badge visualmente sem recarregar a página.
     *
     * @param {number} pedidoId - ID numérico do pedido no banco
     */
    async function atualizarStatus(pedidoId) {
        const select     = document.getElementById('select-' + pedidoId);
        const novoStatus = select.value;

        // Desabilita o botão durante a requisição para evitar duplo clique
        const btn = select.nextElementSibling;
        btn.disabled   = true;
        btn.textContent = 'Salvando...';

        try {
            const resposta = await fetch('atualizar_status.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ pedido_id: pedidoId, novo_status: novoStatus }),
            });

            const dados = await resposta.json();

            if (dados.sucesso) {
                // Atualiza o badge de status com as novas cores e label
                const badge = document.getElementById('badge-' + pedidoId);
                badge.className = 'px-3 py-1 rounded-full text-xs font-bold border '
                                + STATUS_CORES[novoStatus];
                badge.textContent = STATUS_ICONES[novoStatus] + ' ' + STATUS_LABELS[novoStatus];
                badge.classList.add('fade-update');
                setTimeout(() => badge.classList.remove('fade-update'), 500);
            } else {
                alert('Erro: ' + dados.erro);
            }

        } catch (e) {
            alert('Falha na conexão. Verifique se o XAMPP está rodando.');
        } finally {
            // Reabilita o botão independentemente do resultado
            btn.disabled    = false;
            btn.textContent = 'Atualizar';
        }
    }

    // Auto-refresh da página a cada 60 segundos para capturar novos pedidos
    // O usuário pode cancelar clicando em qualquer filtro (que força um reload)
    let autoRefreshTimer = setTimeout(() => location.reload(), 60000);
    document.querySelectorAll('a[href*="status="]').forEach(link => {
        link.addEventListener('click', () => clearTimeout(autoRefreshTimer));
    });
</script>

</body>
</html>
