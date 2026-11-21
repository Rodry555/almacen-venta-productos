<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

// Si ya hay sesión, entra directo al panel.
if (usuarioActual() !== null) {
    header('Location: /admin/dashboard.php');
    exit;
}
$pageTitle = 'Acceso administrador';
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
</head>
<body class="bg-slate-800 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center text-white mb-6">
            <i class="fa-solid fa-boxes-stacked text-5xl text-emerald-400"></i>
            <h1 class="text-2xl font-bold mt-3">Panel de Administración</h1>
            <p class="text-slate-400 text-sm mt-1">Ingresa con tu cuenta de administrador</p>
        </div>

        <form id="form-login" class="bg-white rounded-2xl shadow-xl p-8">
            <?= csrfField() ?>
            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Email</span>
                <input id="login-email" type="email" required autocomplete="username"
                       value="admin@gmail.com"
                       class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </label>
            <label class="block mt-4">
                <span class="text-sm font-semibold text-slate-700">Contraseña</span>
                <input id="login-password" type="password" required autocomplete="current-password"
                       class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </label>
            <button id="btn-login" type="submit"
                    class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Ingresar
            </button>
        </form>

        <p class="text-center text-slate-500 text-xs mt-6">
            <a href="/index.php" class="hover:text-white">← Volver al catálogo</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.getElementById('form-login').addEventListener('submit', async (event) => {
        event.preventDefault();
        const boton = document.getElementById('btn-login');
        boton.disabled = true;
        boton.textContent = 'Verificando...';

        try {
            const respuesta = await fetch('/api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content,
                    email: document.getElementById('login-email').value.trim(),
                    password: document.getElementById('login-password').value
                })
            });
            const datos = await respuesta.json();
            if (datos.success) {
                window.location.href = '/admin/dashboard.php';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo ingresar',
                    html: (datos.errors || ['Error desconocido']).map((e) => `• ${e}`).join('<br>')
                });
            }
        } catch (error) {
            Swal.fire('Error de conexión', 'Inténtalo de nuevo en unos segundos.', 'error');
        } finally {
            boton.disabled = false;
            boton.innerHTML = '<i class="fa-solid fa-right-to-bracket mr-2"></i> Ingresar';
        }
    });
    </script>
</body>
</html>
