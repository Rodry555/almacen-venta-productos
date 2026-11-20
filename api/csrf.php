<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

// Entrega el token CSRF y la cookie de sesión. Necesario para los POST con fetch.
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['csrf_token' => csrfToken()], JSON_UNESCAPED_UNICODE);
