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

require_once __DIR__ . '/../includes/admin-header.php';
?>

<p class="text-sm text-slate-500 mb-6">Resumen general del sistema. Esta vista es de solo lectura.</p>

<!-- Resumen general -->
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Productos activos</span>
            <i class="fa-solid fa-box text-blue-500 text-xl"></i>
        </div>
        <p class="text-3xl font-bold mt-2"><?= $totalProductos ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Stock total (unidades)</span>
            <i class="fa-solid fa-warehouse text-emerald-500 text-xl"></i>
        </div>
        <p class="text-3xl font-bold mt-2"><?= number_format($stockTotal) ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Reservas pendientes</span>
            <i class="fa-solid fa-clock text-amber-500 text-xl"></i>
        </div>
        <p class="text-3xl font-bold mt-2"><?= $reservasPendientes ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Productos con stock bajo</span>
            <i class="fa-solid fa-triangle-exclamation text-red-500 text-xl"></i>
        </div>
        <p class="text-3xl font-bold mt-2"><?= count($stockBajo) ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Reservas de hoy</span>
            <i class="fa-solid fa-calendar-day text-blue-500 text-xl"></i>
        </div>
        <p class="text-3xl font-bold mt-2"><?= $reservasHoy ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Reservas (últimos 7 días)</span>
            <i class="fa-solid fa-calendar-week text-blue-500 text-xl"></i>
        </div>
        <p class="text-3xl font-bold mt-2"><?= $reservasSemana ?></p>
    </div>
</div>

<!-- Alertas de stock bajo -->
<?php if ($stockBajo): ?>
    <section class="mt-8 bg-white rounded-xl shadow-sm overflow-hidden">
        <h2 class="font-bold px-6 py-4 border-b border-slate-100 text-red-700">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>Productos con stock bajo
        </h2>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr><th class="text-left px-6 py-2">Producto</th><th class="text-left px-6 py-2">Stock actual</th><th class="text-left px-6 py-2">Mínimo</th></tr>
            </thead>
            <tbody>
                <?php foreach ($stockBajo as $item): ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-6 py-2"><?= e($item['name']) ?></td>
                        <td class="px-6 py-2 font-bold <?= (int) $item['stock'] === 0 ? 'text-red-600' : 'text-amber-600' ?>"><?= (int) $item['stock'] ?></td>
                        <td class="px-6 py-2 text-slate-500"><?= (int) $item['stock_min'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>

<!-- Últimos movimientos de stock -->
<section class="mt-8 bg-white rounded-xl shadow-sm overflow-hidden">
    <h2 class="font-bold px-6 py-4 border-b border-slate-100">Últimos movimientos de stock</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-left px-6 py-2">Fecha</th>
                    <th class="text-left px-6 py-2">Producto</th>
                    <th class="text-left px-6 py-2">Tipo</th>
                    <th class="text-left px-6 py-2">Cantidad</th>
                    <th class="text-left px-6 py-2">Motivo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$ultimosMovimientos): ?>
                    <tr><td colspan="5" class="px-6 py-6 text-center text-slate-400">Todavía no hay movimientos.</td></tr>
                <?php endif; ?>
                <?php foreach ($ultimosMovimientos as $m): ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-6 py-2 text-slate-500 whitespace-nowrap"><?= e($m['created_at']) ?></td>
                        <td class="px-6 py-2"><?= e($m['name']) ?></td>
                        <td class="px-6 py-2"><span class="px-2 py-1 rounded text-xs bg-slate-100"><?= e($m['type']) ?></span></td>
                        <td class="px-6 py-2 font-semibold <?= (int) $m['quantity'] < 0 ? 'text-red-600' : 'text-emerald-600' ?>">
                            <?= (int) $m['quantity'] > 0 ? '+' : '' ?><?= (int) $m['quantity'] ?>
                        </td>
                        <td class="px-6 py-2 text-slate-500"><?= e($m['reason']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Últimas reservas -->
<section class="mt-8 bg-white rounded-xl shadow-sm overflow-hidden">
    <h2 class="font-bold px-6 py-4 border-b border-slate-100">Últimas reservas</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-left px-6 py-2">ID</th>
                    <th class="text-left px-6 py-2">Cliente</th>
                    <th class="text-left px-6 py-2">Retiro</th>
                    <th class="text-left px-6 py-2">Estado</th>
                    <th class="text-left px-6 py-2">Productos</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$ultimasReservas): ?>
                    <tr><td colspan="5" class="px-6 py-6 text-center text-slate-400">Todavía no hay reservas.</td></tr>
                <?php endif; ?>
                <?php foreach ($ultimasReservas as $r): ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-6 py-2 font-semibold">#<?= (int) $r['id'] ?></td>
                        <td class="px-6 py-2"><?= e($r['customer_name']) ?></td>
                        <td class="px-6 py-2 text-slate-500"><?= e($r['pickup_date']) ?></td>
                        <td class="px-6 py-2">
                            <span class="px-2 py-1 rounded text-xs <?= $estilosEstado[$r['status']] ?? 'bg-slate-100' ?>">
                                <?= e($etiquetasEstado[$r['status']] ?? $r['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-2 text-slate-500"><?= (int) $r['total_items'] ?> items</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
