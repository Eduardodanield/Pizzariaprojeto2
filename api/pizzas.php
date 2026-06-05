<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: api/pizzas.php
 * Responsável principal: Eduardo Daniel RA: 2224104694 (Back-end PHP)
 * Suporte: Eduardo Matheus RA: 2224107415 (integração no script.js)
 * Descrição: Retorna o cardápio completo (pizzas ativas + bebidas) em JSON.
 *            Chamado pelo script.js na inicialização da página.
 *
 * Método: GET
 * URL: /api/pizzas.php
 * Resposta: { "sucesso": true, "pizzas": [...], "bebidas": [...] }
 */

require_once __DIR__ . '/../config/db.php';

// ── Cabeçalhos: JSON + CORS para desenvolvimento local ─────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight OPTIONS enviado pelo navegador antes de requisições cross-origin
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Apenas GET é aceito neste endpoint
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido. Use GET.']);
    exit;
}

// ── Consulta ao banco ────────────────────────────────────────────────────────
try {
    $pdo = obterConexao();

    /**
     * Busca todos os itens ativos, ordenados por categoria e nome.
     * O front-end usa 'categoria' para decidir se renderiza seletor P/M/G.
     */
    $stmt = $pdo->query(
        "SELECT id, nome, descricao, preco_p, preco_m, preco_g, imagem, categoria
         FROM pizzas
         WHERE ativa = 1
         ORDER BY categoria ASC, nome ASC"
    );

    $itens = $stmt->fetchAll();

    // Separa pizzas de bebidas — facilita a renderização no script.js
    $pizzas  = [];
    $bebidas = [];

    foreach ($itens as $item) {
        // PDO retorna strings; converte para float para evitar
        // problemas de tipo no JSON (ex: "35.00" vs 35.00)
        $item['preco_p'] = $item['preco_p'] !== null ? (float) $item['preco_p'] : null;
        $item['preco_m'] = (float) $item['preco_m'];
        $item['preco_g'] = $item['preco_g'] !== null ? (float) $item['preco_g'] : null;

        if ($item['categoria'] === 'bebida') {
            $bebidas[] = $item;
        } else {
            $pizzas[] = $item;
        }
    }

    echo json_encode([
        'sucesso' => true,
        'pizzas'  => $pizzas,
        'bebidas' => $bebidas,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar cardápio. Tente novamente.']);
}
