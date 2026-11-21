// Filtrado rápido en la galería (además de los filtros del servidor).
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('filtro-rapido');
    if (!buscador) return;

    const tarjetas = Array.from(document.querySelectorAll('.producto-card'));
    const sinResultados = document.getElementById('sin-resultados-rapidos');

    buscador.addEventListener('input', () => {
        const texto = buscador.value.trim().toLowerCase();
        let visibles = 0;

        tarjetas.forEach((card) => {
            const coincide = card.textContent.toLowerCase().includes(texto);
            card.style.display = coincide ? '' : 'none';
            if (coincide) visibles++;
        });

        if (sinResultados) {
            sinResultados.classList.toggle('hidden', visibles > 0 || texto === '');
        }
    });
});
