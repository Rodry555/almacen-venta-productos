<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

/**
 * Ajuste manual de stock (venta física, compra a proveedor, corrección, etc.).
 * 'cantidad' es el DELTA con signo: -2 quita 2 unidades, +20 añade 20.
 */
if (metodo() !== 'POST') {
    responder(false, [], '', ['Método no permitido.'], 405);
}
if (!csrfValidar(csrfTokenPeticion())) {
    responder(false, [], '', ['Token CSRF inválido.'], 403);
}
$usuario = requerirLoginApi();

$body = leerJson();
$id = (int) ($body['product_id'] ?? 0);
$delta = (int) ($body['cantidad'] ?? 0);
$motivo = trim((string) ($body['motivo'] ?? ''));

$errores = [];
if ($id <= 0) {
    $errores[] = 'Producto no válido.';
}
if ($delta === 0 || abs($delta) > 10000) {
    $errores[] = 'La cantidad debe ser distinta de cero y como maximo 10000.';
}
if (mb_strlen($motivo) < 3 || mb_strlen($motivo) > 255) {
    $errores[] = 'El motivo debe tener entre 3 y 255 caracteres.';
}
if ($errores) {
    responder(false, [], '', $errores, 400);
}

$pdo = db();
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id, name, stock FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch();
    if ($producto === false) {
        $pdo->rollBack();
        responder(false, [], '', ['Producto no encontrado.'], 404);
    }

    // Condición con dos marcadores distintos: PDO con emulated prepares=false
    // no permite reutilizar el mismo placeholder dos veces en una consulta.
    $actualizar = $pdo->prepare(
        'UPDATE products SET stock = stock + :delta
          WHERE id = :id AND stock + :minimo >= 0'
    );
    $actualizar->execute([':delta' => $delta, ':id' => $id, ':minimo' => $delta]);
    if ($actualizar->rowCount() !== 1) {
        $pdo->rollBack();
        responder(false, [], '', ['El ajuste dejaría el stock en negativo.'], 409);
    }

    $movimiento = $pdo->prepare(
        "INSERT INTO stock_movements (product_id, type, quantity, reason, reference_type, reference_id, user_id)
         VALUES (:producto, 'adjustment', :cantidad, :motivo, 'manual', NULL, :usuario)"
    );
    $movimiento->execute([
        ':producto' => $id,
        ':cantidad' => $delta,
        ':motivo'   => $motivo,
        ':usuario'  => $usuario['id'],
    ]);

    $pdo->commit();

    // Releer fuera de la transacción para devolver el valor real aunque haya
    // llegado otro ajuste concurrente justo después del commit.
    $consultaFinal = $pdo->prepare('SELECT stock FROM products WHERE id = :id');
    $consultaFinal->execute([':id' => $id]);
    $stockFinal = (int) $consultaFinal->fetchColumn();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al ajustar stock: ' . $e->getMessage());
    responder(false, [], '', ['Error interno al ajustar el stock.'], 500);
}

responder(true, [
    'product_id' => $id,
    'stock'      => $stockFinal,
], 'Stock actualizado correctamente.');
