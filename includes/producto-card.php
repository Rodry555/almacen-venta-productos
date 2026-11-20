<?php
/** Partial de tarjeta de producto. Espera la variable $p (array de producto). */
$productoId = (int) $p['id'];
$productoStock = (int) $p['stock'];
$productoImagen = !empty($p['image_url']) ? (string) $p['image_url'] : '/assets/img/placeholder.svg';
?>
<article class="producto-card bg-white rounded-2xl shadow-sm hover:shadow-lg transition overflow-hidden flex flex-col" data-aos="fade-up">
    <a href="/producto-detalle.php?id=<?= $productoId ?>" class="block bg-slate-100 aspect-square overflow-hidden">
        <img src="<?= e($productoImagen) ?>" alt="<?= e($p['name']) ?>"
             class="w-full h-full object-cover hover:scale-105 transition" loading="lazy">
    </a>
    <div class="p-4 flex flex-col flex-1">
        <?php if (!empty($p['categoria'])): ?>
            <span class="text-xs text-blue-600 font-semibold uppercase tracking-wide"><?= e($p['categoria']) ?></span>
        <?php endif; ?>
        <h3 class="font-semibold text-slate-800 mt-1">
            <a href="/producto-detalle.php?id=<?= $productoId ?>" class="hover:text-blue-600"><?= e($p['name']) ?></a>
        </h3>
        <p class="text-slate-500 text-sm mt-1 flex-1"><?= e(mb_substr((string) $p['description'], 0, 90)) ?></p>
        <div class="flex items-center justify-between mt-4">
            <span class="text-lg font-bold text-emerald-600">$<?= e(number_format((float) $p['price'], 2)) ?></span>
            <span class="text-xs <?= $productoStock > 0 ? 'text-slate-500' : 'text-red-600' ?>">
                <?= $productoStock > 0 ? 'Stock: ' . $productoStock : 'Agotado' ?>
            </span>
        </div>
        <button type="button"
                class="btn-agregar mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 rounded-lg disabled:bg-slate-300 disabled:cursor-not-allowed"
                data-agregar
                data-id="<?= $productoId ?>"
                data-nombre="<?= e($p['name']) ?>"
                data-precio="<?= e((float) $p['price']) ?>"
                data-stock="<?= $productoStock ?>"
                data-imagen="<?= e($productoImagen) ?>"
                <?= $productoStock <= 0 ? 'disabled' : '' ?>>
            <i class="fa-solid fa-cart-plus mr-1"></i> Agregar al carrito
        </button>
    </div>
</article>
