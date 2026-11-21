<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Dashboard';
$seccion = 'dashboard';

$pdo = db();

// --- Tarjetas de estadísticas (solo lectura) ---
$totalProductos = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
$stockTotal = (int) $pdo->query('SELECT COALESCE(SUM(stock), 0) FROM products WHERE is_active = 1')->fetchColumn();
$reservasPendientes = (int) $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
$stockBajo = $pdo->query(
    'SELECT id, name, stock, stock_min FROM products
     WHERE is_active = 1 AND stock <= stock_min ORDER BY stock ASC, name ASC'
)->fetchAll();
$reservasHoy = (int) $pdo->query('SELECT COUNT(*) FROM reservations WHERE DATE(created_at) = CURDATE()')->fetchColumn();
$reservasSemana = (int) $pdo->query(
    'SELECT COUNT(*) FROM reservations WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)'
)->fetchColumn();

// --- Últimos movimientos (10) ---
$ultimosMovimientos = $pdo->query(
    'SELECT sm.created_at, p.name, sm.type, sm.quantity, sm.reason
     FROM stock_movements sm
     JOIN products p ON sm.product_id = p.id
     ORDER BY sm.created_at DESC
     LIMIT 10'
)->fetchAll();

// --- Últimas reservas (5). Subconsulta para no depender de ONLY_FULL_GROUP_BY en TiDB. ---
$ultimasReservas = $pdo->query(
    'SELECT r.id, r.customer_name, r.pickup_date, r.status,
            (SELECT COUNT(*) FROM reservation_items ri WHERE ri.reservation_id = r.id) AS total_items
     FROM reservations r
     ORDER BY r.created_at DESC
     LIMIT 5'
)->fetchAll();

$estilosEstado = [
    'pending'   => 'bg-amber-100 text-amber-700',
    'confirmed' => 'bg-blue-100 text-blue-700',
    'cancelled' => 'bg-red-100 text-red-700',
    'completed' => 'bg-emerald-100 text-emerald-700',
];
$etiquetasEstado = [
    'pending'   => 'Pendiente',
    'confirmed' => 'Confirmada',
    'cancelled' => 'Cancelada',
    'completed' => 'Completada',
];

// Solo presentación: cada tarjeta lleva su tono e icono.
$tarjetas = [
    ['Productos activos', $totalProductos, 'fa-box', 'bg-brand-50 text-brand-600'],
    ['Stock total (unidades)', number_format($stockTotal), 'fa-warehouse', 'bg-sky-50 text-sky-600'],
    ['Reservas pendientes', $reservasPendientes, 'fa-clock', 'bg-amber-50 text-amber-600'],
    ['Productos con stock bajo', count($stockBajo), 'fa-triangle-exclamation', 'bg-rose-50 text-rose-600'],
    ['Reservas de hoy', $reservasHoy, 'fa-calendar-day', 'bg-violet-50 text-violet-600'],
    ['Reservas (últimos 7 días)', $reservasSemana, 'fa-calendar-week', 'bg-cyan-50 text-cyan-600'],
];

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <p class="text-sm text-ink-500">Resumen general del sistema. Esta vista es de solo lectura.</p>
        <p class="text-xs text-ink-400 mt-1">Datos actualizados al momento de cargar la página.</p>
    </div>
    <span class="inline-flex items-center gap-2 self-start rounded-full border border-brand-100 bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700">
        <i class="fa-solid fa-eye"></i> Solo lectura
    </span>
</div>

<!-- Resumen general -->
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($tarjetas as [$etiqueta, $valor, $icono, $tono]): ?>
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-soft">
            <div class="flex items-center justify-between gap-3">
                <span class="text-xs font-semibold text-ink-500"><?= e($etiqueta) ?></span>
                <span class="w-10 h-10 rounded-2xl grid place-items-center text-lg <?= e($tono) ?>">
                    <i class="fa-solid <?= e($icono) ?>"></i>
                </span>
            </div>
            <p class="mt-3 font-display text-3xl font-extrabold tracking-tight text-ink-900"><?= e((string) $valor) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Alertas de stock bajo -->
<?php if ($stockBajo): ?>
    <section class="mt-8 overflow-hidden rounded-3xl border border-rose-100 bg-white shadow-soft">
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 grid place-items-center"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <h2 class="font-display font-bold text-ink-900">Productos con stock bajo</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-ink-50 text-[11px] uppercase tracking-wider text-ink-500">
                    <tr><th class="text-left px-6 py-3">Producto</th><th class="text-left px-6 py-3">Stock actual</th><th class="text-left px-6 py-3">Mínimo</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($stockBajo as $item): ?>
                        <tr class="border-t border-slate-100 hover:bg-rose-50/40">
                            <td class="px-6 py-3"><?= e($item['name']) ?></td>
                            <td class="px-6 py-3 font-bold <?= (int) $item['stock'] === 0 ? 'text-rose-600' : 'text-amber-600' ?>"><?= (int) $item['stock'] ?></td>
                            <td class="px-6 py-3 text-ink-500"><?= (int) $item['stock_min'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<div class="mt-8 grid gap-6 xl:grid-cols-2">
    <!-- Últimos movimientos de stock -->
    <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-soft">
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 grid place-items-center"><i class="fa-solid fa-arrow-right-arrow-left"></i></span>
            <h2 class="font-display font-bold text-ink-900">Últimos movimientos de stock</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-ink-50 text-[11px] uppercase tracking-wider text-ink-500">
                    <tr>
                        <th class="text-left px-4 py-3">Fecha</th>
                        <th class="text-left px-4 py-3">Producto</th>
                        <th class="text-left px-4 py-3">Tipo</th>
                        <th class="text-left px-4 py-3">Cantidad</th>
                        <th class="text-left px-4 py-3">Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$ultimosMovimientos): ?>
                        <tr><td colspan="5" class="px-4 py-8 text-center text-ink-400">Todavía no hay movimientos.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($ultimosMovimientos as $m): ?>
                        <tr class="border-t border-slate-100 hover:bg-cream">
                            <td class="px-4 py-3 text-xs text-ink-500 whitespace-nowrap"><?= e($m['created_at']) ?></td>
                            <td class="px-4 py-3 font-medium text-ink-800"><?= e($m['name']) ?></td>
                            <td class="px-4 py-3"><span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold"><?= e($m['type']) ?></span></td>
                            <td class="px-4 py-3 font-bold <?= (int) $m['quantity'] < 0 ? 'text-rose-600' : 'text-brand-600' ?>">
                                <?= (int) $m['quantity'] > 0 ? '+' : '' ?><?= (int) $m['quantity'] ?>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-500"><?= e($m['reason']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Últimas reservas -->
    <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-soft">
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 grid place-items-center"><i class="fa-solid fa-clipboard-list"></i></span>
            <h2 class="font-display font-bold text-ink-900">Últimas reservas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-ink-50 text-[11px] uppercase tracking-wider text-ink-500">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Cliente</th>
                        <th class="text-left px-4 py-3">Retiro</th>
                        <th class="text-left px-4 py-3">Estado</th>
                        <th class="text-left px-4 py-3">Productos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$ultimasReservas): ?>
                        <tr><td colspan="5" class="px-4 py-8 text-center text-ink-400">Todavía no hay reservas.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($ultimasReservas as $r): ?>
                        <tr class="border-t border-slate-100 hover:bg-cream">
                            <td class="px-4 py-3 font-bold text-ink-900">#<?= (int) $r['id'] ?></td>
                            <td class="px-4 py-3"><?= e($r['customer_name']) ?></td>
                            <td class="px-4 py-3 text-xs text-ink-500"><?= e($r['pickup_date']) ?></td>
                            <td class="px-4 py-3">
                                <span class="rounded-lg px-2 py-1 text-xs font-bold <?= $estilosEstado[$r['status']] ?? 'bg-slate-100' ?>">
                                    <?= e($etiquetasEstado[$r['status']] ?? $r['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-500"><?= (int) $r['total_items'] ?> items</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
