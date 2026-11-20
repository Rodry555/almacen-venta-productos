<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/catalogo.php';

$id = (int) ($_GET['id'] ?? 0);
$producto = $id > 0 ? productoPorId($id) : null;
$pageTitle = $producto !== null ? $producto['name'] : 'Producto no encontrado';
include __DIR__ . '/includes/header.php';

if ($producto === null): ?>
    <div class="max-w-3xl mx-auto px-4 py-20 text-center">
        <i class="fa-solid fa-box-open text-5xl text-slate-300 mb-4"></i>
        <h1 class="text-2xl font-bold">Producto no encontrado</h1>
        <p class="text-slate-500 mt-2">Puede que esté desactivado o que el enlace sea incorrecto.</p>
        <a href="/productos.php" class="inline-block mt-6 bg-blue-600 text-white px-5 py-2 rounded-lg">Volver al catálogo</a>
    </div>
<?php else: ?>
    <div class="max-w-6xl mx-auto px-4 py-10">
        <nav class="text-sm text-slate-500 mb-6">
            <a href="/productos.php" class="hover:text-blue-600">Productos</a>
            <?php if (!empty($producto['categoria'])): ?>
                / <span><?= e($producto['categoria']) ?></span>
            <?php endif; ?>
            / <span class="text-slate-700"><?= e($producto['name']) ?></span>
        </nav>

        <div class="grid gap-10 md:grid-cols-2 bg-white rounded-2xl shadow-sm p-6 md:p-10">
            <img src="<?= e(!empty($producto['image_url']) ? $producto['image_url'] : '/assets/img/placeholder.svg') ?>"
                 alt="<?= e($producto['name']) ?>" class="w-full aspect-square object-cover rounded-xl bg-slate-100">

            <div class="flex flex-col">
                <?php if (!empty($producto['categoria'])): ?>
                    <span class="text-xs text-blue-600 font-semibold uppercase"><?= e($producto['categoria']) ?></span>
                <?php endif; ?>
                <h1 class="text-3xl font-bold mt-2"><?= e($producto['name']) ?></h1>
                <p class="text-3xl font-bold text-emerald-600 mt-4">$<?= e(number_format((float) $producto['price'], 2)) ?></p>
                <p class="mt-4 text-slate-600 leading-relaxed"><?= nl2br(e($producto['description'])) ?></p>

                <div class="mt-6">
                    <?php if ((int) $producto['stock'] > 0): ?>
                        <p class="text-emerald-700 font-semibold"><i class="fa-solid fa-circle-check mr-2"></i>Disponible: <?= (int) $producto['stock'] ?> unidades</p>
                    <?php else: ?>
                        <p class="text-red-600 font-semibold"><i class="fa-solid fa-circle-xmark mr-2"></i>Agotado temporalmente</p>
                    <?php endif; ?>
                </div>

                <button type="button"
                        class="btn-agregar mt-8 w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg disabled:bg-slate-300"
                        data-agregar
                        data-id="<?= (int) $producto['id'] ?>"
                        data-nombre="<?= e($producto['name']) ?>"
                        data-precio="<?= e((float) $producto['price']) ?>"
                        data-stock="<?= (int) $producto['stock'] ?>"
                        data-imagen="<?= e(!empty($producto['image_url']) ? $producto['image_url'] : '/assets/img/placeholder.svg') ?>"
                        <?= (int) $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-cart-plus mr-2"></i> Agregar al carrito
                </button>
                <a href="/carrito/carrito.php" class="mt-4 text-sm text-blue-600 hover:underline">Ir a mi carrito →</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
