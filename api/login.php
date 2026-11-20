<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (metodo() !== 'POST') {
    responder(false, [], '', ['Método no permitido.'], 405);
}
if (!csrfValidar(csrfTokenPeticion())) {
    responder(false, [], '', ['Token CSRF inválido.'], 403);
}

$body = leerJson();
$email = trim((string) ($body['email'] ?? ''));
$password = (string) ($body['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    responder(false, [], '', ['Email o contraseña no válidos.'], 400);
}

$stmt = db()->prepare('SELECT id, email, password, name FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$usuario = $stmt->fetch();

if ($usuario === false || !password_verify($password, $usuario['password'])) {
    responder(false, [], '', ['Credenciales incorrectas.'], 401);
}

iniciarSesion();
session_regenerate_id(true);
$_SESSION['user_id'] = (int) $usuario['id'];
$_SESSION['user_name'] = $usuario['name'];
$_SESSION['user_email'] = $usuario['email'];
// Rotar el token al cambiar de privilegios.
unset($_SESSION['csrf_token']);
$nuevoToken = csrfToken();

responder(true, [
    'user' => [
        'id'    => (int) $usuario['id'],
        'name'  => $usuario['name'],
        'email' => $usuario['email'],
    ],
    'csrf_token' => $nuevoToken,
], 'Sesión iniciada correctamente.');
