// Utilidades generales del sitio público.
document.addEventListener('DOMContentLoaded', () => {
    const hoy = new Date().toISOString().split('T')[0];
    document.querySelectorAll('input[type="date"]').forEach((input) => {
        if (!input.min) input.min = hoy;
        if (!input.value) input.value = hoy;
    });

    // Menú de navegación en móvil.
    const botonMenu = document.getElementById('btn-menu-publico');
    const menu = document.getElementById('menu-publico');
    if (botonMenu && menu) {
        const abrir = (abierto) => {
            menu.classList.toggle('hidden', !abierto);
            botonMenu.setAttribute('aria-expanded', String(abierto));
            botonMenu.innerHTML = `<i class="fa-solid fa-${abierto ? 'xmark' : 'bars'}"></i>`;
        };
        botonMenu.addEventListener('click', () => abrir(menu.classList.contains('hidden')));
        menu.querySelectorAll('a').forEach((enlace) => {
            enlace.addEventListener('click', () => abrir(false));
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) abrir(false);
        });
    }
});

/**
 * Precios reales cargados en la BD: formato siempre en bolivianos.
 * Si price = 0 (producto aún sin precio), mostrar "A consultar" en vez de "Bs 0.00".
 */
window.formatoPrecio = function (precio) {
    const numero = Number(precio) || 0;
    return numero > 0 ? 'Bs ' + numero.toFixed(2) : 'A consultar';
};

window.mensaje = function (texto, tipo = 'success') {
    if (typeof Toastify !== 'undefined') {
        const colores = { success: '#059669', warning: '#d97706', error: '#dc2626' };
        // El header es sticky: colocar el toast debajo de él para no tapar el carrito.
        const header = document.querySelector('header.sticky');
        const offsetY = header ? Math.round(header.getBoundingClientRect().bottom) + 12 : 88;
        Toastify({
            text: texto,
            duration: 3000,
            gravity: 'top',
            position: 'right',
            offset: { x: 0, y: offsetY },
            backgroundColor: colores[tipo] || colores.success,
            borderRadius: '12px',
        }).showToast();
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: tipo, title: texto, toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
    }
};
