/* Envío de la reserva usando la API existente. */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-reserva');
    if (!form) return;

    const fecha = document.getElementById('cliente-fecha');
    const hoy = new Date().toISOString().split('T')[0];
    if (fecha) {
        fecha.min = hoy;
        if (!fecha.value) fecha.value = hoy;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!window.Carrito || window.Carrito.items.length === 0) {
            Swal.fire('Carrito vacío', 'Agrega productos antes de reservar.', 'warning');
            return;
        }

        const telefono = document.getElementById('cliente-telefono').value.trim();
        if (!/^[67][0-9]{7}$/.test(telefono)) {
            Swal.fire('Teléfono inválido', 'Debe tener 8 dígitos y empezar con 6 o 7.', 'warning');
            document.getElementById('cliente-telefono').focus();
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').content;
        const body = {
            csrf_token: token,
            customer_name: document.getElementById('cliente-nombre').value.trim(),
            customer_phone: telefono,
            pickup_date: fecha.value,
            notes: document.getElementById('cliente-notas').value.trim(),
            items: window.Carrito.items.map((item) => ({ id: item.id, cantidad: item.cantidad }))
        };

        const boton = document.getElementById('btn-reservar');
        boton.disabled = true;
        boton.textContent = 'Procesando...';

        try {
            const respuesta = await fetch('/api/agregar-reserva.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const datos = await respuesta.json();

            if (datos.success) {
                window.Carrito.vaciar();
                const resultado = await Swal.fire({
                    icon: 'success',
                    title: '¡Reserva creada!',
                    html: `Número de reserva: <strong>#${datos.data.reservation_id}</strong><br>
                           Retiro: <strong>${body.pickup_date}</strong>`,
                    confirmButtonText: 'Entendido'
                });
                if (resultado.isConfirmed) window.location.href = '/index.php';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo crear la reserva',
                    html: (datos.errors || ['Error desconocido']).map((e) => `• ${e}`).join('<br>')
                });
            }
        } catch (error) {
            Swal.fire('Error de conexión', 'Inténtalo de nuevo en unos segundos.', 'error');
        } finally {
            boton.disabled = false;
            boton.innerHTML = '<i class="fa-solid fa-check mr-2"></i> Confirmar reserva';
        }
    });
});
