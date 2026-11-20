<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/** Lee una variable de entorno como cadena limpia. */
function cloudinaryValor(string $clave): string
{
    $valor = getenv($clave);
    return $valor === false ? '' : $valor;
}

/** ¿Hay credenciales suficientes para subir imágenes? */
function cloudinaryConfigurado(): bool
{
    return cloudinaryValor('CLOUDINARY_CLOUD_NAME') !== ''
        && cloudinaryValor('CLOUDINARY_API_KEY') !== ''
        && cloudinaryValor('CLOUDINARY_API_SECRET') !== '';
}

/**
 * Sube una imagen a Cloudinary usando una petición REST firmada.
 * No necesita Composer: la firma es sha1 de los parámetros ordenados + el secreto.
 * Devuelve ['ok'=>bool, 'url'=>..., 'public_id'=>...] o ['ok'=>false,'error'=>...].
 */
function cloudinarySubir(array $archivo): array
{
    if (!cloudinaryConfigurado()) {
        return ['ok' => false, 'error' => 'Cloudinary no está configurado. La imagen se omitió.'];
    }
    if (!isset($archivo['tmp_name'], $archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'No se recibió ninguna imagen válida.'];
    }
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'La extensión curl de PHP no está disponible.'];
    }

    $tipos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($archivo['tmp_name']);
    if (!isset($tipos[$mime])) {
        return ['ok' => false, 'error' => 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.'];
    }
    if ((int) $archivo['size'] > 5 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'La imagen supera los 5 MB.'];
    }

    $parametros = [
        'public_id' => 'productos/' . bin2hex(random_bytes(10)),
        'timestamp' => time(),
        'overwrite' => 'true',
    ];
    ksort($parametros);
    $cadena = '';
    foreach ($parametros as $clave => $valor) {
        $cadena .= $clave . '=' . $valor . '&';
    }
    $cadena .= cloudinaryValor('CLOUDINARY_API_SECRET');
    $parametros['api_key'] = cloudinaryValor('CLOUDINARY_API_KEY');
    $parametros['signature'] = sha1($cadena);

    $url = 'https://api.cloudinary.com/v1_1/'
        . rawurlencode(cloudinaryValor('CLOUDINARY_CLOUD_NAME')) . '/image/upload';
    $parametros['file'] = new CURLFile($archivo['tmp_name'], $mime, 'imagen.' . $tipos[$mime]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $parametros,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $respuesta = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($respuesta === false) {
        return ['ok' => false, 'error' => 'Error de red al subir la imagen: ' . $error];
    }
    $json = json_decode($respuesta, true);
    if (!is_array($json) || !isset($json['secure_url'], $json['public_id'])) {
        return ['ok' => false, 'error' => $json['error']['message'] ?? 'Respuesta inesperada de Cloudinary.'];
    }
    return [
        'ok'        => true,
        'url'       => (string) $json['secure_url'],
        'public_id' => (string) $json['public_id'],
    ];
}

/** Borra una imagen de Cloudinary por su public_id. */
function cloudinaryEliminar(string $publicId): bool
{
    if ($publicId === '' || !cloudinaryConfigurado() || !function_exists('curl_init')) {
        return false;
    }
    $parametros = ['public_id' => $publicId, 'timestamp' => time()];
    ksort($parametros);
    $cadena = '';
    foreach ($parametros as $clave => $valor) {
        $cadena .= $clave . '=' . $valor . '&';
    }
    $cadena .= cloudinaryValor('CLOUDINARY_API_SECRET');
    $parametros['api_key'] = cloudinaryValor('CLOUDINARY_API_KEY');
    $parametros['signature'] = sha1($cadena);

    $url = 'https://api.cloudinary.com/v1_1/'
        . rawurlencode(cloudinaryValor('CLOUDINARY_CLOUD_NAME')) . '/image/destroy';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $parametros,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $respuesta = curl_exec($ch);
    curl_close($ch);
    $json = is_string($respuesta) ? json_decode($respuesta, true) : null;
    return is_array($json) && isset($json['result']) && $json['result'] === 'ok';
}
