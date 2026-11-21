/* Carrito de reservas en localStorage (sin frameworks). */
const Carrito = {
    clave: 'carrito',
    items: [],

    init() {
        try {
            this.items = JSON.parse(localStorage.getItem(this.clave) || '[]');
        } catch (e) {
            this.items = [];
        }
        if (!Array.isArray(this.items)) this.items = [];

        this.actualizarContador();
        this.render();

        document.addEventListener('click', (e) => {
            const agregar = e.target.closest('[data-agregar]');
            if (agregar) { e.preventDefault(); this.agregarDesdeBoton(agregar); return; }

            const quitar = e.target.closest('[data-quitar]');
            if (quitar) { e.preventDefault(); this.eliminar(Number(quitar.dataset.quitar)); return; }

            const mas = e.target.closest('[data-mas]');
            if (mas) { e.preventDefault(); this.cambiar(Number(mas.dataset.mas), 1); return; }

            const menos = e.target.closest('[data-menos]');
            if (menos) { e.preventDefault(); this.cambiar(Number(menos.dataset.menos), -1); }
        });

        document.addEventListener('change', (e) => {
            const input = e.target.closest('[data-cantidad]');
            if (input) this.establecer(Number(input.dataset.cantidad), Number(input.value));
        });
    },

    agregarDesdeBoton(boton) {
        this.agregar({
            id: Number(boton.dataset.id),
            nombre: boton.dataset.nombre,
            precio: Number(boton.dataset.precio),
            stock: Number(boton.dataset.stock),
            imagen: boton.dataset.imagen
        });
    },

    agregar(producto) {
        const existente = this.items.find((item) => item.id === producto.id);
        if (existente) {
            if (existente.cantidad >= existente.stock) {
                window.mensaje('No hay más stock disponible de este producto', 'warning');
                return;
            }
            existente.cantidad++;
        } else {
            if (producto.stock < 1) {
                window.mensaje('Producto sin stock', 'warning');
                return;
            }
            this.items.push({ ...producto, cantidad: 1 });
        }
        this.guardar();
        window.mensaje('Producto agregado al carrito');
    },

    eliminar(id) {
        this.items = this.items.filter((item) => item.id !== id);
        this.guardar();
    },

    cambiar(id, delta) {
        const item = this.items.find((i) => i.id === id);
        if (!item) return;
        this.establecer(id, item.cantidad + delta);
    },

    establecer(id, cantidad) {
        const item = this.items.find((i) => i.id === id);
        if (!item) return;
        if (!Number.isFinite(cantidad) || cantidad < 1) { this.eliminar(id); return; }
        item.cantidad = Math.min(Math.floor(cantidad), item.stock);
        this.guardar();
    },

    guardar() {
        localStorage.setItem(this.clave, JSON.stringify(this.items));
        this.actualizarContador();
        this.render();
    },

    actualizarContador() {
        const contador = document.getElementById('carrito-contador');
        if (contador) {
            contador.textContent = this.items.reduce((sum, item) => sum + item.cantidad, 0);
            contador.classList.add('scale-125');
            setTimeout(() => contador.classList.remove('scale-125'), 180);
        }
    },

    obtenerTotal() {
        return this.items.reduce((sum, item) => sum + item.precio * item.cantidad, 0);
    },

    vaciar() {
        this.items = [];
        localStorage.removeItem(this.clave);
        this.actualizarContador();
        this.render();
    },

    escape(texto) {
        return String(texto).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[c]));
    },

    render() {
        const contenedor = document.getElementById('carrito-items');
        if (!contenedor) return;

        if (this.items.length === 0) {
            contenedor.innerHTML = `
                <div class="py-14 px-6 text-center">
                    <span class="w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-500 grid place-items-center text-3xl">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </span>
                    <p class="font-display font-bold text-lg text-ink-900 mt-4">Tu carrito está vacío</p>
                    <p class="text-sm text-ink-500 mt-1">Explora el catálogo y agrega los productos que quieras reservar.</p>
                    <a href="/productos.php" class="inline-flex mt-5 px-5 py-2.5 rounded-xl bg-ink-900 text-white text-sm font-semibold hover:bg-brand-700 transition">
                        <i class="fa-solid fa-arrow-right mr-2"></i> Ver productos
                    </a>
                </div>`;
        } else {
            contenedor.innerHTML = this.items.map((item) => `
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 border-b border-slate-100 py-5 last:border-0">
                    <div class="w-16 h-16 shrink-0 rounded-2xl bg-gradient-to-br from-brand-50 to-slate-100 overflow-hidden grid place-items-center">
                        <img src="${this.escape(item.imagen)}" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-ink-900 leading-snug">${this.escape(item.nombre)}</p>
                        <p class="text-xs text-ink-500 mt-1">${window.formatoPrecio(item.precio)} c/u · máx. ${item.stock}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" data-menos="${item.id}" aria-label="Quitar una unidad"
                                class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-brand-50 hover:border-brand-300 hover:text-brand-700 transition font-bold">−</button>
                        <input type="number" data-cantidad="${item.id}" value="${item.cantidad}" min="1" max="${item.stock}"
                               aria-label="Cantidad"
                               class="w-14 h-9 text-center border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <button type="button" data-mas="${item.id}" aria-label="Agregar una unidad"
                                class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-brand-50 hover:border-brand-300 hover:text-brand-700 transition font-bold">+</button>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-4 sm:w-32">
                        <span class="font-bold text-ink-900">${window.formatoPrecio(item.precio * item.cantidad)}</span>
                        <button type="button" data-quitar="${item.id}" class="text-rose-500 hover:text-rose-700 transition" aria-label="Eliminar producto">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>`).join('');
        }

        const cantidades = document.getElementById('carrito-cantidades');
        if (cantidades) {
            cantidades.textContent = this.items.reduce((sum, item) => sum + item.cantidad, 0);
        }
        const total = document.getElementById('carrito-total');
        if (total) total.textContent = window.formatoPrecio(this.obtenerTotal());
        const boton = document.getElementById('btn-reservar');
        if (boton) boton.disabled = this.items.length === 0;
    }
};

document.addEventListener('DOMContentLoaded', () => Carrito.init());
window.Carrito = Carrito;
