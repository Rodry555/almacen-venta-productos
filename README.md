# Sistema de Almacén y Venta de Productos

Catálogo público con carrito de **reservas** (sin pagos online) y panel de administración.
**Sistema completo implementado (Fases 0 a 5).** Suite smoke verificada: 52 pruebas, 0 fallos.

## Requisitos

- Docker y Docker Compose
- (Opcional) PHP 8.3 en local solo si quieres ejecutar scripts fuera del contenedor

## Arranque local

```bash
cp .env.example .env        # el .env ya viene preparado para docker compose
docker compose up -d --build
```

- Web: http://localhost:8000
- MySQL: `localhost:3306` (usuario `almacen`, pass `almacen_secret`, BD `test`)
- Login admin: **http://localhost:8000/login.php** → credenciales definidas en `database/seed.sql`

## Mapa de rutas

### Público

| Ruta | Descripción |
|---|---|
| `/index.php` | Portada con categorías y productos recientes |
| `/productos.php` | Galería con filtro por categoría y búsqueda (`?categoria=`, `?q=`) |
| `/producto-detalle.php?id=X` | Ficha del producto |
| `/contacto.php` | Información de contacto |
| `/carrito/carrito.php` | Carrito (`localStorage`) + formulario de reserva |

### Admin (requiere sesión)

| Ruta | Descripción |
|---|---|
| `/login.php` | Acceso (raíz, no `/admin/login.php`) |
| `/admin/dashboard.php` | **Solo lectura**: métricas, movimientos, reservas y alertas |
| `/admin/productos.php` | Listado con DataTables; activar/desactivar y **Ajustar stock** |
| `/admin/producto-form.php` | Alta/edición (formulario PHP clásico, imagen a Cloudinary) |
| `/admin/categorias.php` | CRUD de categorías (formulario PHP clásico) |
| `/admin/reservas.php` | Listado filtrable por estado |
| `/admin/reserva-detalle.php?id=X` | Detalle y cambio de estado |

Productos y categorías se guardan con **formularios PHP clásicos** (POST a la misma página,
validación en servidor, PRG con mensaje flash). Reservas, estados, stock y login usan la API JSON.

### Pruebas smoke

```bash
docker compose exec web php tests/smoke.php
```

La suite deja la BD en un estado conocido y comprueba, entre otras cosas:

- token CSRF y rechazo de peticiones sin token / con método incorrecto;
- crear reserva **descuenta stock** y crea un movimiento `out`;
- un segundo intento sin stock responde `409` y no modifica nada;
- confirmar **no** vuelve a descontar ni crea movimiento;
- cancelar devuelve el stock y crea un movimiento `in`;
- ajuste manual con motivo "Venta física";
- login, rotación de token, logout y escapado XSS;
- páginas públicas y admin (acceso sin sesión redirige al login; alta de categoría por formulario).

## Regla de stock acordada

| Acción | Stock | Movimiento |
|---|---|---|
| Crear reserva (`pending`) | se aparta (−) | `out` |
| Confirmar | sin cambio | ninguno |
| Cancelar | se devuelve (+) | `in` |
| Completar | sin cambio | ninguno |
| Ajuste manual | delta con signo | `adjustment` |
| Stock inicial de producto | se registra | `adjustment` |

`stock_movements.quantity` se guarda **con signo**: negativo = salida, positivo = entrada.
El descuento es atómico (`UPDATE ... WHERE stock >= :cantidad`), así que no se puede sobrevender
aunque lleguen varias peticiones a la vez. Las reservas pendientes abandonadas se revisan
manualmente desde `/admin/reservas.php` (no hay cron en el plan free de Render).

**El stock solo se modifica desde la API de reservas o desde "Ajustar stock" en
`/admin/productos.php`**, nunca desde el dashboard ni editando el campo en el formulario de
producto (en edición se muestra de solo lectura).

## Paso a producción

1. En **TiDB Cloud** ejecuta `database/schema.sql` y `database/seed.sql` sobre tu BD (por ejemplo `test`).
   El usuario de TiDB lleva prefijo de cuenta (`xxxxxx.root`).
2. Crea el servicio en **Render** desde este repo con el Blueprint `render.yaml`. Render no tiene
   runtime PHP nativo, por eso se usa el `Dockerfile` incluido.
3. En el dashboard de Render rellena **una sola variable**:
   `DB_URL=mysql://USUARIO:PASSWORD@HOST:4000/test`
   (usa la cadena que te da TiDB Cloud; el CA del sistema ya está en la imagen).
4. Cloudinary es opcional. Si no tienes credenciales, no añadas nada: los productos se guardan
   sin imagen y se muestra un placeholder.
5. No hace falta `APP_URL`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` ni `DB_PASS` en producción:
   `includes/db.php` obtiene todo de `DB_URL`.

> Nota: `AGENTS.md` es la especificación completa del proyecto.
