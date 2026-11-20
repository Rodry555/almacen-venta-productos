<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

verificarLogin();
$usuario = usuarioActual();
$pageTitle = $pageTitle ?? 'Panel';
$extraScripts = $extraScripts ?? [];
$seccion = $seccion ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title><?= e($pageTitle) ?> — Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/3.0.0/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex">
<!-- Sidebar -->
<aside id="sidebar" class="bg-slate-800 text-slate-200 w-64 min-h-screen flex-shrink-0 hidden md:flex flex-col fixed inset-y-0 left-0 z-30">
    <div class="px-6 py-5 border-b border-slate-700">
        <a href="/admin/dashboard.php" class="font-bold text-lg text-white block">
            <i class="fa-solid fa-boxes-stacked mr-2 text-emerald-400"></i>Mi Almacén
        </a>
        <span class="text-xs text-slate-400">Panel de administración</span>
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
        <?php
        $enlaces = [
            ['dashboard.php', 'Dashboard', 'fa-gauge-high', 'dashboard'],
            ['productos.php', 'Productos', 'fa-box', 'productos'],
            ['categorias.php', 'Categorías', 'fa-layer-group', 'categorias'],
            ['reservas.php', 'Reservas', 'fa-clipboard-list', 'reservas'],
        ];
        foreach ($enlaces as [$archivo, $etiqueta, $icono, $clave]):
            $activo = $seccion === $clave;
        ?>
            <a href="/admin/<?= $archivo ?>"
               class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $activo ? 'bg-blue-600 text-white' : 'hover:bg-slate-700' ?>">
                <i class="fa-solid <?= $icono ?> w-5 text-center"></i><?= $etiqueta ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="px-3 py-4 border-t border-slate-700">
        <p class="text-xs text-slate-400 px-3 mb-2 truncate"><?= e($usuario['email']) ?></p>
        <button id="btn-logout" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-sm">
            <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> Cerrar sesión
        </button>
        <a href="/index.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-sm mt-1">
            <i class="fa-solid fa-arrow-left w-5 text-center"></i> Ver catálogo
        </a>
    </div>
</aside>

<!-- Barra superior móvil -->
<div class="md:hidden bg-slate-800 text-white px-4 py-3 flex items-center justify-between w-full">
    <a href="/admin/dashboard.php" class="font-bold"><i class="fa-solid fa-boxes-stacked mr-2 text-emerald-400"></i>Mi Almacén</a>
    <button id="btn-menu" class="text-xl" aria-label="Abrir menú"><i class="fa-solid fa-bars"></i></button>
</div>
<div id="menu-movil" class="hidden md:hidden bg-slate-800 text-slate-200 px-4 pb-4 space-y-1 text-sm">
    <?php foreach ($enlaces as [$archivo, $etiqueta, $icono, $clave]): ?>
        <a href="/admin/<?= $archivo ?>" class="block px-3 py-2 rounded hover:bg-slate-700">
            <i class="fa-solid <?= $icono ?> w-5"></i><?= $etiqueta ?>
        </a>
    <?php endforeach; ?>
    <button id="btn-logout-movil" class="block w-full text-left px-3 py-2 rounded hover:bg-slate-700">
        <i class="fa-solid fa-right-from-bracket w-5"></i>Cerrar sesión
    </button>
</div>

<div class="flex-1 md:ml-64 min-w-0">
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <h1 class="text-xl font-bold"><?= e($pageTitle) ?></h1>
        <span class="text-sm text-slate-500"><i class="fa-solid fa-user mr-2"></i><?= e($usuario['name']) ?></span>
    </header>
    <main class="p-6">
