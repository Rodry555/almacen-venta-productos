# AGENTS.md — Sistema de Almacén y Venta de Productos

## 📋 Descripción del Proyecto

Sistema web para gestión de almacén y venta de productos. Consiste en un **catálogo público** donde los clientes pueden ver productos disponibles y sus cantidades, con un **carrito de reservas** que permite solicitar productos mediante un formulario. Incluye un **panel de administración** para gestionar productos, stock y reservas.

**Importante:** No es un e-commerce con pagos online. Es un catálogo con sistema de reservas.

---

## 🛠️ Stack Tecnológico

| Tecnología | Uso |
|------------|-----|
| **HTML5** | Estructura |
| **CSS** | Estilos con **Tailwind CSS** (vía CDN) |
| **JavaScript Vanilla** | Interactividad, carrito, validaciones |
| **PHP 8+** | Backend, API, lógica de negocio |
| **MySQL** | Base de datos (TiDB Cloud - compatible MySQL) |
| **Cloudinary** | Almacenamiento de imágenes |

### Librerías externas permitidas (vía CDN)

- **Tailwind CSS** → Estilos y diseño responsive
- **Font Awesome 6** → Iconos
- **SweetAlert2** → Alertas y confirmaciones
- **DataTables** → Tablas con búsqueda y paginación (admin)
- **Toastify** → Notificaciones toast
- **AOS (Animate On Scroll)** → Animaciones al hacer scroll
- **PHPMailer** → Envío de emails (opcional)

---

## 🚀 Deploy

- **Hosting:** Render (free tier) — se despliega con **`Dockerfile`** (Render no tiene runtime nativo PHP)
- **Base de datos:** TiDB Cloud (free tier) — conexión vía MySQL protocol
- **Imágenes:** Cloudinary (free tier)

---

## 📁 Estructura de Archivos

```
/
├── index.php                    # Página de inicio
├── productos.php                # Galería de productos con filtros
├── producto-detalle.php         # Detalle de un producto (?id=X)
├── contacto.php                 # Página de contacto
│
├── /carrito/
│   └── carrito.php              # Ver carrito + formulario de reserva
│
├── /admin/
│   ├── login.php                # Login del admin
│   ├── dashboard.php            # Dashboard SOLO LECTURA (estadísticas)
│   ├── productos.php            # Lista de productos (CRUD + ajuste stock)
│   ├── producto-form.php        # Crear/editar producto
│   ├── categorias.php           # Gestión de categorías
│   ├── reservas.php             # Lista de reservas
│   └── reserva-detalle.php      # Detalle de una reserva
│
├── /api/
│   ├── agregar-reserva.php      # POST - Crear reserva
│   ├── actualizar-stock.php     # POST - Ajustar stock
│   ├── actualizar-reserva.php   # POST - Cambiar estado de reserva
│   ├── subir-imagen.php         # POST - Subir imagen a Cloudinary
│   └── eliminar-imagen.php      # POST - Eliminar imagen de Cloudinary
│
├── /includes/
│   ├── db.php                   # Conexión PDO a TiDB Cloud
│   ├── auth.php                 # Verificación de sesión admin
│   ├── header.php               # Header común (público)
│   ├── footer.php               # Footer común
│   ├── admin-header.php         # Header del panel admin
│   ├── admin-footer.php         # Footer del panel admin
│   └── functions.php            # Funciones utilitarias
│
├── /assets/
│   ├── /css/
│   │   └── custom.css           # Estilos personalizados (si son necesarios)
│   ├── /js/
│   │   ├── carrito.js           # Lógica del carrito (localStorage)
│   │   ├── productos.js         # Filtrado y galería
│   │   ├── admin.js             # Funciones del panel admin
│   │   └── main.js              # Funciones generales
│   └── /img/
│       └── (recursos estáticos)
│
├── /uploads/                    # Temporal para imágenes antes de Cloudinary
│
├── .env                         # Variables de entorno (NO subir a git)
└── .env.example                 # Ejemplo de variables de entorno
```

---

## 🗄️ Modelo de Datos (MySQL)

```sql
-- Usuarios administradores
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,  -- Usar password_hash()
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categorías de productos
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Productos
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  stock_min INT DEFAULT 5,
  category_id INT,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Imágenes de productos (almacenadas en Cloudinary)
CREATE TABLE product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  image_public_id VARCHAR(255) NOT NULL,
  is_primary BOOLEAN DEFAULT FALSE,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Reservas de clientes
CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(255) NOT NULL,
  customer_email VARCHAR(255) NOT NULL,
  customer_phone VARCHAR(50) NOT NULL,
  pickup_date DATE NOT NULL,
  notes TEXT,
  status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Items de cada reserva
CREATE TABLE reservation_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Movimientos de stock (trazabilidad - HISTORIAL DE ACCIONES)
CREATE TABLE stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  type ENUM('in', 'out', 'adjustment') NOT NULL,
  quantity INT NOT NULL,
  reason VARCHAR(255),
  reference_type VARCHAR(50),  -- 'reservation', 'manual', 'purchase', 'sale'
  reference_id INT,
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id)
);
```

---

## 🔄 Flujos Principales

### Flujo 1: Cliente ve productos y reserva

```
1. Cliente entra a la galería (/productos.php)
2. Ve productos con imagen, nombre, precio, stock
3. Puede filtrar por categoría o buscar por nombre
4. Click en "Agregar al carrito" → se guarda en localStorage
5. Icono de carrito en header muestra contador de items
6. Click en carrito → ve lista de productos seleccionados
7. Puede ajustar cantidad o eliminar productos
8. Click en "Reservar" → se abre formulario:
   - Nombre completo (requerido)
   - Email (requerido, validado)
   - Teléfono (requerido)
   - Fecha deseada de retiro (requerido, no puede ser pasado)
   - Notas/comentarios (opcional)
9. Submit → POST a /api/agregar-reserva.php
10. Backend valida stock, crea reserva, guarda items
11. Se limpia el carrito (localStorage)
12. Muestra mensaje de confirmación
```

### Flujo 2: Admin gestiona productos

```
1. Admin hace login (/login.php, en la raíz)
2. Accede a dashboard con estadísticas (SOLO LECTURA)
3. Va a /admin/productos.php para gestionar:
   - Crear/editar productos
   - AJUSTAR STOCK MANUALMENTE (+/-)
   - Desactivar productos
4. Cuando vende físicamente:
   - Va a /admin/productos.php
   - Busca el producto
   - Click en "Ajustar stock"
   - Resta la cantidad vendida
   - Motivo: "Venta física"
```

### Flujo 3: Admin gestiona reservas

```
1. Admin ve lista de reservas (/admin/reservas.php)
2. Filtra por estado: pendientes, confirmadas, canceladas, completadas
3. Click en reserva → ve detalle:
   - Datos del cliente
   - Productos reservados con cantidades
   - Fecha de retiro
   - Notas
4. Puede cambiar estado:
   - 'pending' → 'confirmed' (NO descuenta stock: ya se apartó al crear la reserva)
   - 'pending' → 'cancelled' (devuelve el stock)
   - 'confirmed' → 'cancelled' (devuelve el stock si aún no se entregó)
   - 'confirmed' → 'completed' (entregado al cliente, sin cambio de stock)
5. Al crear la reserva → descuento atómico y movimiento 'out'. Al cancelar → movimiento 'in'.
```

> **Regla de stock acordada (Fase 0):** el stock se **aparta al crear la reserva**, no al confirmarla.
> El descuento es atómico (`UPDATE ... WHERE stock >= :cantidad`) para evitar sobreventa.
> `stock_movements.quantity` se guarda **con signo**: negativo = salida, positivo = entrada.
> Las reservas pendientes abandonadas se revisan manualmente desde `/admin/reservas.php` (sin cron en free tier).

---

## 📊 Dashboard del Admin (SOLO LECTURA)

**Propósito:** Vista rápida del estado del sistema. NO se edita nada aquí.

### Tarjetas de Estadísticas (Cards)

```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│  Productos      │  │  Stock Total    │  │  Reservas       │  │  Stock Bajo     │
│  Activos        │  │  (unidades)     │  │  Pendientes     │  │  (< mínimo)     │
│      45         │  │     1,234       │  │       8         │  │       3         │
└─────────────────┘  └─────────────────┘  └─────────────────┘  └─────────────────┘
```

### Secciones del Dashboard

#### 1. Resumen General (Cards)
- Total de productos activos
- Stock total (suma de unidades)
- Reservas pendientes
- Productos con stock bajo (< stock_min)
- Reservas de hoy
- Reservas de esta semana

#### 2. Últimos Movimientos de Stock (Tabla simple, solo lectura)
| Fecha | Producto | Tipo | Cantidad | Motivo |
|-------|----------|------|----------|--------|
| 2026-11-20 10:30 | Producto A | Salida | -5 | Venta física |
| 2026-11-20 09:15 | Producto B | Entrada | +20 | Compra proveedor |
| 2026-11-19 16:45 | Producto C | Salida | -2 | Reserva #123 |

#### 3. Últimas Reservas (Tabla simple, solo lectura)
| ID | Cliente | Fecha | Estado | Productos |
|----|---------|-------|--------|-----------|
| #125 | Juan Pérez | 2026-11-22 | Pendiente | 3 items |
| #124 | María López | 2026-11-20 | Confirmada | 1 item |

#### 4. Productos con Stock Bajo (Alerta visual)
| Producto | Stock Actual | Stock Mínimo |
|----------|--------------|--------------|
| Producto X | 2 | 5 |
| Producto Y | 0 | 3 |

### Queries para el Dashboard

```php
// Total productos activos
$totalProductos = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();

// Stock total
$stockTotal = $pdo->query("SELECT COALESCE(SUM(stock), 0) FROM products WHERE is_active = 1")->fetchColumn();

// Reservas pendientes
$reservasPendientes = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();

// Productos con stock bajo
$stmt = $pdo->query("
    SELECT name, stock, stock_min 
    FROM products 
    WHERE is_active = 1 AND stock <= stock_min 
    ORDER BY stock ASC
");
$stockBajo = $stmt->fetchAll();

// Últimos movimientos (últimos 10)
$stmt = $pdo->query("
    SELECT sm.created_at, p.name, sm.type, sm.quantity, sm.reason
    FROM stock_movements sm
    JOIN products p ON sm.product_id = p.id
    ORDER BY sm.created_at DESC
    LIMIT 10
");
$ultimosMovimientos = $stmt->fetchAll();

// Últimas reservas (últimas 5). Se usa una subconsulta para no depender
// de la detección de dependencias funcionales de ONLY_FULL_GROUP_BY en TiDB.
$stmt = $pdo->query("
    SELECT r.id, r.customer_name, r.pickup_date, r.status,
           (SELECT COUNT(*) FROM reservation_items ri
            WHERE ri.reservation_id = r.id) AS total_items
    FROM reservations r
    ORDER BY r.created_at DESC
    LIMIT 5
");
$ultimasReservas = $stmt->fetchAll();
```

---

## 🎨 Diseño y UI

### Paleta de colores sugerida (Tailwind)

```
Primario:    blue-600    (#2563eb)
Secundario:  slate-800   (#1e293b)
Acento:      emerald-500 (#10b981)
Fondo:       slate-50    (#f8fafc)
Texto:       slate-800   (#1e293b)
```

### Componentes clave

- **Header público:** Logo, navegación (Inicio, Productos, Contacto), icono carrito con contador
- **Footer:** Información de contacto, enlaces, copyright
- **Card de producto:** Imagen, nombre, precio, stock, botón "Agregar al carrito"
- **Formulario de reserva:** Modal o página con validación en tiempo real
- **Sidebar admin:** Dashboard, Productos, Categorías, Reservas, Logout
- **Dashboard admin:** Cards de estadísticas + tablas de solo lectura
- **Tablas admin:** Con DataTables para búsqueda y paginación (excepto dashboard)

### Responsive

- Mobile-first con Tailwind
- Galería: 1 columna móvil, 2 tablet, 3-4 desktop
- Carrito: dropdown en desktop, página completa en móvil
- Dashboard: cards en grid 1/2/4 columnas según pantalla

---

## 🔐 Seguridad (OBLIGATORIO)

1. **Contraseñas:** Usar `password_hash()` y `password_verify()`
2. **SQL Injection:** SIEMPRE usar **prepared statements (PDO)**
3. **XSS:** Usar `htmlspecialchars()` al mostrar datos del usuario
4. **CSRF:** Incluir token CSRF en todos los formularios POST
5. **Validación:** 
   - Cliente (JavaScript) → para UX
   - Servidor (PHP) → SIEMPRE validar de nuevo
6. **Sesiones:** Usar `session_start()` y regenerar ID en login
7. **Archivos subidos:** Validar tipo MIME y tamaño antes de subir a Cloudinary
8. **Variables de entorno:** NUNCA hardcodear credenciales, usar `.env`

---

## 📝 Convenciones de Código

### PHP

```php
<?php
// Ejemplo de conexión a BD (includes/db.php)

$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'nombre_db';
$user = getenv('DB_USER') ?: 'usuario';
$pass = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
```

```php
// Ejemplo de función para verificar sesión admin (includes/auth.php)

function verificarLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}
```

### JavaScript

```javascript
// Usar módulos o IIFE para evitar contaminación global
// Ejemplo: carrito.js

const Carrito = {
    items: [],
    
    init() {
        this.items = JSON.parse(localStorage.getItem('carrito')) || [];
        this.actualizarContador();
    },
    
    agregar(producto) {
        const existente = this.items.find(item => item.id === producto.id);
        if (existente) {
            existente.cantidad++;
        } else {
            this.items.push({ ...producto, cantidad: 1 });
        }
        this.guardar();
        this.mostrarNotificacion('Producto agregado al carrito');
    },
    
    eliminar(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.guardar();
    },
    
    actualizarCantidad(productId, cantidad) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            item.cantidad = Math.max(1, cantidad);
            this.guardar();
        }
    },
    
    guardar() {
        localStorage.setItem('carrito', JSON.stringify(this.items));
        this.actualizarContador();
    },
    
    actualizarContador() {
        const total = this.items.reduce((sum, item) => sum + item.cantidad, 0);
        const contador = document.getElementById('carrito-contador');
        if (contador) contador.textContent = total;
    },
    
    obtenerTotal() {
        return this.items.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    },
    
    vaciar() {
        this.items = [];
        localStorage.removeItem('carrito');
        this.actualizarContador();
    },
    
    mostrarNotificacion(mensaje) {
        if (typeof Toastify !== 'undefined') {
            Toastify({
                text: mensaje,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#10b981"
            }).showToast();
        }
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => Carrito.init());
```

### Nomenclatura

- **Archivos:** kebab-case (`producto-detalle.php`)
- **Variables PHP:** camelCase (`$productoId`, `$stockActual`)
- **Variables JS:** camelCase (`const carritoItems`)
- **Clases CSS:** BEM o Tailwind utilities
- **Tablas BD:** snake_case plural (`product_images`, `stock_movements`)
- **Columnas BD:** snake_case (`customer_name`, `pickup_date`)

---

## 🌐 Variables de Entorno (.env)

```env
# Producción (Render + TiDB Cloud): una sola variable con la cadena completa
DB_URL=mysql://tu_usuario:tu_password@tu-host.tidbcloud.com:4000/test

# Local (docker compose): variables separadas
DB_HOST=mysql
DB_PORT=3306
DB_NAME=test
DB_USER=almacen
DB_PASS=almacen_secret

# Cloudinary (opcional)
CLOUDINARY_CLOUD_NAME=tu_cloud_name
CLOUDINARY_API_KEY=tu_api_key
CLOUDINARY_API_SECRET=tu_api_secret
```

---

## 📦 Endpoints de la API

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/csrf.php` | Obtener token CSRF y cookie de sesión |
| POST | `/api/agregar-reserva.php` | Crear nueva reserva |
| POST | `/api/actualizar-reserva.php` | Cambiar estado de reserva |
| POST | `/api/actualizar-stock.php` | Ajustar stock de producto |
| POST | `/api/subir-imagen.php` | Subir imagen a Cloudinary |
| POST | `/api/eliminar-imagen.php` | Eliminar imagen de Cloudinary |
| POST | `/api/login.php` | Autenticar admin |
| POST | `/api/logout.php` | Cerrar sesión |

### Formato de respuesta estándar

```json
// Éxito
{
  "success": true,
  "data": { ... },
  "message": "Operación exitosa"
}

// Error
{
  "success": false,
  "errors": ["Mensaje de error 1", "Mensaje de error 2"]
}
```

---

## 🚨 Reglas y Restricciones

### OBLIGATORIO

1. ✅ Usar **Tailwind CSS** para todos los estilos
2. ✅ Usar **PDO con prepared statements** para todas las queries
3. ✅ Validar datos en cliente (JS) Y servidor (PHP)
4. ✅ Usar **localStorage** para el carrito (no cookies)
5. ✅ Mostrar mensajes de error/éxito con **SweetAlert2** o **Toastify**
6. ✅ Incluir **token CSRF** en todos los formularios
7. ✅ Sanitizar salida con `htmlspecialchars()`
8. ✅ Usar **Cloudinary** para todas las imágenes de productos
9. ✅ El **Dashboard es SOLO LECTURA** — no incluir formularios ni botones de acción
10. ✅ Registrar **TODOS** los movimientos de stock en `stock_movements`

### PROHIBIDO

1. ❌ No usar frameworks PHP (Laravel, Symfony, etc.)
2. ❌ No usar frameworks JS (React, Vue, Angular)
3. ❌ No usar jQuery (JavaScript vanilla)
4. ❌ No hardcodear credenciales en el código
5. ❌ No almacenar imágenes en el servidor (usar Cloudinary)
6. ❌ No implementar pagos online (solo reservas)
7. ❌ No usar librerías pesadas innecesarias
8. ❌ No permitir edición desde el Dashboard (solo visualización)

---

## 🎯 Fases de Desarrollo

### Fase 0: Viabilidad (COMPLETADA)
- [x] Docker local (`Dockerfile`, `docker-compose.yml`) + MySQL 8
- [x] `database/schema.sql` y `database/seed.sql`
- [x] `includes/db.php` (PDO + TLS opcional), `functions.php` (CSRF) y `auth.php`
- [x] API de reservas con stock apartado, trazabilidad y login admin
- [x] `tests/smoke.php` con pruebas automáticas

### Fase 1: Base y Estructura
- [x] Configurar estructura de carpetas
- [x] Crear `includes/db.php` con conexión a TiDB Cloud
- [x] Crear tablas en MySQL
- [x] Configurar `.env` y `.env.example`
- [x] Crear header/footer públicos con Tailwind

### Fase 2: Catálogo Público
- [x] Página de inicio (`index.php`)
- [x] Galería de productos (`productos.php`) con filtros
- [x] Detalle de producto (`producto-detalle.php`)
- [x] Card de producto con botón "Agregar al carrito"

### Fase 3: Carrito de Reservas
- [x] Lógica del carrito en JavaScript (`carrito.js`)
- [x] Icono de carrito en header con contador
- [x] Página/modal para ver carrito
- [x] Formulario de reserva con validación
- [x] API para crear reserva (`api/agregar-reserva.php`)

### Fase 4: Panel Admin
- [x] Login (`/login.php`, en la raíz)
- [x] Dashboard SOLO LECTURA con estadísticas y últimos movimientos
- [x] CRUD de productos con ajuste manual de stock
- [x] CRUD de categorías
- [x] Subida de imágenes a Cloudinary
- [x] Gestión de reservas (lista + detalle + cambio de estado)

### Fase 5: Pulido
- [x] Animaciones con AOS
- [x] Notificaciones con Toastify/SweetAlert2
- [x] Tablas con DataTables (excepto dashboard; DataTables 3 sin jQuery)
- [x] Responsive completo
- [ ] Pruebas de flujos completos (ejecutar en Docker)

> **Decisión de implementación:** productos y categorías se guardan con **formularios PHP
> clásicos** (POST a la misma página + PRG + mensaje flash). Reservas, estados, stock y login
> usan la API JSON. El stock **nunca** se edita desde el dashboard ni desde el formulario de
> producto en edición: solo desde `/api/actualizar-stock.php` (botón "Ajustar stock" en
> `/admin/productos.php`), para preservar la trazabilidad en `stock_movements`.

---

## 📝 Notas para el Agente

1. **Genera código completo y funcional**, no snippets parciales
2. **Incluye comentarios** explicando la lógica compleja
3. **Sigue la estructura de archivos** definida arriba
4. **Usa Tailwind CSS** para todos los estilos, evita CSS personalizado
5. **Valida siempre** en ambos lados (cliente y servidor)
6. **Maneja errores** adecuadamente (try-catch, validaciones)
7. **No agregues funcionalidades extra** que no estén en este documento
8. **Si necesitas agregar algo**, pregunta primero antes de implementar
9. **El código debe ser compatible** con PHP 8+ y navegadores modernos
10. **Optimiza para el free tier de Render** (evita procesos pesados en background)
11. **El Dashboard es SOLO LECTURA** — solo mostrar datos, no editar
12. **El ajuste de stock manual se hace en `/admin/productos.php`**, no en el dashboard

---

## 🔗 Referencias Útiles

- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [SweetAlert2](https://sweetalert2.github.io/)
- [DataTables](https://datatables.net/)
- [Toastify](https://apvarun.github.io/toastify-js/)
- [Font Awesome Icons](https://fontawesome.com/icons)
- [Cloudinary PHP SDK](https://cloudinary.com/documentation/php_integration)
- [TiDB Cloud Docs](https://docs.pingcap.com/tidbcloud/)

---

*Última actualización: 2026-11-20 — Fase 0 implementada*