<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: api/pedido.php
 * Responsável principal: Eduardo Daniel RA: 2224104694 (Back-end PHP)
 * Suporte: Eduardo Matheus RA: 2224107415 (chamada no script.js)
 * Descrição: Recebe um novo pedido via JSON, valida todos os campos,
 *            e persiste cliente + pedido + itens em uma única transação PDO.
 *            Em caso de falha em qualquer etapa, faz rollback completo.
 *
 * Método: POST
 * Content-Type: application/json
 * Body esperado:
 *   {
 *     "nome":      "João Silva",
 *     "cpf":       "12345678901",
 *     "telefone":  "11987654321",
 *     "endereco":  "Rua das Flores, 123",
 *     "pagamento": "Pix",          (Pix | Cartão | Dinheiro)
 *     "obs":       "Sem cebola",   (opcional)
 *     "itens": [
 *       { "pizza_id": 1, "tamanho": "M", "quantidade": 2, "preco_unitario": 35.00 }
 *     ]
 *   }
 * Resposta de sucesso:
 *   { "sucesso": true, "numero_pedido": "TT-20250429-0001", "status": "recebido", "valor_total": "70,00" }
 */

require_once __DIR__ . '/../config/db.php';

// ── Cabeçalhos ──────────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido. Use POST.']);
    exit;
}

// ── Leitura do corpo JSON ────────────────────────────────────────────────────
// fetch() envia Content-Type: application/json, então lemos php://input
$corpo = file_get_contents('php://input');
$dados = json_decode($corpo, true);

if (!is_array($dados)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Corpo da requisição inválido. Envie JSON.']);
    exit;
}

// ── Validação server-side ────────────────────────────────────────────────────
// Nunca confie apenas na validação do front-end — o servidor sempre valida

$erros = [];

// Sanitiza e extrai campos do JSON recebido
$nome      = trim($dados['nome']      ?? '');
$cpf       = preg_replace('/\D/', '', $dados['cpf'] ?? '');   // Remove tudo que não for dígito
$telefone  = trim($dados['telefone']  ?? '');
$endereco  = trim($dados['endereco']  ?? '');
$pagamento = trim($dados['pagamento'] ?? '');
$obs       = trim($dados['obs']       ?? '');
$itens     = $dados['itens']          ?? [];

if (mb_strlen($nome) < 3) {
    $erros[] = 'Nome deve ter pelo menos 3 caracteres.';
}
if (strlen($cpf) !== 11 || !ctype_digit($cpf)) {
    $erros[] = 'CPF inválido. Informe os 11 dígitos numéricos.';
}
if (mb_strlen($telefone) < 10) {
    $erros[] = 'Telefone inválido. Informe DDD + número.';
}
if (mb_strlen($endereco) < 5) {
    $erros[] = 'Endereço muito curto.';
}
if (!in_array($pagamento, ['Pix', 'Cartão', 'Dinheiro'], true)) {
    $erros[] = 'Forma de pagamento inválida.';
}
if (empty($itens) || !is_array($itens)) {
    $erros[] = 'O pedido deve ter pelo menos um item.';
}

if (!empty($erros)) {
    http_response_code(422);
    echo json_encode(['erro' => implode(' ', $erros)]);
    exit;
}

// Valida cada item individualmente
$tamanhosValidos = ['P', 'M', 'G', 'UN'];

foreach ($itens as $idx => $item) {
    $n = $idx + 1;
    if (empty($item['pizza_id']) || !is_numeric($item['pizza_id'])) {
        $erros[] = "Item {$n}: pizza_id inválido.";
    }
    if (empty($item['tamanho']) || !in_array($item['tamanho'], $tamanhosValidos, true)) {
        $erros[] = "Item {$n}: tamanho inválido.";
    }
    if (empty($item['quantidade']) || (int) $item['quantidade'] < 1) {
        $erros[] = "Item {$n}: quantidade inválida.";
    }
    if (!isset($item['preco_unitario']) || (float) $item['preco_unitario'] <= 0) {
        $erros[] = "Item {$n}: preço inválido.";
    }
}

if (!empty($erros)) {
    http_response_code(422);
    echo json_encode(['erro' => implode(' ', $erros)]);
    exit;
}

// ── Persistência em transação ────────────────────────────────────────────────
$pdo = obterConexao();
try {
    $pdo->beginTransaction();

    // PASSO 1: Insere o cliente
    $stmtCliente = $pdo->prepare(
        "INSERT INTO clientes (nome, cpf, telefone, endereco)
         VALUES (:nome, :cpf, :telefone, :endereco)"
    );
    $stmtCliente->execute([
        ':nome'     => $nome,
        ':cpf'      => $cpf,
        ':telefone' => $telefone,
        ':endereco' => $endereco,
    ]);
    $clienteId = (int) $pdo->lastInsertId();

    // PASSO 2: Gera número único do pedido → TT-AAAAMMDD-NNNN
    // Conta pelo prefixo do número (evita conflito de fuso PHP vs MySQL)
    $prefixo = 'TT-' . date('Ymd') . '-';
    $stmtContagem = $pdo->prepare(
        "SELECT COUNT(*) FROM pedidos WHERE numero_pedido LIKE ?"
    );
    $stmtContagem->execute([$prefixo . '%']);
    $contagem     = (int) $stmtContagem->fetchColumn();
    $numeroPedido = $prefixo . str_pad($contagem + 1, 4, '0', STR_PAD_LEFT);

    // PASSO 3: Calcula o valor total no servidor
    // Mesmo que o front envie preços, recalculamos para segurança
    $valorTotal = 0.0;
    foreach ($itens as $item) {
        $valorTotal += (float) $item['preco_unitario'] * (int) $item['quantidade'];
    }

    // PASSO 4: Insere o pedido
    $stmtPedido = $pdo->prepare(
        "INSERT INTO pedidos (numero_pedido, cliente_id, valor_total, forma_pagamento, status, obs)
         VALUES (:numero_pedido, :cliente_id, :valor_total, :pagamento, 'recebido', :obs)"
    );
    $stmtPedido->execute([
        ':numero_pedido' => $numeroPedido,
        ':cliente_id'    => $clienteId,
        ':valor_total'   => $valorTotal,
        ':pagamento'     => $pagamento,
        ':obs'           => $obs ?: null,
    ]);
    $pedidoId = (int) $pdo->lastInsertId();

    // PASSO 5: Insere cada item do pedido
    $stmtItem = $pdo->prepare(
        "INSERT INTO itens_pedido (pedido_id, pizza_id, tamanho, quantidade, preco_unitario)
         VALUES (:pedido_id, :pizza_id, :tamanho, :quantidade, :preco_unitario)"
    );
    foreach ($itens as $item) {
        $stmtItem->execute([
            ':pedido_id'      => $pedidoId,
            ':pizza_id'       => (int) $item['pizza_id'],
            ':tamanho'        => $item['tamanho'],
            ':quantidade'     => (int) $item['quantidade'],
            ':preco_unitario' => (float) $item['preco_unitario'],
        ]);
    }

    // Tudo certo — confirma a transação no banco
    $pdo->commit();

    // ── Integrações externas (stubs — implementar quando as APIs estiverem disponíveis) ──

    // TODO: Enviar confirmação de pedido via WhatsApp Business API
    // Integração: Twilio / Z-API / WPPConnect
    // enviarWhatsApp($telefone, "Olá {$nome}! Pedido {$numeroPedido} recebido. Total: R$ {$valorTotal}");

    // TODO: Ativar rastreio Glympse e enviar link de entrega por SMS
    // Integração: Glympse REST API
    // $linkRastreio = ativarGlympse($numeroPedido);
    // enviarSMS($telefone, "Acompanhe sua entrega: {$linkRastreio}");

    // TODO: Sincronizar pedido com marketplace se a origem for iFood/Uber Eats
    // Integração: iFood Partner API / Uber Eats Orders API
    // if ($dados['origem'] === 'ifood') sincronizarIFood($numeroPedido, $itens);

    echo json_encode([
        'sucesso'       => true,
        'numero_pedido' => $numeroPedido,
        'status'        => 'recebido',
        'valor_total'   => number_format($valorTotal, 2, ',', '.'),
    ]);

} catch (PDOException $e) {
    // Desfaz todas as inserções se qualquer etapa falhar
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao registrar pedido. Tente novamente em instantes.']);
}
