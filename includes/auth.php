<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/** Datos del administrador en sesión, o null si no ha iniciado sesión. */
function usuarioActual(): ?array
{
    iniciarSesion();
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'    => (int) $_SESSION['user_id'],
        'name'  => (string) ($_SESSION['user_name'] ?? ''),
        'email' => (string) ($_SESSION['user_email'] ?? ''),
    ];
}

/** Para páginas del panel admin: redirige al login. */
function verificarLogin(): void
{
    if (usuarioActual() === null) {
        header('Location: /login.php');
        exit;
    }
}

/** Para endpoints de la API: responde 401 en JSON si no hay sesión. */
function requerirLoginApi(): array
{
    $usuario = usuarioActual();
    if ($usuario === null) {
        responder(false, [], '', ['Inicia sesión como administrador.'], 401);
    }
    return $usuario;
}
