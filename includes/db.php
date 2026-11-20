<?php
declare(strict_types=1);

/**
 * Devuelve la ruta al certificado CA de TiDB cuando se usa TLS.
 * Se admiten dos formas de configurarlo (en orden de prioridad):
 *   - DB_SSL_CA_B64: contenido del .pem en base64 (ideal para Render, sin montar archivos)
 *   - DB_SSL_CA:     ruta a un .pem dentro del contenedor
 * En local ambas variables quedan vacías y la conexión va sin TLS.
 */
function dbCaPath(): ?string
{
    $b64 = getenv('DB_SSL_CA_B64');
    if ($b64 !== false && $b64 !== '') {
        $decoded = base64_decode($b64, true);
        if ($decoded === false) {
            throw new RuntimeException('DB_SSL_CA_B64 no contiene base64 válido.');
        }
        // Archivo temporal efímero: el filesystem de Render no es persistente.
        $path = sys_get_temp_dir() . '/tidb-ca-' . md5($b64) . '.pem';
        if (!is_file($path) && file_put_contents($path, $decoded, LOCK_EX) === false) {
            throw new RuntimeException('No se pudo escribir el certificado CA temporal.');
        }
        return $path;
    }

    $path = getenv('DB_SSL_CA');
    return ($path !== false && $path !== '') ? $path : null;
}

/**
 * Conexión PDO única por petición.
 * La misma función sirve para MySQL local y para TiDB Cloud Starter.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'almacen';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS');
    $pass = ($pass === false) ? '' : $pass;

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    $ca = dbCaPath();
    if ($ca !== null) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
    }

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Nunca exponer credenciales ni detalles de conexión al cliente.
        error_log('Error de conexión a BD: ' . $e->getMessage());
        http_response_code(500);
        die('Error de conexión con la base de datos.');
    }

    return $pdo;
}
