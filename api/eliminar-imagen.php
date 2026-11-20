<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cloudinary.php';

/** Elimina una imagen de Cloudinary por public_id. Solo admin. */
if (metodo() !== 'POST') {
    responder(false, [], '', ['Método no permitido.'], 405);
}
requerirLoginApi();

$body = leerJson();
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($body['csrf_token'] ?? null);
if (!csrfValidar(is_string($token) ? $token : null)) {
    responder(false, [], '', ['Token CSRF inválido.'], 403);
}

$publicId = trim((string) ($body['public_id'] ?? ''));
if ($publicId === '') {
    responder(false, [], '', ['public_id no válido.'], 400);
}
if (!cloudinaryEliminar($publicId)) {
    responder(false, [], '', ['No se pudo eliminar la imagen.'], 500);
}

responder(true, [], 'Imagen eliminada correctamente.');
