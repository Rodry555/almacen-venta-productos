<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

// La sesión debe iniciarse ANTES de imprimir cualquier HTML: así se puede
// enviar la cookie de sesión y el token CSRF funciona en la primera visita.
csrfToken();

$pageTitle = $pageTitle ?? 'Inicio';
$extraScripts = $extraScripts ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title><?= e($pageTitle) ?> — Almacén</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">
<header class="bg-slate-800 text-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/index.php" class="font-bold text-xl tracking-tight">
            <i class="fa-solid fa-boxes-stacked mr-2 text-emerald-400"></i>Mi Almacén
        </a>
        <nav class="flex items-center gap-4 sm:gap-6 text-sm">
            <a href="/index.php" class="hover:text-emerald-400">Inicio</a>
            <a href="/productos.php" class="hover:text-emerald-400">Productos</a>
            <a href="/contacto.php" class="hover:text-emerald-400">Contacto</a>
            <a href="/carrito/carrito.php" class="relative hover:text-emerald-400" aria-label="Ver carrito">
                <i class="fa-solid fa-cart-shopping text-lg"></i>
                <span id="carrito-contador"
                      class="absolute -top-2 -right-3 bg-emerald-500 text-white text-xs font-bold rounded-full h-5 min-w-5 px-1 flex items-center justify-center">0</span>
            </a>
        </nav>
    </div>
</header>
<main class="flex-1">
