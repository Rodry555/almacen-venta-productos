<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/catalogo.php';

$categorias = categoriasActivas();
$destacados = productosDestacados(6);
$totalProductos = (int) db()->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
$pageTitle = 'Inicio';
include __DIR__ . '/includes/header.php';
?>

<section class="bg-gradient-to-r from-slate-800 to-blue-700 text-white">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24" data-aos="fade-up">
        <p class="text-emerald-300 font-semibold uppercase tracking-widest text-sm">Catálogo con reserva previa</p>
        <h1 class="text-4xl md:text-5xl font-bold mt-3">Encuentra lo que necesitas y resérvalo</h1>
        <p class="mt-4 max-w-xl text-slate-200">
            Consulta disponibilidad en tiempo real, arma tu carrito y solicita la fecha de retiro.
            Sin pagos online: pagas al recibir en tienda.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="/productos.php" class="bg-emerald-500 hover:bg-emerald-600 px-6 py-3 rounded-lg font-semibold">
                Ver productos
            </a>
            <a href="/contacto.php" class="border border-white/60 hover:bg-white/10 px-6 py-3 rounded-lg font-semibold">
                Contáctanos
            </a>
        </div>
        <p class="mt-8 text-sm text-slate-300"><strong><?= $totalProductos ?></strong> productos disponibles en el catálogo</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-12">
    <h2 class="text-2xl font-bold mb-6">Categorías</h2>
    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
        <?php if (!$categorias): ?>
            <p class="text-slate-500">Todavía no hay categorías activas.</p>
        <?php endif; ?>
        <?php foreach ($categorias as $c): ?>
            <a href="/productos.php?categoria=<?= (int) $c['id'] ?>"
               class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md border border-slate-100" data-aos="fade-up">
                <i class="fa-solid fa-layer-group text-blue-600 text-xl mb-3"></i>
                <h3 class="font-bold"><?= e($c['name']) ?></h3>
                <p class="text-sm text-slate-500 mt-1"><?= e($c['description']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-4 pb-12">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Productos recientes</h2>
        <a href="/productos.php" class="text-blue-600 text-sm font-semibold hover:underline">Ver todos →</a>
    </div>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php if (!$destacados): ?>
            <p class="text-slate-500">Todavía no hay productos publicados.</p>
        <?php endif; ?>
        <?php foreach ($destacados as $p): ?>
            <?php include __DIR__ . '/includes/producto-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
