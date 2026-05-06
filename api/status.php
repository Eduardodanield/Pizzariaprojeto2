<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: api/status.php
 * Responsável principal: Eduardo Daniel (Back-end PHP)
 * Suporte: Eduardo Matheus (chamada no script.js)
 * Descrição: Consulta o status atual de um pedido pelo número.
 *            O cliente pode usar para rastrear o próprio pedido.
 *
 * Método: GET
 * Parâmetro: ?numero=TT-20250429-0001
 * Resposta de sucesso:
 *   {
 *     "sucesso": true,
 *     "numero_pedido": "TT-20250429-0001",
 *     "status": "preparando",
 *     "status_label": "Em Preparo",
 *     "cliente": "João Silva",
 *     "valor_total": 70.00,
 *     "forma_pagamento": "Pix",
 *     "obs": "Sem cebola",
 *     "criado_em": "2025-04-29 19:30:00",
 *     "itens": [...]
 *   }
 */

require_once __DIR__ . '/../config/db.php';

// ── Cabeçalhos ──────────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido. Use GET.']);
    exit;
}

// ── Validação do parâmetro ───────────────────────────────────────────────────
$numero = trim($_GET['numero'] ?? '');

if (empty($numero)) {
    http_response_code(400);
    echo json_encode([
        'erro' => 'Informe o número do pedido. Exemplo: ?numero=TT-20250429-0001',
    ]);
    exit;
}

// ── Consulta ao banco ────────────────────────────────────────────────────────
try {
    $pdo = obterConexao();

    // Busca o pedido com dados do cliente via JOIN
    $stmtPedido = $pdo->prepare(
        "SELECT p.id, p.numero_pedido, p.status, p.valor_total,
                p.forma_pagamento, p.obs, p.criado_em,
                c.nome AS cliente_nome
         FROM pedidos p
         JOIN clientes c ON p.cliente_id = c.id
         WHERE p.numero_pedido = ?"
    );
    $stmtPedido->execute([$numero]);
    $pedido = $stmtPedido->fetch();

    if (!$pedido) {
        http_response_code(404);
        echo json_encode(['erro' => 'Pedido não encontrado. Verifique o número informado.']);
        exit;
    }

    // Busca os itens do pedido com nome da pizza
    $stmtItens = $pdo->prepare(
        "SELECT pz.nome AS pizza, ip.tamanho, ip.quantidade, ip.preco_unitario
         FROM itens_pedido ip
         JOIN pizzas pz ON ip.pizza_id = pz.id
         WHERE ip.pedido_id = ?
         ORDER BY ip.id ASC"
    );
    $stmtItens->execute([$pedido['id']]);
    $itens = $stmtItens->fetchAll();

    // Converte tipos numéricos para float
    foreach ($itens as &$item) {
        $item['preco_unitario'] = (float) $item['preco_unitario'];
        $item['quantidade']     = (int)   $item['quantidade'];
    }
    unset($item);

    // Mapa de status para rótulo legível em português
    $statusLabels = [
        'recebido'     => 'Pedido Recebido ✅',
        'preparando'   => 'Em Preparo 👨‍🍳',
        'saiu_entrega' => 'Saiu para Entrega 🛵',
        'entregue'     => 'Entregue 🎉',
        'cancelado'    => 'Cancelado ❌',
    ];

    echo json_encode([
        'sucesso'         => true,
        'numero_pedido'   => $pedido['numero_pedido'],
        'status'          => $pedido['status'],
        'status_label'    => $statusLabels[$pedido['status']] ?? $pedido['status'],
        'cliente'         => $pedido['cliente_nome'],
        'valor_total'     => (float) $pedido['valor_total'],
        'forma_pagamento' => $pedido['forma_pagamento'],
        'obs'             => $pedido['obs'],
        'criado_em'       => $pedido['criado_em'],
        'itens'           => $itens,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao consultar pedido.']);
}
