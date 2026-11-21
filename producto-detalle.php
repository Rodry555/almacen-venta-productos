<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/catalogo.php';

$id = (int) ($_GET['id'] ?? 0);
$producto = $id > 0 ? productoPorId($id) : null;
$pageTitle = $producto !== null ? $producto['name'] : 'Producto no encontrado';
include __DIR__ . '/includes/header.php';

if ($producto === null): ?>
    <div class="max-w-3xl mx-auto px-4 py-20 text-center" data-aos="fade-up">
        <span class="w-20 h-20 mx-auto rounded-3xl bg-slate-100 text-ink-400 grid place-items-center text-4xl">
            <i class="fa-solid fa-box-open"></i>
        </span>
        <h1 class="mt-5 font-display text-3xl font-extrabold text-ink-900">Producto no encontrado</h1>
        <p class="mt-2 text-ink-500">Puede que esté desactivado o que el enlace sea incorrecto.</p>
        <a href="/productos.php" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-ink-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition">
            <i class="fa-solid fa-arrow-left"></i> Volver al catálogo
        </a>
    </div>
<?php else:
    $productoStock = (int) $producto['stock'];
    $tieneImagen = !empty($producto['image_url']);
    $productoImagen = $tieneImagen ? (string) $producto['image_url'] : '/assets/img/placeholder.svg';
    $nombreCategoria = (string) ($producto['categoria'] ?? '');
    $categoriaId = (int) ($producto['category_id'] ?? 0);
    [$iconoTono, $degradado] = categoriaTono($categoriaId, $nombreCategoria);
    $iconoCategoria = categoriaIcono($categoriaId, $nombreCategoria);
    ?>
    <div class="max-w-7xl mx-auto px-4 py-8 md:py-12">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-xs text-ink-500" aria-label="Ruta de navegación">
            <a href="/productos.php" class="hover:text-brand-700">Productos</a>
            <?php if ($nombreCategoria !== ''): ?>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <a href="/productos.php?categoria=<?= $categoriaId ?>" class="hover:text-brand-700"><?= e($nombreCategoria) ?></a>
            <?php endif; ?>
            <i class="fa-solid fa-chevron-right text-[9px]"></i>
            <span class="font-semibold text-ink-800 linea-2"><?= e($producto['name']) ?></span>
        </nav>

        <div class="grid gap-7 lg:grid-cols-2">
            <!-- Contenedor de imagen (placeholder hasta que haya fotos). -->
            <div class="relative overflow-hidden rounded-4xl border border-slate-100 bg-white shadow-soft" data-aos="fade-up">
                <div class="relative aspect-square overflow-hidden bg-gradient-to-br <?= e($degradado) ?>">
                    <?php if ($tieneImagen): ?>
                        <img src="<?= e($productoImagen) ?>" alt="<?= e($producto['name']) ?>"
                             class="w-full h-full object-contain p-10">
                    <?php else: ?>
                        <div class="absolute inset-0 grid place-items-center" aria-hidden="true">
                            <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-4xl bg-white/80 shadow-soft ring-1 ring-white grid place-items-center">
                                <i class="fa-solid <?= e($iconoCategoria) ?> text-5xl sm:text-6xl <?= e($iconoTono) ?>"></i>
                            </div>
                        </div>
                        <p class="absolute bottom-6 left-0 right-0 text-center text-[11px] font-bold uppercase tracking-[0.2em] text-ink-500/70">
                            Imagen próximamente
                        </p>
                    <?php endif; ?>
                    <?php if ($nombreCategoria !== ''): ?>
                        <span class="absolute left-5 top-5 inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-bold text-ink-700 shadow-sm backdrop-blur">
                            <i class="fa-solid <?= e($iconoCategoria) ?> <?= e($iconoTono) ?>"></i>
                            <?= e($nombreCategoria) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap gap-x-6 gap-y-2 border-t border-slate-100 px-6 py-4 text-xs text-ink-500">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-store text-brand-500"></i> Retiro en tienda</span>
                    <span class="flex items-center gap-2"><i class="fa-solid fa-wallet text-brand-500"></i> Pago al retirar</span>
                    <span class="flex items-center gap-2"><i class="fa-solid fa-shield-halved text-brand-500"></i> Stock apartado al reservar</span>
                </div>
            </div>

            <!-- Información y reserva -->
            <div class="flex flex-col justify-center" data-aos="fade-up" data-aos-delay="100">
                <?php if ($nombreCategoria !== ''): ?>
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-brand-700">
                        <i class="fa-solid <?= e($iconoCategoria) ?>"></i> <?= e($nombreCategoria) ?>
                    </span>
                <?php endif; ?>
                <h1 class="mt-4 font-display text-3xl md:text-4xl font-extrabold leading-tight tracking-tight text-ink-900">
                    <?= e($producto['name']) ?>
                </h1>

                <?php if (!empty($producto['description'])): ?>
                    <p class="mt-4 leading-relaxed text-ink-600"><?= nl2br(e($producto['description'])) ?></p>
                <?php endif; ?>

                <div class="mt-7 rounded-3xl border border-slate-100 bg-white p-5 shadow-soft sm:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-ink-400">Precio</p>
                            <p class="mt-1 font-display text-3xl font-extrabold text-ink-900"><?= e(etiquetaPrecio($producto['price'])) ?></p>
                            <p class="mt-1 text-xs text-ink-500">Se confirma al retirar en tienda.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-bold <?= $productoStock > 0 ? 'bg-brand-50 text-brand-700' : 'bg-rose-50 text-rose-600' ?>">
                            <i class="fa-solid <?= $productoStock > 0 ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                            <?= e(etiquetaDisponibilidad($productoStock)) ?>
                        </span>
                    </div>

                    <button type="button"
                            class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 py-4 text-sm font-bold text-white shadow-soft transition hover:bg-brand-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400"
                            data-agregar
                            data-id="<?= (int) $producto['id'] ?>"
                            data-nombre="<?= e($producto['name']) ?>"
                            data-precio="<?= e((float) $producto['price']) ?>"
                            data-stock="<?= $productoStock ?>"
                            data-imagen="<?= e($productoImagen) ?>"
                            <?= $productoStock <= 0 ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-cart-plus"></i>
                        <?= $productoStock > 0 ? 'Agregar al carrito' : 'Agotado temporalmente' ?>
                    </button>
                    <a href="/carrito/carrito.php" class="mt-3 flex items-center justify-center gap-2 text-sm font-semibold text-brand-700 hover:underline">
                        Ir a mi carrito <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <i class="fa-solid fa-list-check text-brand-500"></i>
                        <p class="mt-2 text-xs font-bold text-ink-900">Elige cantidad</p>
                        <p class="mt-1 text-[11px] text-ink-500">Ajusta tu pedido en el carrito.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <i class="fa-solid fa-calendar-day text-brand-500"></i>
                        <p class="mt-2 text-xs font-bold text-ink-900">Elige la fecha</p>
                        <p class="mt-1 text-[11px] text-ink-500">Indica cuándo quieres retirar.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <i class="fa-solid fa-handshake text-brand-500"></i>
                        <p class="mt-2 text-xs font-bold text-ink-900">Paga al retirar</p>
                        <p class="mt-1 text-[11px] text-ink-500">Sin pagos online.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
