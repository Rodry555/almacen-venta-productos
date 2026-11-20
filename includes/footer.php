</main>
<footer class="bg-slate-800 text-slate-300 mt-12">
    <div class="max-w-7xl mx-auto px-4 py-8 grid gap-6 md:grid-cols-3 text-sm">
        <div>
            <h4 class="text-white font-bold mb-2">Mi Almacén</h4>
            <p>Catálogo de productos con reserva previa. Retiro en tienda.</p>
        </div>
        <div>
            <h4 class="text-white font-bold mb-2">Contacto</h4>
            <p><i class="fa-solid fa-envelope mr-2"></i>contacto@mialmacen.local</p>
            <p><i class="fa-solid fa-phone mr-2"></i>+00 000 000 000</p>
        </div>
        <div>
            <h4 class="text-white font-bold mb-2">Enlaces</h4>
            <p><a class="hover:text-white" href="/productos.php">Ver productos</a></p>
            <p><a class="hover:text-white" href="/carrito/carrito.php">Mi carrito</a></p>
        </div>
    </div>
    <div class="text-center text-xs text-slate-500 py-4 border-t border-slate-700">
        &copy; <?= date('Y') ?> Mi Almacén. Todos los derechos reservados.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="/assets/js/main.js"></script>
<script src="/assets/js/carrito.js"></script>
<?php foreach ($extraScripts as $src): ?>
    <script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
<script>
    if (window.AOS) AOS.init({ duration: 600, once: true });
</script>
</body>
</html>
