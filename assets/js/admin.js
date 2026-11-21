/* Utilidades compartidas del panel admin. */
document.addEventListener('DOMContentLoaded', () => {
    // Logout mediante la API existente.
    const logout = async () => {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        try {
            await fetch('/api/logout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: token })
            });
        } finally {
            window.location.href = '/login.php';
        }
    };
    document.getElementById('btn-logout')?.addEventListener('click', logout);
    document.getElementById('btn-logout-movil')?.addEventListener('click', logout);

    // Menú móvil.
    const botonMenu = document.getElementById('btn-menu');
    const menuMovil = document.getElementById('menu-movil');
    botonMenu?.addEventListener('click', () => {
        const abierto = menuMovil?.classList.toggle('hidden') === false;
        botonMenu.setAttribute('aria-expanded', String(abierto));
        botonMenu.innerHTML = `<i class="fa-solid fa-${abierto ? 'xmark' : 'bars'}"></i>`;
    });

    // Confirmación para acciones destructivas en formularios clásicos.
    document.querySelectorAll('[data-confirmar]').forEach((boton) => {
        boton.addEventListener('click', async (event) => {
            event.preventDefault();
            const resultado = await Swal.fire({
                title: '¿Estás seguro?',
                text: boton.dataset.confirmar,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            });
            if (resultado.isConfirmed) {
                boton.closest('form')?.submit();
            }
        });
    });
});

window.adminMensaje = function (texto, icono = 'success') {
    Swal.fire({ icon: icono, title: texto, timer: 2000, showConfirmButton: false });
};

/** Petición JSON con token CSRF, usada por los módulos del panel. */
window.adminFetch = async function (url, datos) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const respuesta = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...datos, csrf_token: token })
    });
    return respuesta.json();
};
