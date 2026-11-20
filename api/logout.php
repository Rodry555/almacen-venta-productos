<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (metodo() !== 'POST') {
    responder(false, [], '', ['Método no permitido.'], 405);
}
if (usuarioActual() === null) {
    responder(false, [], '', ['No hay sesión activa.'], 401);
}
if (!csrfValidar(csrfTokenPeticion())) {
    responder(false, [], '', ['Token CSRF inválido.'], 403);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'] ?? '',
        (bool) $params['secure'],
        (bool) $params['httponly']
    );
}
session_destroy();

responder(true, [], 'Sesión cerrada correctamente.');
