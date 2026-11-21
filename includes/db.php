<?php
declare(strict_types=1);

/**
 * Ruta al certificado CA cuando la conexión usa TLS.
 * Prioridad: DB_SSL_CA_B64 > DB_SSL_CA > CA del sistema (solo con DB_URL).
 */
function dbCaPath(bool $usarCaSistema = false): ?string
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
    if ($path !== false && $path !== '') {
        return $path;
    }

    // En producción (DB_URL) la imagen trae el CA del sistema instalado.
    if ($usarCaSistema && is_file('/etc/ssl/certs/ca-certificates.crt')) {
        return '/etc/ssl/certs/ca-certificates.crt';
    }

    return null;
}

/**
 * Configuración de la BD.
 *
 * Producción: una sola variable de entorno con la cadena completa:
 *   DB_URL=mysql://usuario:password@host:4000/test
 * (DATABASE_URL se acepta como alias.)
 *
 * Desarrollo local: se mantienen las variables separadas de docker compose.
 */
function dbConfig(): array
{
    $url = getenv('DB_URL');
    if ($url === false || $url === '') {
        $url = getenv('DATABASE_URL');
    }

    if ($url !== false && $url !== '') {
        $partes = parse_url($url);
        if ($partes === false || empty($partes['host'])) {
            throw new RuntimeException('DB_URL no es válida.');
        }

        $nombre = isset($partes['path']) ? ltrim($partes['path'], '/') : '';

        return [
            'host' => $partes['host'],
            'port' => isset($partes['port']) ? (string) $partes['port'] : '3306',
            'name' => $nombre !== '' ? $nombre : 'test',
            'user' => isset($partes['user']) ? rawurldecode($partes['user']) : '',
            'pass' => isset($partes['pass']) ? rawurldecode($partes['pass']) : '',
            'tls'  => true,
        ];
    }

    $pass = getenv('DB_PASS');

    return [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'test',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => ($pass === false) ? '' : $pass,
        'tls'  => false,
    ];
}

/**
 * Conexión PDO única por petición. Sirve para MySQL local y para TiDB Cloud.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = dbConfig();

    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    $ca = dbCaPath($cfg['tls']);
    if ($ca !== null) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
    }

    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);
    } catch (PDOException $e) {
        // Nunca exponer credenciales ni detalles de conexión al cliente.
        error_log('Error de conexión a BD: ' . $e->getMessage());
        http_response_code(500);
        die('Error de conexión con la base de datos.');
    }

    return $pdo;
}
