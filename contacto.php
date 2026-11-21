<?php
declare(strict_types=1);
$pageTitle = 'Contacto';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-10 md:py-14">
    <div data-aos="fade-up">
        <nav class="mb-4 flex items-center gap-2 text-xs text-ink-500">
            <a href="/index.php" class="hover:text-brand-700">Inicio</a>
            <i class="fa-solid fa-chevron-right text-[9px]"></i>
            <span class="font-semibold text-ink-800">Contacto</span>
        </nav>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Estamos para ayudarte</p>
        <h1 class="mt-2 font-display text-3xl md:text-5xl font-extrabold tracking-tight text-ink-900">Contacto</h1>
        <p class="mt-3 max-w-2xl text-ink-500">Ante cualquier duda sobre una reserva o un producto, escríbenos o llámanos.</p>
    </div>

    <div class="mt-9 grid gap-5 md:grid-cols-3">
        <?php
        $contactos = [
            ['fa-envelope', 'Email', 'contacto@mialmacen.local', 'Respuestas en horario hábil', 'bg-brand-50 text-brand-600'],
            ['fa-phone', 'Teléfono', '+00 000 000 000', 'Lun a Vie, 9:00 – 18:00', 'bg-sky-50 text-sky-600'],
            ['fa-clock', 'Horario', '9:00 – 18:00', 'Retiros de lunes a viernes', 'bg-amber-50 text-amber-600'],
        ];
        foreach ($contactos as $i => [$icono, $titulo, $valor, $detalle, $tono]): ?>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-soft hover:shadow-lift transition" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                <span class="w-12 h-12 rounded-2xl grid place-items-center text-xl <?= e($tono) ?>">
                    <i class="fa-solid <?= e($icono) ?>"></i>
                </span>
                <h2 class="mt-5 font-display text-lg font-bold text-ink-900"><?= e($titulo) ?></h2>
                <p class="mt-1 font-semibold text-ink-800"><?= e($valor) ?></p>
                <p class="mt-1 text-xs text-ink-500"><?= e($detalle) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <section id="como-funciona" class="mt-12 grid gap-7 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-3xl bg-gradient-to-br from-ink-900 to-brand-900 p-7 text-white sm:p-9" data-aos="fade-up">
            <span class="w-12 h-12 rounded-2xl bg-white/10 text-brand-300 grid place-items-center text-xl">
                <i class="fa-solid fa-circle-question"></i>
            </span>
            <h2 class="mt-5 font-display text-2xl sm:text-3xl font-extrabold">¿Cómo funciona una reserva?</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-300">
                Es un catálogo con reserva previa, no una tienda con pagos online.
                Tu pedido queda apartado y pagas cuando lo retiras.
            </p>
            <a href="/productos.php" class="mt-7 inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-bold text-ink-950 hover:bg-brand-400 transition">
                <i class="fa-solid fa-table-cells"></i> Ver productos
            </a>
        </div>

        <ol class="rounded-3xl border border-slate-100 bg-white p-6 shadow-soft sm:p-8" data-aos="fade-up" data-aos-delay="100">
            <?php
            $pasos = [
                'Agregas productos al carrito indicando la cantidad.',
                'Completas tus datos y la fecha deseada de retiro.',
                'El stock queda apartado automáticamente.',
                'Confirmamos la reserva y tú pagas al retirar en tienda.',
            ];
            foreach ($pasos as $i => $paso): ?>
                <li class="flex gap-4 <?= $i < count($pasos) - 1 ? 'border-b border-slate-100 pb-5 mb-5' : '' ?>">
                    <span class="w-9 h-9 shrink-0 rounded-xl bg-brand-50 text-brand-700 grid place-items-center font-display font-extrabold">
                        <?= $i + 1 ?>
                    </span>
                    <p class="pt-1.5 text-sm leading-relaxed text-ink-700"><?= e($paso) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
