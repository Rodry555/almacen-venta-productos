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
            contenedor.innerHTML = '<p class="text-slate-500 py-8 text-center">Tu carrito está vacío. <a class="text-blue-600 underline" href="/productos.php">Ver productos</a></p>';
        } else {
            contenedor.innerHTML = this.items.map((item) => `
                <div class="flex items-center gap-4 border-b border-slate-100 py-4">
                    <img src="${this.escape(item.imagen)}" alt="" class="w-16 h-16 rounded object-cover bg-slate-100">
                    <div class="flex-1">
                        <p class="font-semibold">${this.escape(item.nombre)}</p>
                        <p class="text-sm text-slate-500">$${item.precio.toFixed(2)} c/u · máx. ${item.stock}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" data-menos="${item.id}" class="w-8 h-8 rounded bg-slate-200 hover:bg-slate-300">−</button>
                        <input type="number" data-cantidad="${item.id}" value="${item.cantidad}" min="1" max="${item.stock}"
                               class="w-14 text-center border border-slate-300 rounded">
                        <button type="button" data-mas="${item.id}" class="w-8 h-8 rounded bg-slate-200 hover:bg-slate-300">+</button>
                    </div>
                    <span class="font-bold w-24 text-right">$${(item.precio * item.cantidad).toFixed(2)}</span>
                    <button type="button" data-quitar="${item.id}" class="text-red-500 hover:text-red-700" aria-label="Eliminar">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>`).join('');
        }

        const total = document.getElementById('carrito-total');
        if (total) total.textContent = '$' + this.obtenerTotal().toFixed(2);
        const boton = document.getElementById('btn-reservar');
        if (boton) boton.disabled = this.items.length === 0;
    }
};

document.addEventListener('DOMContentLoaded', () => Carrito.init());
window.Carrito = Carrito;
