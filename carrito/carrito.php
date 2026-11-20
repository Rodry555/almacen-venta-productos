<?php
declare(strict_types=1);
$pageTitle = 'Mi carrito';
$extraScripts = ['/carrito/reserva.js'];
include __DIR__ . '/../includes/header.php';
?>
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-2"><i class="fa-solid fa-cart-shopping mr-2 text-blue-600"></i>Mi carrito</h1>
    <p class="text-slate-500 mb-8">Revisa las cantidades y completa tus datos para reservar.</p>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-4 md:p-6">
            <div id="carrito-items"></div>
        </div>

        <aside class="bg-white rounded-2xl shadow-sm p-6 h-fit">
            <h2 class="font-bold text-lg mb-4">Resumen</h2>
            <div class="flex justify-between text-slate-600 mb-2">
                <span>Productos</span><span id="carrito-cantidades">0</span>
            </div>
            <div class="flex justify-between font-bold text-xl mt-4 pt-4 border-t">
                <span>Total estimado</span><span id="carrito-total">$0.00</span>
            </div>
            <p class="text-xs text-slate-400 mt-2">El total se confirma al retirar.</p>
        </aside>
    </div>

    <form id="form-reserva" class="bg-white rounded-2xl shadow-sm p-6 mt-8 max-w-3xl">
        <h2 class="font-bold text-xl mb-5">Datos para la reserva</h2>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="text-sm font-semibold">Nombre completo *</span>
                <input id="cliente-nombre" required minlength="3" maxlength="255" autocomplete="name"
                       class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </label>
            <label class="block">
                <span class="text-sm font-semibold">Email *</span>
                <input id="cliente-email" type="email" required autocomplete="email"
                       class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </label>
            <label class="block">
                <span class="text-sm font-semibold">Teléfono *</span>
                <input id="cliente-telefono" type="tel" required pattern="[0-9+\s\-()]{7,50}"
                       autocomplete="tel" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </label>
            <label class="block">
                <span class="text-sm font-semibold">Fecha deseada de retiro *</span>
                <input id="cliente-fecha" type="date" required
                       class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </label>
        </div>
        <label class="block mt-4">
            <span class="text-sm font-semibold">Notas (opcional)</span>
            <textarea id="cliente-notas" rows="3" maxlength="1000"
                      class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"></textarea>
        </label>
        <button id="btn-reservar" type="submit"
                class="mt-6 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-8 rounded-lg disabled:bg-slate-300 disabled:cursor-not-allowed">
            <i class="fa-solid fa-check mr-2"></i> Confirmar reserva
        </button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
