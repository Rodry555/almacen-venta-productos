<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/catalogo.php';

$categoria = isset($_GET['categoria']) ? (int) $_GET['categoria'] : null;
$q = trim((string) ($_GET['q'] ?? ''));
$categorias = categoriasActivas();
$productos = listarProductos($categoria, $q);
$pageTitle = 'Productos';
$extraScripts = ['/assets/js/productos.js'];
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-2">Nuestros productos</h1>
    <p class="text-slate-500 mb-8">Filtra por categoría o busca por nombre.</p>

    <form method="get" action="/productos.php" class="flex flex-wrap gap-3 mb-6">
        <?php if ($categoria): ?>
            <input type="hidden" name="categoria" value="<?= $categoria ?>">
        <?php endif; ?>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar producto..."
               class="flex-1 min-w-60 border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold">
            <i class="fa-solid fa-magnifying-glass mr-1"></i> Buscar
        </button>
        <input type="text" id="filtro-rapido" placeholder="Filtro rápido en pantalla..."
               class="border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </form>

    <div class="flex flex-wrap gap-2 mb-8">
        <a href="/productos.php" class="px-4 py-2 rounded-full text-sm <?= $categoria === null ? 'bg-slate-800 text-white' : 'bg-white border border-slate-200' ?>">
            Todos
        </a>
        <?php foreach ($categorias as $c): ?>
            <a href="/productos.php?categoria=<?= (int) $c['id'] ?><?= $q !== '' ? '&q=' . rawurlencode($q) : '' ?>"
               class="px-4 py-2 rounded-full text-sm <?= $categoria === (int) $c['id'] ? 'bg-slate-800 text-white' : 'bg-white border border-slate-200' ?>">
                <?= e($c['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (!$productos): ?>
        <div class="bg-white rounded-xl p-10 text-center text-slate-500">
            No se encontraron productos con esos criterios.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($productos as $p): ?>
            <?php include __DIR__ . '/includes/producto-card.php'; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
