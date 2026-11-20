<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cloudinary.php';

/** Sube una imagen recibida como multipart/form-data. Solo admin. */
if (metodo() !== 'POST') {
    responder(false, [], '', ['Método no permitido.'], 405);
}
requerirLoginApi();

// En este endpoint el token viaja en el campo del formulario multipart.
if (!csrfValidar($_POST['csrf_token'] ?? null)) {
    responder(false, [], '', ['Token CSRF inválido.'], 403);
}

$resultado = cloudinarySubir($_FILES['imagen'] ?? []);
if (!$resultado['ok']) {
    responder(false, [], '', [$resultado['error']], 400);
}

responder(true, [
    'url'       => $resultado['url'],
    'public_id' => $resultado['public_id'],
], 'Imagen subida correctamente.');
