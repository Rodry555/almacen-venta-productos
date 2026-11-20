<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

/** Cambia el estado de una reserva. Solo admin. */
if (metodo() !== 'POST') {
    responder(false, [], '', ['Método no permitido.'], 405);
}
if (!csrfValidar(csrfTokenPeticion())) {
    responder(false, [], '', ['Token CSRF inválido.'], 403);
}
$usuario = requerirLoginApi();

$body = leerJson();
$id = (int) ($body['id'] ?? 0);
$nuevo = (string) ($body['status'] ?? '');

if ($id <= 0) {
    responder(false, [], '', ['ID de reserva no válido.'], 400);
}

// Transiciones permitidas. El stock ya se descontó al crear la reserva:
// confirmar no mueve stock; cancelar lo devuelve.
$transiciones = [
    'pending'   => ['confirmed', 'cancelled'],
    'confirmed' => ['cancelled', 'completed'],
    'cancelled' => [],
    'completed' => [],
];

$pdo = db();
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id, status FROM reservations WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $reserva = $stmt->fetch();
    if ($reserva === false) {
        $pdo->rollBack();
        responder(false, [], '', ['Reserva no encontrada.'], 404);
    }

    $actual = (string) $reserva['status'];
    if (!in_array($nuevo, $transiciones[$actual] ?? [], true)) {
        $pdo->rollBack();
        responder(false, [], '', ['Transición de estado no permitida.'], 409);
    }

    // UPDATE condicional: evita que dos admins confirmen/cancelen a la vez.
    $cambio = $pdo->prepare('UPDATE reservations SET status = :nuevo WHERE id = :id AND status = :actual');
    $cambio->execute([':nuevo' => $nuevo, ':id' => $id, ':actual' => $actual]);
    if ($cambio->rowCount() !== 1) {
        $pdo->rollBack();
        responder(false, [], '', ['La reserva cambió de estado mientras se procesaba.'], 409);
    }

    $itemsDevueltos = 0;
    if ($nuevo === 'cancelled') {
        $stmtItems = $pdo->prepare('SELECT product_id, quantity FROM reservation_items WHERE reservation_id = :id');
        $stmtItems->execute([':id' => $id]);
        $items = $stmtItems->fetchAll();

        $devolver = $pdo->prepare('UPDATE products SET stock = stock + :cantidad WHERE id = :id');
        $movimiento = $pdo->prepare(
            "INSERT INTO stock_movements (product_id, type, quantity, reason, reference_type, reference_id, user_id)
             VALUES (:producto, 'in', :cantidad, :motivo, 'reservation', :reserva, :usuario)"
        );
        foreach ($items as $item) {
            $productoId = (int) $item['product_id'];
            $cantidad = (int) $item['quantity'];
            $devolver->execute([':cantidad' => $cantidad, ':id' => $productoId]);
            $movimiento->execute([
                ':producto' => $productoId,
                ':cantidad' => $cantidad,
                ':motivo'   => 'Cancelación reserva #' . $id,
                ':reserva'  => $id,
                ':usuario'  => $usuario['id'],
            ]);
            $itemsDevueltos++;
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al actualizar reserva: ' . $e->getMessage());
    responder(false, [], '', ['Error interno al actualizar la reserva.'], 500);
}

responder(true, [
    'reservation_id' => $id,
    'status'         => $nuevo,
    'items_devueltos'=> $itemsDevueltos,
], 'Estado de la reserva actualizado.');
