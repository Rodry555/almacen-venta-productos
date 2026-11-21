<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

/**
 * Crea una reserva y APARTA el stock en el mismo momento.
 * Reglas acordadas:
 *  - pending  -> descuenta stock y genera movimiento 'out'
 *  - confirmed -> NO toca stock (ya está apartado)
 *  - cancelled -> devuelve stock y genera movimiento 'in'
 */
if (metodo() !== 'POST') {
    responder(false, [], '', ['Método no permitido.'], 405);
}
if (!csrfValidar(csrfTokenPeticion())) {
    responder(false, [], '', ['Token CSRF inválido.'], 403);
}

$body = leerJson();
$nombre   = trim((string) ($body['customer_name'] ?? ''));
$telefono = trim((string) ($body['customer_phone'] ?? ''));
$pickup   = trim((string) ($body['pickup_date'] ?? ''));
$notas    = trim((string) ($body['notes'] ?? ''));
$items    = $body['items'] ?? [];

$errores = [];
if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 255) {
    $errores[] = 'El nombre debe tener entre 3 y 255 caracteres.';
}
// Teléfono boliviano simple: 8 dígitos, empieza con 6 o 7.
if (!preg_match('/^[67][0-9]{7}$/D', $telefono)) {
    $errores[] = 'El teléfono debe tener 8 dígitos y empezar con 6 o 7.';
}
if (!esFechaFutura($pickup)) {
    $errores[] = 'La fecha de retiro debe ser hoy o posterior (YYYY-MM-DD).';
}
if (mb_strlen($notas) > 1000) {
    $errores[] = 'Las notas no pueden superar los 1000 caracteres.';
}
if (!is_array($items) || count($items) === 0) {
    $errores[] = 'El carrito está vacío.';
}

$normalizados = [];
$vistos = [];
if (is_array($items)) {
    foreach ($items as $item) {
        if (!is_array($item)) {
            $errores[] = 'Hay un producto inválido en el carrito.';
            continue;
        }
        $id = (int) ($item['id'] ?? 0);
        $cantidad = (int) ($item['cantidad'] ?? $item['quantity'] ?? 0);
        if ($id <= 0) {
            $errores[] = 'Producto inválido en el carrito.';
        }
        if ($cantidad < 1 || $cantidad > 99) {
            $errores[] = 'La cantidad debe estar entre 1 y 99.';
        }
        if (isset($vistos[$id])) {
            $errores[] = 'Hay productos duplicados en el carrito.';
        }
        $vistos[$id] = true;
        $normalizados[] = ['id' => $id, 'cantidad' => $cantidad];
    }
}
if ($errores) {
    responder(false, [], '', $errores, 400);
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // 1) Validar disponibilidad dentro de la transacción.
    $stmtProducto = $pdo->prepare('SELECT id, name, price, stock, is_active FROM products WHERE id = :id');
    $productos = [];
    foreach ($normalizados as $n) {
        $stmtProducto->execute([':id' => $n['id']]);
        $producto = $stmtProducto->fetch();
        if ($producto === false || (int) $producto['is_active'] !== 1) {
            $pdo->rollBack();
            responder(false, [], '', ['Uno de los productos ya no está disponible.'], 409);
        }
        if ((int) $producto['stock'] < $n['cantidad']) {
            $pdo->rollBack();
            responder(false, [], '', ['Stock insuficiente para ' . $producto['name'] . '.'], 409);
        }
        $productos[] = ['producto' => $producto, 'cantidad' => $n['cantidad']];
    }

    // 2) Cabecera de la reserva.
    $insReserva = $pdo->prepare(
        "INSERT INTO reservations (customer_name, customer_phone, pickup_date, notes, status)
         VALUES (:nombre, :telefono, :pickup, :notas, 'pending')"
    );
    $insReserva->execute([
        ':nombre'   => $nombre,
        ':telefono' => $telefono,
        ':pickup'   => $pickup,
        ':notas'    => $notas !== '' ? $notas : null,
    ]);
    $reservaId = (int) $pdo->lastInsertId();

    // 3) Descuento atómico + detalle + trazabilidad.
    //    WHERE stock >= :minimo evita sobreventa incluso con peticiones concurrentes.
    $descontar = $pdo->prepare(
        'UPDATE products SET stock = stock - :cantidad
          WHERE id = :id AND stock >= :minimo AND is_active = 1'
    );
    $insItem = $pdo->prepare(
        'INSERT INTO reservation_items (reservation_id, product_id, quantity, price)
         VALUES (:reserva, :producto, :cantidad, :precio)'
    );
    $insMovimiento = $pdo->prepare(
        "INSERT INTO stock_movements (product_id, type, quantity, reason, reference_type, reference_id, user_id)
         VALUES (:producto, 'out', :cantidad, :motivo, 'reservation', :reserva, NULL)"
    );

    $total = 0.0;
    foreach ($productos as $linea) {
        $producto = $linea['producto'];
        $cantidad = $linea['cantidad'];
        $productoId = (int) $producto['id'];

        $descontar->execute([
            ':cantidad' => $cantidad,
            ':id'       => $productoId,
            ':minimo'   => $cantidad,
        ]);
        if ($descontar->rowCount() !== 1) {
            $pdo->rollBack();
            responder(false, [], '', ['Stock insuficiente para ' . $producto['name'] . '.'], 409);
        }

        $insItem->execute([
            ':reserva'  => $reservaId,
            ':producto' => $productoId,
            ':cantidad' => $cantidad,
            ':precio'   => $producto['price'],
        ]);
        $insMovimiento->execute([
            ':producto' => $productoId,
            ':cantidad' => -$cantidad,
            ':motivo'   => 'Reserva #' . $reservaId,
            ':reserva'  => $reservaId,
        ]);

        $total += (float) $producto['price'] * $cantidad;
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al crear reserva: ' . $e->getMessage());
    responder(false, [], '', ['Error interno al crear la reserva.'], 500);
}

responder(true, [
    'reservation_id' => $reservaId,
    'total'          => round($total, 2),
    'status'         => 'pending',
], 'Reserva creada correctamente.');
