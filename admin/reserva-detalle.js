/* Cambio de estado usando la API existente (devuelve stock al cancelar). */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#acciones-estado [data-estado]').forEach((boton) => {
        boton.addEventListener('click', async () => {
            const confirmacion = await Swal.fire({
                title: 'Confirmar acción',
                text: boton.dataset.pregunta,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Volver'
            });
            if (!confirmacion.isConfirmed) return;

            boton.disabled = true;
            try {
                const respuesta = await window.adminFetch('/api/actualizar-reserva.php', {
                    id: Number(boton.dataset.id),
                    status: boton.dataset.estado
                });
                if (respuesta.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Estado actualizado',
                        timer: 1600,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo actualizar',
                        html: (respuesta.errors || ['Error desconocido']).map((e) => `• ${e}`).join('<br>')
                    });
                    boton.disabled = false;
                }
            } catch (error) {
                Swal.fire('Error de conexión', 'Inténtalo de nuevo.', 'error');
                boton.disabled = false;
            }
        });
    });
});
