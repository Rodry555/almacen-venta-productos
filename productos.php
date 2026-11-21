<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/catalogo.php';

$categoria = isset($_GET['categoria']) ? max(0, (int) $_GET['categoria']) : null;
if ($categoria === 0) {
    $categoria = null;
}
$q = trim((string) ($_GET['q'] ?? ''));
$categorias = categoriasActivas();
$productos = listarProductos($categoria, $q);
$pageTitle = 'Productos';
$extraScripts = ['/assets/js/productos.js'];
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <!-- Cabecera -->
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6" data-aos="fade-up">
        <div>
            <nav class="mb-3 flex items-center gap-2 text-xs text-ink-500">
                <a href="/index.php" class="hover:text-brand-700">Inicio</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="font-semibold text-ink-800">Productos</span>
            </nav>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Catálogo completo</p>
            <h1 class="mt-2 font-display text-3xl md:text-5xl font-extrabold tracking-tight text-ink-900">Nuestros productos</h1>
            <p class="mt-3 max-w-2xl text-ink-500">Filtra por categoría o busca por nombre. Luego agrégalos a tu carrito de reservas.</p>
        </div>
        <span class="inline-flex items-center gap-2 self-start rounded-full border border-brand-100 bg-brand-50 px-4 py-2 text-xs font-bold text-brand-700">
            <i class="fa-solid fa-box"></i>
            <?= count($productos) ?> <?= count($productos) === 1 ? 'resultado' : 'resultados' ?>
        </span>
    </div>

    <!-- Buscador -->
    <form method="get" action="/productos.php"
          class="mt-8 rounded-3xl border border-slate-100 bg-white p-4 shadow-soft sm:p-5" data-aos="fade-up">
        <?php if ($categoria !== null): ?>
            <input type="hidden" name="categoria" value="<?= $categoria ?>">
        <?php endif; ?>
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-ink-400"></i>
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre de producto..."
                       aria-label="Buscar productos"
                       class="w-full rounded-xl border border-slate-200 bg-cream py-3 pl-11 pr-4 text-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
            </div>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-ink-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-600">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-60">
                <i class="fa-solid fa-filter absolute left-3.5 top-1/2 -translate-y-1/2 text-brand-500 text-xs"></i>
                <input type="text" id="filtro-rapido" placeholder="Filtro rápido en esta página..."
                       aria-label="Filtro rápido"
                       class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-xs outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100">
            </div>
            <?php if ($q !== '' || $categoria !== null): ?>
                <a href="/productos.php" class="text-xs font-semibold text-ink-500 hover:text-brand-700 hover:underline">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Limpiar filtros
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Categorías (scroll horizontal en móvil) -->
    <div class="mt-7 -mx-4 px-4 sm:mx-0 sm:px-0" data-aos="fade-up">
        <div class="flex gap-2 overflow-x-auto pb-2 sin-scrollbar" role="group" aria-label="Filtrar por categoría">
            <a href="/productos.php<?= $q !== '' ? '?q=' . rawurlencode($q) : '' ?>"
               class="inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-semibold transition <?= $categoria === null ? 'border-brand-600 bg-brand-600 text-white shadow-soft' : 'border-slate-200 bg-white text-ink-600 hover:border-brand-300 hover:text-brand-700' ?>">
                <i class="fa-solid fa-table-cells"></i> Todos
            </a>
            <?php foreach ($categorias as $c): ?>
                <?php
                $categoriaId = (int) $c['id'];
                $nombreCategoria = (string) $c['name'];
                $iconoCategoria = categoriaIcono($categoriaId, $nombreCategoria);
                $activo = $categoria === $categoriaId;
                ?>
                <a href="/productos.php?categoria=<?= $categoriaId ?><?= $q !== '' ? '&q=' . rawurlencode($q) : '' ?>"
                   class="inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-semibold transition <?= $activo ? 'border-brand-600 bg-brand-600 text-white shadow-soft' : 'border-slate-200 bg-white text-ink-600 hover:border-brand-300 hover:text-brand-700' ?>">
                    <i class="fa-solid <?= e($iconoCategoria) ?>"></i>
                    <?= e($nombreCategoria) ?>
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] <?= $activo ? 'bg-white/20' : 'bg-slate-100 text-ink-500' ?>">
                        <?= (int) $c['total_productos'] ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Resultados -->
    <?php if (!$productos): ?>
        <div class="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center" data-aos="fade-up">
            <span class="w-16 h-16 mx-auto rounded-2xl bg-slate-50 text-ink-400 grid place-items-center text-2xl">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <p class="mt-4 font-display text-xl font-bold text-ink-900">No encontramos productos</p>
            <p class="mt-1 text-sm text-ink-500">Prueba con otro nombre o elige una categoría distinta.</p>
            <a href="/productos.php" class="mt-5 inline-flex rounded-xl bg-ink-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition">
                Ver todos los productos
            </a>
        </div>
    <?php else: ?>
        <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($productos as $p): ?>
                <?php include __DIR__ . '/includes/producto-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <p id="sin-resultados-rapidos" class="hidden mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-ink-500">
            Ningún producto coincide con el filtro rápido.
        </p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
