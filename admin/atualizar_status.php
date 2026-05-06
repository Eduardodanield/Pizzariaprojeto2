<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: admin/atualizar_status.php
 * Responsável principal: Eduardo Daniel (Back-end PHP)
 * Suporte: —
 * Descrição: Endpoint AJAX chamado pelo admin/index.php para atualizar
 *            o status de um pedido sem recarregar a página.
 *
 * Método: POST (JSON)
 * Requer: sessão admin ativa
 * Body:   { "pedido_id": 42, "novo_status": "preparando" }
 * Resposta: { "sucesso": true, "novo_status": "preparando" }
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Apenas admins autenticados podem alterar status de pedidos
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Acesso negado. Faça login primeiro.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Lê o corpo JSON da requisição AJAX
$corpo = file_get_contents('php://input');
$dados = json_decode($corpo, true);

$pedidoId   = (int) ($dados['pedido_id']   ?? 0);
$novoStatus = trim($dados['novo_status']   ?? '');

// Apenas os status válidos do ENUM da tabela são aceitos
$statusPermitidos = ['recebido', 'preparando', 'saiu_entrega', 'entregue', 'cancelado'];

if ($pedidoId <= 0 || !in_array($novoStatus, $statusPermitidos, true)) {
    http_response_code(422);
    echo json_encode(['erro' => 'Dados inválidos: pedido_id ou novo_status incorreto.']);
    exit;
}

try {
    $pdo  = obterConexao();
    $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
    $stmt->execute([$novoStatus, $pedidoId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['erro' => 'Pedido não encontrado.']);
        exit;
    }

    echo json_encode(['sucesso' => true, 'novo_status' => $novoStatus]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao atualizar status. Tente novamente.']);
}
