<?php
/** Partial de tarjeta de producto. Espera la variable $p (array de producto). */
require_once __DIR__ . '/catalogo.php';

$productoId = (int) $p['id'];
$productoStock = (int) $p['stock'];
$tieneImagen = !empty($p['image_url']);
$productoImagen = $tieneImagen ? (string) $p['image_url'] : '/assets/img/placeholder.svg';
$nombreCategoria = (string) ($p['categoria'] ?? '');
$categoriaId = (int) ($p['category_id'] ?? 0);
[$iconoTono, $degradado] = categoriaTono($categoriaId, $nombreCategoria);
$iconoCategoria = categoriaIcono($categoriaId, $nombreCategoria);
?>
<article class="producto-card group flex flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-soft hover:shadow-lift"
         data-aos="fade-up">
    <div class="relative">
        <a href="/producto-detalle.php?id=<?= $productoId ?>"
           class="block aspect-[5/4] overflow-hidden bg-gradient-to-br <?= e($degradado) ?>">
            <?php if ($tieneImagen): ?>
                <img src="<?= e($productoImagen) ?>" alt="<?= e($p['name']) ?>"
                     class="w-full h-full object-contain p-4 transition duration-500 group-hover:scale-105" loading="lazy">
            <?php else: ?>
                <span class="absolute inset-0 grid place-items-center" aria-hidden="true">
                    <span class="w-20 h-20 rounded-3xl bg-white/80 shadow-sm grid place-items-center ring-1 ring-white">
                        <i class="fa-solid <?= e($iconoCategoria) ?> text-3xl <?= e($iconoTono) ?>"></i>
                    </span>
                </span>
                <span class="absolute bottom-3 left-0 right-0 text-center text-[10px] font-bold uppercase tracking-[0.18em] text-ink-500/70">
                    Imagen próximamente
                </span>
            <?php endif; ?>
        </a>
        <?php if ($nombreCategoria !== ''): ?>
            <span class="absolute left-3 top-3 inline-flex max-w-[78%] items-center gap-1.5 rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wide text-ink-700 shadow-sm backdrop-blur">
                <i class="fa-solid <?= e($iconoCategoria) ?> <?= e($iconoTono) ?>"></i>
                <span class="truncate"><?= e($nombreCategoria) ?></span>
            </span>
        <?php endif; ?>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <?php if ($nombreCategoria !== ''): ?>
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Catálogo</p>
        <?php endif; ?>
        <h3 class="mt-1.5 font-display text-lg font-bold leading-snug text-ink-900 linea-2 min-h-[3.25rem]">
            <a href="/producto-detalle.php?id=<?= $productoId ?>" class="hover:text-brand-700 transition"><?= e($p['name']) ?></a>
        </h3>
        <p class="mt-2 flex items-center gap-1.5 text-[11px] text-ink-500">
            <i class="fa-solid fa-circle-check text-brand-500"></i>
            <?= e(etiquetaDisponibilidad($productoStock)) ?>
        </p>

        <div class="mt-4 flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-400">Precio</p>
                <p class="font-display text-lg font-extrabold text-ink-900"><?= e(etiquetaPrecio($p['price'])) ?></p>
            </div>
            <span class="rounded-full bg-slate-50 px-2.5 py-1 text-[10px] font-semibold text-ink-500">Retiro en tienda</span>
        </div>

        <button type="button"
                class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-ink-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-brand-600 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400"
                data-agregar
                data-id="<?= $productoId ?>"
                data-nombre="<?= e($p['name']) ?>"
                data-precio="<?= e((float) $p['price']) ?>"
                data-stock="<?= $productoStock ?>"
                data-imagen="<?= e($productoImagen) ?>"
                <?= $productoStock <= 0 ? 'disabled' : '' ?>>
            <i class="fa-solid fa-cart-plus"></i>
            <span><?= $productoStock > 0 ? 'Agregar al carrito' : 'Agotado' ?></span>
        </button>
    </div>
</article>
