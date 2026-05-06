<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: admin/login.php
 * Responsável principal: Eduardo Daniel (Back-end PHP)
 * Suporte: —
 * Descrição: Página de login do painel administrativo.
 *            GET  → exibe o formulário de login.
 *            POST → valida credenciais, inicia sessão e redireciona.
 */

session_start();

// Se já está autenticado, vai direto para o painel
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

// ── Processamento do formulário (POST) ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';

    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = $_POST['senha']        ?? '';

    if (empty($usuario) || empty($senha)) {
        $erro = 'Preencha usuário e senha.';
    } else {
        try {
            $pdo  = obterConexao();
            $stmt = $pdo->prepare(
                "SELECT id, usuario, senha_hash FROM usuarios_admin WHERE usuario = ? LIMIT 1"
            );
            $stmt->execute([$usuario]);
            $admin = $stmt->fetch();

            // password_verify compara a senha em texto com o hash bcrypt salvo no banco
            if ($admin && password_verify($senha, $admin['senha_hash'])) {
                // Regenera o ID da sessão para prevenir session fixation attack
                session_regenerate_id(true);

                $_SESSION['admin_id']      = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];

                header('Location: index.php');
                exit;
            } else {
                // Mensagem genérica: não revela se o usuário existe
                $erro = 'Usuário ou senha incorretos.';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao verificar credenciais. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Painel Pizzaria TT</title>
    <!-- Tailwind via CDN: suficiente para páginas internas do admin -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-900 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-sm">

        <!-- Cabeçalho com logo -->
        <div class="text-center mb-6">
            <p class="text-4xl mb-2">🍕</p>
            <h1 class="text-2xl font-bold text-red-600">Pizzaria TT</h1>
            <p class="text-sm text-gray-500 mt-1">Painel Administrativo</p>
        </div>

        <!-- Mensagem de erro (só aparece se houver erro) -->
        <?php if ($erro): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de login -->
        <form method="POST" action="login.php" novalidate>

            <div class="mb-4">
                <label for="usuario" class="block text-sm font-semibold text-gray-700 mb-1">
                    Usuário
                </label>
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    required
                    autocomplete="username"
                    placeholder="admin"
                    value="<?= htmlspecialchars($_POST['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    class="w-full border-2 border-gray-300 rounded-lg px-3 py-2
                           focus:outline-none focus:border-red-500 transition"
                />
            </div>

            <div class="mb-6">
                <label for="senha" class="block text-sm font-semibold text-gray-700 mb-1">
                    Senha
                </label>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full border-2 border-gray-300 rounded-lg px-3 py-2
                           focus:outline-none focus:border-red-500 transition"
                />
            </div>

            <button
                type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold
                       py-2 rounded-lg transition duration-200"
            >
                Entrar
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
            Pizzaria TT &copy; <?= date('Y') ?> — Projeto de Extensão
        </p>
    </div>

</body>
</html>
