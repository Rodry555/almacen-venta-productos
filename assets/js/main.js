// Utilidades generales del sitio público.
document.addEventListener('DOMContentLoaded', () => {
    const hoy = new Date().toISOString().split('T')[0];
    document.querySelectorAll('input[type="date"]').forEach((input) => {
        if (!input.min) input.min = hoy;
        if (!input.value) input.value = hoy;
    });
});

window.mensaje = function (texto, tipo = 'success') {
    if (typeof Toastify !== 'undefined') {
        const colores = { success: '#10b981', warning: '#f59e0b', error: '#ef4444' };
        Toastify({
            text: texto,
            duration: 3000,
            gravity: 'top',
            position: 'right',
            backgroundColor: colores[tipo] || colores.success
        }).showToast();
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: tipo, title: texto, toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
    }
};
