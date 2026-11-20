<?php
declare(strict_types=1);
$pageTitle = 'Contacto';
include __DIR__ . '/includes/header.php';
?>
<div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-2">Contacto</h1>
    <p class="text-slate-500 mb-8">Ante cualquier duda sobre una reserva, escríbenos.</p>
    <div class="grid gap-6 md:grid-cols-3">
        <div class="bg-white rounded-xl shadow-sm p-6" data-aos="fade-up">
            <i class="fa-solid fa-envelope text-blue-600 text-2xl mb-3"></i>
            <h2 class="font-bold">Email</h2>
            <p class="text-slate-600 text-sm mt-1">contacto@mialmacen.local</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6" data-aos="fade-up">
            <i class="fa-solid fa-phone text-blue-600 text-2xl mb-3"></i>
            <h2 class="font-bold">Teléfono</h2>
            <p class="text-slate-600 text-sm mt-1">+00 000 000 000</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6" data-aos="fade-up">
            <i class="fa-solid fa-clock text-blue-600 text-2xl mb-3"></i>
            <h2 class="font-bold">Horario</h2>
            <p class="text-slate-600 text-sm mt-1">Lun a Vie, 9:00 – 18:00</p>
        </div>
    </div>
    <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-bold mb-3">¿Cómo funciona una reserva?</h2>
        <ol class="list-decimal list-inside text-slate-600 space-y-2">
            <li>Agregas productos al carrito indicando la cantidad.</li>
            <li>Completas tus datos y la fecha deseada de retiro.</li>
            <li>El stock queda apartado automáticamente.</li>
            <li>Confirmamos la reserva y tú pagas al retirar en tienda.</li>
        </ol>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
