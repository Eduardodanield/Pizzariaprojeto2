<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: config/db.php
 * Responsável principal: Eduardo Daniel (Back-end PHP)
 * Suporte: —
 * Descrição: Fornece a conexão PDO com o banco de dados pizzaria_tt
 *            rodando no MySQL do XAMPP (localhost, root, sem senha).
 */

/**
 * Retorna uma conexão PDO reutilizável (padrão singleton por requisição).
 *
 * Configurações importantes:
 *   - ERRMODE_EXCEPTION: qualquer erro SQL lança PDOException (facilita try/catch)
 *   - FETCH_ASSOC: resultados como arrays associativos (sem índices numéricos extras)
 *   - EMULATE_PREPARES false: prepared statements reais no MySQL (previne SQL Injection)
 *   - charset utf8mb4: suporte completo a emojis e caracteres especiais
 *
 * @return PDO Instância de conexão pronta para uso
 */
function obterConexao(): PDO
{
    // Variável estática: persiste entre chamadas na mesma requisição PHP
    // Evita abrir múltiplas conexões desnecessárias no mesmo ciclo de vida
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    // ── Credenciais do XAMPP padrão ──────────────────────────────────────
    $host    = 'localhost';
    $banco   = 'pizzaria_tt';
    $usuario = 'root';
    $senha   = '';          // XAMPP default: senha vazia
    // ─────────────────────────────────────────────────────────────────────

    $dsn = "mysql:host={$host};dbname={$banco};charset=utf8mb4";

    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
    } catch (PDOException $e) {
        // Retorna JSON de erro e encerra — os endpoints tratam isso como 500
        // Em produção: registre em log e não exponha $e->getMessage()
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'erro' => 'Não foi possível conectar ao banco de dados. '
                    . 'Verifique se o MySQL do XAMPP está rodando e se o banco pizzaria_tt existe.',
        ]);
        exit;
    }

    return $pdo;
}
