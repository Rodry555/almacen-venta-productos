    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="/assets/js/admin.js"></script>
<?php foreach ($extraScripts as $src): ?>
    <script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
<?php
$flash = flashLeer();
if ($flash !== []): ?>
    <script>
        Swal.fire({
            icon: <?= json_encode($flash['tipo'] ?? 'success') ?>,
            title: <?= json_encode($flash['mensaje'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
            timer: 2500,
            showConfirmButton: false
        });
    </script>
<?php endif; ?>
</body>
</html>
