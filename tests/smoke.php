<?php
declare(strict_types=1);

/**
 * Pruebas del sistema completo. Ejecutar DENTRO del contenedor web:
 *   docker compose exec web php tests/smoke.php
 */
require_once __DIR__ . '/../includes/functions.php';

$base = getenv('SMOKE_URL') ?: 'http://localhost';
$cookies = [];
$fallos = 0;
$pruebas = 0;
$warningsPagina = [];

function req(
    string $method,
    string $path,
    ?array $data = null,
    ?string $token = null,
    bool $formulario = false,
    bool $seguirRedirecciones = true
): array {
    global $base, $cookies, $warningsPagina;

    $headers = ['Accept: */*'];
    if ($formulario) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $contenido = $data === null ? null : http_build_query($data);
    } else {
        $headers[] = 'Content-Type: application/json';
        $contenido = $data === null ? null : json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    if ($token !== null) {
        $headers[] = 'X-CSRF-Token: ' . $token;
    }
    if ($cookies !== []) {
        $pares = [];
        foreach ($cookies as $nombre => $valor) {
            $pares[] = $nombre . '=' . $valor;
        }
        $headers[] = 'Cookie: ' . implode('; ', $pares);
    }

    $ctx = stream_context_create(['http' => [
        'method'           => $method,
        'header'           => implode("\r\n", $headers),
        'content'          => $contenido,
        'ignore_errors'    => true,
        'timeout'          => 15,
        'follow_location'  => $seguirRedirecciones ? 1 : 0,
        'max_redirects'    => 3,
    ]]);

    $http_response_header = [];
    $body = @file_get_contents($base . $path, false, $ctx);

    $status = 0;
    $location = '';
    foreach ($http_response_header as $cabecera) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $cabecera, $m)) {
            $status = (int) $m[1];
        }
        if (preg_match('/^Location:\s*(.+)$/i', $cabecera, $m)) {
            $location = trim($m[1]);
        }
        if (preg_match('/^Set-Cookie:\s*([^;]+)/i', $cabecera, $m)) {
            $partes = array_pad(explode('=', $m[1], 2), 2, '');
            $cookies[$partes[0]] = $partes[1];
        }
    }

    $raw = $body === false ? '' : $body;
    if ($status === 200 && strpos($raw, '<b>Warning</b>') !== false) {
        $warningsPagina[] = $path;
    }

    $json = json_decode($raw, true);
    return [
        'status'   => $status,
        'json'     => is_array($json) ? $json : [],
        'raw'      => $raw,
        'location' => $location,
    ];
}

function check(string $nombre, bool $ok, string $detalle = ''): void
{
    global $fallos, $pruebas;
    $pruebas++;
    echo ($ok ? "  OK    " : "  FALLO ") . $nombre
        . ($detalle !== '' ? '  -> ' . $detalle : '') . PHP_EOL;
    if (!$ok) {
        $fallos++;
    }
}

function stockDe(int $id): int
{
    $stmt = db()->prepare('SELECT stock FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return (int) $stmt->fetchColumn();
}

function movimientos(int $productoId, string $tipo): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM stock_movements WHERE product_id = :id AND type = :tipo');
    $stmt->execute([':id' => $productoId, ':tipo' => $tipo]);
    return (int) $stmt->fetchColumn();
}

function resetDatos(): void
{
    $pdo = db();
    $pdo->exec('DELETE FROM stock_movements');
    $pdo->exec('DELETE FROM reservation_items');
    $pdo->exec('DELETE FROM reservations');
    $pdo->exec('UPDATE products SET stock = CASE id WHEN 1 THEN 5 WHEN 2 THEN 20 WHEN 3 THEN 2 ELSE stock END');
}

echo "Pruebas smoke del sistema\n";
echo "======================\n";
resetDatos();

// --- Token CSRF -----------------------------------------------------------
$r = req('GET', '/api/csrf.php');
$token = (string) ($r['json']['csrf_token'] ?? '');
check('GET /api/csrf.php devuelve token', $r['status'] === 200 && strlen($token) === 64, $r['raw']);

// --- Endpoint público -----------------------------------------------------
$r = req('GET', '/api/agregar-reserva.php');
check('GET a agregar-reserva devuelve 405', $r['status'] === 405);

$r = req('POST', '/api/agregar-reserva.php', [
    'customer_name' => 'Juan Perez',
]);
check('POST sin token CSRF devuelve 403', $r['status'] === 403);

$r = req('POST', '/api/agregar-reserva.php', [
    'customer_name'   => 'Juan Perez',
    'customer_email'  => 'no-es-email',
    'customer_phone'  => '600123456',
    'pickup_date'     => date('Y-m-d', strtotime('+2 days')),
    'items'           => [['id' => 1, 'cantidad' => 1]],
], $token);
check('Email inválido devuelve 400', $r['status'] === 400);

// --- Reserva y apartado de stock ------------------------------------------
$pickup = date('Y-m-d', strtotime('+2 days'));
$reserva = [
    'customer_name'   => 'Juan Perez',
    'customer_email'  => 'juan@example.com',
    'customer_phone'  => '600123456',
    'pickup_date'     => $pickup,
    'notes'           => '',
    'items'           => [['id' => 1, 'cantidad' => 3]],
];
$r = req('POST', '/api/agregar-reserva.php', $reserva, $token);
check('Crear reserva devuelve 200', $r['status'] === 200 && ($r['json']['success'] ?? false) === true, $r['raw']);
$reservaId = (int) ($r['json']['data']['reservation_id'] ?? 0);
check('La reserva tiene ID', $reservaId > 0);
check('Stock apartado: 5 -> 2', stockDe(1) === 2, 'stock=' . stockDe(1));
check('Movimiento out registrado', movimientos(1, 'out') === 1);

// --- Intento de sobreventa -------------------------------------------------
$r = req('POST', '/api/agregar-reserva.php', $reserva, $token);
check('Reserva sin stock suficiente devuelve 409', $r['status'] === 409, $r['raw']);
check('El stock no se ha tocado tras el fallo', stockDe(1) === 2, 'stock=' . stockDe(1));
check('No se añadió otro movimiento', movimientos(1, 'out') === 1);

// --- Páginas públicas ------------------------------------------------------
$r = req('GET', '/index.php');
check('GET /index.php devuelve 200', $r['status'] === 200 && strpos($r['raw'], 'Catálogo con reserva previa') !== false, $r['raw']);

$r = req('GET', '/productos.php');
check('GET /productos.php muestra el catálogo', $r['status'] === 200 && strpos($r['raw'], 'Cuaderno A5') !== false);

$r = req('GET', '/producto-detalle.php?id=1');
check('GET /producto-detalle.php?id=1 devuelve 200', $r['status'] === 200 && strpos($r['raw'], 'Cuaderno A5') !== false);

$r = req('GET', '/contacto.php');
check('GET /contacto.php devuelve 200', $r['status'] === 200);

$r = req('GET', '/carrito/carrito.php');
check('GET /carrito/carrito.php devuelve 200', $r['status'] === 200 && strpos($r['raw'], 'form-reserva') !== false);

// --- Login admin ----------------------------------------------------------
$r = req('GET', '/admin/dashboard.php', null, null, false, false);
check('Dashboard sin sesión redirige a /login.php',
    $r['status'] === 302 && strpos($r['location'], '/login.php') !== false, $r['location']);

$r = req('GET', '/login.php');
check('GET /login.php devuelve 200', $r['status'] === 200);

$r = req('POST', '/api/login.php', ['email' => 'admin@gmail.com', 'password' => 'incorrecta'], $token);
check('Login con contraseña incorrecta devuelve 401', $r['status'] === 401);

$r = req('POST', '/api/login.php', ['email' => 'admin@gmail.com', 'password' => 'almacenventasproductos'], $token);
check('Login admin devuelve 200', $r['status'] === 200, $r['raw']);
$adminToken = (string) ($r['json']['data']['csrf_token'] ?? '');
check('El token CSRF rota tras el login', $adminToken !== '' && $adminToken !== $token);

// --- Páginas del panel -----------------------------------------------------
$r = req('GET', '/admin/dashboard.php');
check('Dashboard con sesión devuelve 200', $r['status'] === 200 && strpos($r['raw'], 'solo lectura') !== false);
check('Dashboard NO contiene formularios (solo lectura)', strpos($r['raw'], '<form') === false);

$r = req('GET', '/admin/productos.php');
check('Listado de productos devuelve 200', $r['status'] === 200 && strpos($r['raw'], 'Cuaderno A5') !== false);

$r = req('GET', '/admin/producto-form.php?id=1');
check('Formulario de edición devuelve 200', $r['status'] === 200 && strpos($r['raw'], 'Editar producto') !== false);

$r = req('GET', '/admin/categorias.php');
check('Listado de categorías devuelve 200', $r['status'] === 200 && strpos($r['raw'], 'Papelería') !== false);
preg_match('/name="csrf-token" content="([^"]+)"/', $r['raw'], $m);
check('Token CSRF del HTML coincide con la API', ($m[1] ?? '') === $adminToken);

$r = req('GET', '/admin/reservas.php');
check('Listado de reservas devuelve 200', $r['status'] === 200);

$r = req('GET', '/admin/reserva-detalle.php?id=' . $reservaId);
check('Detalle de reserva devuelve 200', $r['status'] === 200 && strpos($r['raw'], 'Juan Perez') !== false);

// --- Formulario clásico de categorías (POST + PRG) -------------------------
$r = req('POST', '/admin/categorias.php', [
    'csrf_token'  => $adminToken,
    'accion'      => 'guardar',
    'name'        => 'Categoria Smoke',
    'description' => 'Creada por la prueba smoke',
    'is_active'   => '1',
], null, true, false);
check('Alta de categoría redirige (PRG)', $r['status'] === 302 && strpos($r['location'], 'categorias.php') !== false, $r['location']);

$stmt = db()->prepare('SELECT id FROM categories WHERE name = :n');
$stmt->execute([':n' => 'Categoria Smoke']);
$categoriaId = (int) $stmt->fetchColumn();
check('La categoría se guardó en la BD', $categoriaId > 0);

$r = req('POST', '/admin/categorias.php', [
    'csrf_token' => $adminToken,
    'accion'     => 'eliminar',
    'id'         => $categoriaId,
], null, true, false);
check('Borrado de categoría redirige (PRG)', $r['status'] === 302, $r['location']);

$stmt = db()->prepare('SELECT COUNT(*) FROM categories WHERE name = :n');
$stmt->execute([':n' => 'Categoria Smoke']);
check('La categoría de prueba se eliminó', (int) $stmt->fetchColumn() === 0);

// --- Formulario clásico de productos --------------------------------------
$r = req('POST', '/admin/producto-form.php', [
    'csrf_token'  => $adminToken,
    'name'        => 'Producto Smoke',
    'description' => 'Creado por la prueba smoke',
    'price'       => '9.50',
    'stock'       => '4',
    'stock_min'   => '2',
    'category_id' => '1',
    'is_active'   => '1',
], null, true, false);
check('Alta de producto redirige (PRG)', $r['status'] === 302, $r['location']);

$stmt = db()->prepare('SELECT id, stock FROM products WHERE name = :n');
$stmt->execute([':n' => 'Producto Smoke']);
$nuevoProducto = $stmt->fetch();
$nuevoProductoId = (int) ($nuevoProducto['id'] ?? 0);
check('Producto guardado con su stock inicial', $nuevoProductoId > 0 && (int) $nuevoProducto['stock'] === 4);

$stmt = db()->prepare("SELECT COUNT(*) FROM stock_movements WHERE product_id = :id AND reason = 'Stock inicial'");
$stmt->execute([':id' => $nuevoProductoId]);
check('Stock inicial queda trazado', (int) $stmt->fetchColumn() === 1);

$r = req('POST', '/admin/productos.php', [
    'csrf_token' => $adminToken,
    'id'         => $nuevoProductoId,
    'accion'     => 'toggle',
], null, true, false);
check('Cambiar visibilidad redirige (PRG)', $r['status'] === 302, $r['location']);

$stmt = db()->prepare('SELECT is_active FROM products WHERE id = :id');
$stmt->execute([':id' => $nuevoProductoId]);
check('El producto pasó a inactivo', (int) $stmt->fetchColumn() === 0);

// Limpieza del producto de prueba.
$stmt = db()->prepare('DELETE FROM stock_movements WHERE product_id = :id');
$stmt->execute([':id' => $nuevoProductoId]);
$stmt = db()->prepare('DELETE FROM products WHERE id = :id');
$stmt->execute([':id' => $nuevoProductoId]);

// --- Confirmar: no debe mover stock ---------------------------------------
$r = req('POST', '/api/actualizar-reserva.php', ['id' => $reservaId, 'status' => 'confirmed'], $adminToken);
check('Confirmar reserva devuelve 200', $r['status'] === 200, $r['raw']);
check('Confirmar NO descuenta stock', stockDe(1) === 2, 'stock=' . stockDe(1));
check('Confirmar NO crea movimiento nuevo', movimientos(1, 'out') === 1 && movimientos(1, 'in') === 0);

$r = req('POST', '/api/actualizar-reserva.php', ['id' => $reservaId, 'status' => 'pending'], $adminToken);
check('Transición inválida devuelve 409', $r['status'] === 409);

// --- Cancelar: devuelve stock ---------------------------------------------
$r = req('POST', '/api/actualizar-reserva.php', ['id' => $reservaId, 'status' => 'cancelled'], $adminToken);
check('Cancelar reserva confirmada devuelve 200', $r['status'] === 200, $r['raw']);
check('Stock devuelto: 2 -> 5', stockDe(1) === 5, 'stock=' . stockDe(1));
check('Movimiento in registrado', movimientos(1, 'in') === 1);

// --- Ajuste manual --------------------------------------------------------
$r = req('POST', '/api/actualizar-stock.php', [
    'product_id' => 1,
    'cantidad'   => -2,
    'motivo'     => 'Venta física',
], $adminToken);
check('Ajuste manual -2 devuelve 200', $r['status'] === 200, $r['raw']);
check('Stock tras venta física: 5 -> 3', stockDe(1) === 3, 'stock=' . stockDe(1));
check('Movimiento adjustment registrado', movimientos(1, 'adjustment') === 1);

$r = req('POST', '/api/actualizar-stock.php', [
    'product_id' => 1,
    'cantidad'   => -99,
    'motivo'     => 'Error de prueba',
], $adminToken);
check('Ajuste que deja stock negativo devuelve 409', $r['status'] === 409);

// --- Varios ---------------------------------------------------------------
check('e() escapa HTML', e('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;');

$r = req('POST', '/api/logout.php', [], $adminToken);
check('Logout devuelve 200', $r['status'] === 200, $r['raw']);

check('Ninguna página muestra warnings de PHP', $warningsPagina === [],
    $warningsPagina === [] ? '' : implode(', ', $warningsPagina));

echo "======================\n";
echo "{$pruebas} pruebas, {$fallos} fallos\n";
exit($fallos > 0 ? 1 : 0);
