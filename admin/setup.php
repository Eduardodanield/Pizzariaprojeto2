<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: admin/setup.php
 * Responsável principal: Eduardo Daniel 
 * Suporte: — Diogo Neves Oliveira RA: 2224102999 
 * Descrição: Script de primeiro acesso — cria ou redefine o usuário admin.
 *            Execute UMA vez após importar o banco, depois DELETE este arquivo.
 *
 * Acesso: http://localhost/pizzaria/Pizzaria-main/admin/setup.php
 *
 * ⚠️  ATENÇÃO: Delete este arquivo após criar o admin!
 *     Deixá-lo acessível permite que qualquer pessoa redefina a senha.
 */

require_once __DIR__ . '/../config/db.php';

// ── Credenciais padrão — altere aqui antes de executar em produção ───────────
$USUARIO_PADRAO = 'admin';
$SENHA_PADRAO   = 'admin123';
// ─────────────────────────────────────────────────────────────────────────────

$mensagem = '';
$sucesso  = false;

try {
    $pdo = obterConexao();

    // Verifica se o usuário já existe no banco
    $stmt = $pdo->prepare("SELECT id FROM usuarios_admin WHERE usuario = ?");
    $stmt->execute([$USUARIO_PADRAO]);
    $adminExistente = $stmt->fetch();

    // Gera o hash bcrypt da senha — nunca armazene senha em texto puro
    $hash = password_hash($SENHA_PADRAO, PASSWORD_BCRYPT);

    if ($adminExistente) {
        // Atualiza o hash (útil para redefinir senha esquecida)
        $upd = $pdo->prepare("UPDATE usuarios_admin SET senha_hash = ? WHERE usuario = ?");
        $upd->execute([$hash, $USUARIO_PADRAO]);
        $mensagem = "Senha do admin <strong>{$USUARIO_PADRAO}</strong> redefinida com sucesso.";
    } else {
        // Cria o admin pela primeira vez
        $ins = $pdo->prepare(
            "INSERT INTO usuarios_admin (usuario, senha_hash) VALUES (?, ?)"
        );
        $ins->execute([$USUARIO_PADRAO, $hash]);
        $mensagem = "Admin <strong>{$USUARIO_PADRAO}</strong> criado com sucesso.";
    }

    $sucesso = true;

} catch (PDOException $e) {
    $mensagem = "Erro: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
              . "<br>Verifique se o banco <strong>pizzaria_tt</strong> foi importado e se o XAMPP está rodando.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Setup — Pizzaria TT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-900 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md text-center">

        <p class="text-5xl mb-4"><?= $sucesso ? '✅' : '❌' ?></p>
        <h1 class="text-2xl font-bold <?= $sucesso ? 'text-green-600' : 'text-red-600' ?> mb-4">
            <?= $sucesso ? 'Setup Concluído' : 'Erro no Setup' ?>
        </h1>

        <p class="text-gray-700 mb-6"><?= $mensagem ?></p>

        <?php if ($sucesso): ?>
            <!-- Exibe as credenciais para o primeiro acesso -->
            <div class="bg-gray-100 rounded-lg p-4 text-left mb-6 text-sm">
                <p class="font-semibold text-gray-600 mb-2">Credenciais de acesso:</p>
                <p><strong>Usuário:</strong> <?= htmlspecialchars($USUARIO_PADRAO) ?></p>
                <p><strong>Senha:</strong> <?= htmlspecialchars($SENHA_PADRAO) ?></p>
            </div>

            <!-- Alerta vermelho destacado para apagar o arquivo -->
            <div class="bg-red-100 border border-red-400 text-red-700 rounded-lg p-4 mb-6 text-sm text-left">
                <p class="font-bold mb-1">⚠️ Segurança — Ação obrigatória:</p>
                <p>Delete o arquivo <code class="bg-red-200 px-1 rounded">admin/setup.php</code>
                   imediatamente após este acesso. Deixá-lo acessível permite que qualquer pessoa
                   redefina a senha de administrador.</p>
            </div>

            <a href="login.php"
               class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold
                      px-6 py-3 rounded-lg transition">
                Ir para o Login →
            </a>
        <?php endif; ?>

    </div>
</body>
</html>
