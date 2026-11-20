// Filtrado rápido en la galería (además de los filtros del servidor).
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('filtro-rapido');
    if (!buscador) return;
    buscador.addEventListener('input', () => {
        const texto = buscador.value.trim().toLowerCase();
        document.querySelectorAll('.producto-card').forEach((card) => {
            card.style.display = card.textContent.toLowerCase().includes(texto) ? '' : 'none';
        });
    });
});
