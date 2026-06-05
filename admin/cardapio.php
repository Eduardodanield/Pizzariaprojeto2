<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: admin/cardapio.php
 * Responsável principal: Eduardo Daniel RA: 2224104694 (Back-end PHP)
 * Suporte: Eduardo Matheus RA: 2224107415 (Front-end)
 * Descrição: Gerenciamento do cardápio — adicionar novas pizzas/bebidas
 *            com upload de imagem e desativar/reativar itens existentes.
 *            Requer sessão admin ativa.
 */

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$pdo      = obterConexao();
$mensagem = '';
$tipoMsg  = '';

// ── AÇÃO: Adicionar nova pizza ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'adicionar') {

    $nome      = trim($_POST['nome']      ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = $_POST['categoria'] ?? 'pizza';
    $preco_m   = floatval($_POST['preco_m'] ?? 0);
    $preco_p   = $categoria === 'pizza' ? floatval($_POST['preco_p'] ?? 0) : null;
    $preco_g   = $categoria === 'pizza' ? floatval($_POST['preco_g'] ?? 0) : null;

    // Validações básicas
    if (empty($nome) || $preco_m <= 0) {
        $mensagem = 'Nome e preço M são obrigatórios.';
        $tipoMsg  = 'erro';

    } elseif (empty($_FILES['imagem']['name'])) {
        $mensagem = 'Selecione uma imagem para o item.';
        $tipoMsg  = 'erro';

    } else {
        // Upload de imagem
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $nomeOriginal        = $_FILES['imagem']['name'];
        $extensao            = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $tamanhoMax          = 5 * 1024 * 1024; // 5 MB

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            $mensagem = 'Formato inválido. Use JPG, PNG, WEBP ou GIF.';
            $tipoMsg  = 'erro';

        } elseif ($_FILES['imagem']['size'] > $tamanhoMax) {
            $mensagem = 'Imagem muito grande. Máximo permitido: 5 MB.';
            $tipoMsg  = 'erro';

        } else {
            // Nome de arquivo seguro: slug do nome da pizza + timestamp
            $slug      = preg_replace('/[^a-z0-9]+/', '_', strtolower($nome));
            $nomeArq   = $slug . '_' . time() . '.' . $extensao;
            $destino   = __DIR__ . '/../assets/' . $nomeArq;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                try {
                    $stmt = $pdo->prepare(
                        "INSERT INTO pizzas (nome, descricao, preco_p, preco_m, preco_g, imagem, categoria)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$nome, $descricao, $preco_p, $preco_m, $preco_g, $nomeArq, $categoria]);

                    $mensagem = "✅ \"{$nome}\" adicionado ao cardápio com sucesso!";
                    $tipoMsg  = 'sucesso';

                } catch (PDOException $e) {
                    @unlink($destino); // Remove imagem se o INSERT falhar
                    $mensagem = 'Erro ao salvar no banco. Tente novamente.';
                    $tipoMsg  = 'erro';
                }
            } else {
                $mensagem = 'Falha ao fazer upload da imagem. Verifique permissões da pasta assets/.';
                $tipoMsg  = 'erro';
            }
        }
    }
}

// ── AÇÃO: Ativar / Desativar item ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['toggle'], $_GET['id'])) {
    $id    = (int) $_GET['id'];
    $novoStatus = (int) $_GET['toggle']; // 0 ou 1

    if ($id > 0 && in_array($novoStatus, [0, 1], true)) {
        $pdo->prepare("UPDATE pizzas SET ativa = ? WHERE id = ?")->execute([$novoStatus, $id]);
        $mensagem = $novoStatus === 1 ? '✅ Item reativado.' : '🚫 Item desativado.';
        $tipoMsg  = $novoStatus === 1 ? 'sucesso' : 'aviso';
    }
}

// ── Carrega todos os itens do cardápio ───────────────────────────────────────
$pizzas = $pdo->query(
    "SELECT id, nome, descricao, preco_p, preco_m, preco_g, imagem, categoria, ativa
     FROM pizzas ORDER BY ativa DESC, categoria, nome"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio — Pizzaria TT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- ── Cabeçalho ── -->
<header class="bg-red-600 text-white py-4 px-6 flex justify-between items-center shadow-lg sticky top-0 z-10">
    <div class="flex items-center gap-3">
        <span class="text-2xl">🍕</span>
        <div>
            <h1 class="font-bold text-lg leading-tight">Pizzaria TT</h1>
            <p class="text-xs text-red-200">Gerenciar Cardápio</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <a href="index.php" class="bg-white text-red-600 px-3 py-1 rounded-lg text-sm font-semibold hover:bg-red-50 transition">
            📋 Pedidos
        </a>
        <a href="logout.php" class="bg-red-800 text-white px-3 py-1 rounded-lg text-sm font-semibold hover:bg-red-900 transition">
            Sair
        </a>
    </div>
</header>

<main class="max-w-6xl mx-auto px-4 py-6">

    <!-- ── Mensagem de feedback ── -->
    <?php if ($mensagem): ?>
        <div class="mb-5 px-4 py-3 rounded-lg text-sm font-medium
            <?= $tipoMsg === 'sucesso' ? 'bg-green-100 text-green-800 border border-green-300'
              : ($tipoMsg === 'aviso'  ? 'bg-yellow-100 text-yellow-800 border border-yellow-300'
              : 'bg-red-100 text-red-800 border border-red-300') ?>">
            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- ── Formulário: adicionar item ── -->
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h2 class="font-bold text-xl text-gray-800 mb-4">➕ Adicionar item ao cardápio</h2>

        <form method="POST" action="cardapio.php" enctype="multipart/form-data"
              class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <input type="hidden" name="acao" value="adicionar">

            <!-- Nome -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nome *</label>
                <input type="text" name="nome" required maxlength="100"
                       placeholder="Ex: Calabresa, Marguerita, Coca-Cola 2L..."
                       class="w-full border-2 border-gray-300 rounded-lg px-3 py-2
                              focus:outline-none focus:border-red-500 transition">
            </div>

            <!-- Descrição -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Descrição</label>
                <textarea name="descricao" rows="2" maxlength="500"
                          placeholder="Ingredientes e detalhes do item..."
                          class="w-full border-2 border-gray-300 rounded-lg px-3 py-2
                                 focus:outline-none focus:border-red-500 transition resize-none"></textarea>
            </div>

            <!-- Categoria -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Categoria *</label>
                <select name="categoria" id="categoria-select"
                        class="w-full border-2 border-gray-300 rounded-lg px-3 py-2
                               focus:outline-none focus:border-red-500 transition">
                    <option value="pizza">🍕 Pizza</option>
                    <option value="bebida">🥤 Bebida</option>
                </select>
            </div>

            <!-- Preço M -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Preço M / Único (R$) *
                </label>
                <input type="number" name="preco_m" required min="0.01" step="0.01"
                       placeholder="35.00"
                       class="w-full border-2 border-gray-300 rounded-lg px-3 py-2
                              focus:outline-none focus:border-red-500 transition">
            </div>

            <!-- Preço P (só pizza) -->
            <div id="campo-preco-p">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Preço P (R$)</label>
                <input type="number" name="preco_p" min="0.01" step="0.01"
                       placeholder="30.00"
                       class="w-full border-2 border-gray-300 rounded-lg px-3 py-2
                              focus:outline-none focus:border-red-500 transition">
            </div>

            <!-- Preço G (só pizza) -->
            <div id="campo-preco-g">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Preço G (R$)</label>
                <input type="number" name="preco_g" min="0.01" step="0.01"
                       placeholder="40.00"
                       class="w-full border-2 border-gray-300 rounded-lg px-3 py-2
                              focus:outline-none focus:border-red-500 transition">
            </div>

            <!-- Upload de imagem -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Imagem * <span class="text-gray-400 font-normal">(JPG, PNG, WEBP — máx. 5 MB)</span>
                </label>
                <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp,image/gif" required
                       class="w-full border-2 border-gray-300 rounded-lg px-3 py-2
                              focus:outline-none focus:border-red-500 transition
                              file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0
                              file:bg-red-600 file:text-white file:font-semibold file:cursor-pointer">
            </div>

            <div class="md:col-span-2">
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2
                               rounded-lg transition duration-200">
                    Salvar no cardápio
                </button>
            </div>

        </form>
    </div>

    <!-- ── Lista de pizzas ── -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-bold text-xl text-gray-800">📋 Itens do cardápio (<?= count($pizzas) ?>)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Imagem</th>
                        <th class="px-4 py-3 text-left">Nome</th>
                        <th class="px-4 py-3 text-left">Categoria</th>
                        <th class="px-4 py-3 text-right">P</th>
                        <th class="px-4 py-3 text-right">M</th>
                        <th class="px-4 py-3 text-right">G</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($pizzas as $pizza): ?>
                    <tr class="<?= $pizza['ativa'] ? 'hover:bg-gray-50' : 'bg-gray-50 opacity-60' ?>">

                        <!-- Imagem -->
                        <td class="px-4 py-3">
                            <img src="../assets/<?= htmlspecialchars($pizza['imagem'], ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($pizza['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                 class="w-14 h-14 object-contain rounded-lg"
                                 onerror="this.src='../assets/Logo_Pizzaria.jpg'">
                        </td>

                        <!-- Nome + descrição -->
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-800">
                                <?= htmlspecialchars($pizza['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="text-gray-400 text-xs mt-0.5 max-w-xs truncate">
                                <?= htmlspecialchars($pizza['descricao'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </td>

                        <!-- Categoria -->
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                <?= $pizza['categoria'] === 'pizza'
                                    ? 'bg-orange-100 text-orange-700'
                                    : 'bg-blue-100 text-blue-700' ?>">
                                <?= $pizza['categoria'] === 'pizza' ? '🍕 Pizza' : '🥤 Bebida' ?>
                            </span>
                        </td>

                        <!-- Preços -->
                        <td class="px-4 py-3 text-right text-gray-600">
                            <?= $pizza['preco_p'] ? 'R$ ' . number_format($pizza['preco_p'], 2, ',', '.') : '—' ?>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                            R$ <?= number_format($pizza['preco_m'], 2, ',', '.') ?>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            <?= $pizza['preco_g'] ? 'R$ ' . number_format($pizza['preco_g'], 2, ',', '.') : '—' ?>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3 text-center">
                            <?php if ($pizza['ativa']): ?>
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">
                                    ✅ Ativo
                                </span>
                            <?php else: ?>
                                <span class="bg-gray-200 text-gray-500 px-2 py-1 rounded-full text-xs font-semibold">
                                    🚫 Inativo
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Botão de ação -->
                        <td class="px-4 py-3 text-center">
                            <?php if ($pizza['ativa']): ?>
                                <a href="?toggle=0&id=<?= $pizza['id'] ?>"
                                   onclick="return confirm('Desativar \'<?= addslashes($pizza['nome']) ?>\'? Ele sumirá do site mas não será apagado.')"
                                   class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1
                                          rounded-lg text-xs font-semibold transition">
                                    🚫 Desativar
                                </a>
                            <?php else: ?>
                                <a href="?toggle=1&id=<?= $pizza['id'] ?>"
                                   class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1
                                          rounded-lg text-xs font-semibold transition">
                                    ✅ Reativar
                                </a>
                            <?php endif; ?>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
    // Esconde campos de preço P/G quando categoria for bebida
    const categoriaSelect = document.getElementById('categoria-select');
    const campoP = document.getElementById('campo-preco-p');
    const campoG = document.getElementById('campo-preco-g');

    function togglePrecos() {
        const ehBebida = categoriaSelect.value === 'bebida';
        campoP.style.display = ehBebida ? 'none' : '';
        campoG.style.display = ehBebida ? 'none' : '';
    }

    categoriaSelect.addEventListener('change', togglePrecos);
    togglePrecos();
</script>

</body>
</html>
