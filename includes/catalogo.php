<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/** Categorías visibles en el catálogo público, con el número de productos activos. */
function categoriasActivas(): array
{
    return db()->query(
        'SELECT c.id, c.name, c.description,
                (SELECT COUNT(*) FROM products p
                 WHERE p.category_id = c.id AND p.is_active = 1) AS total_productos
         FROM categories c
         WHERE c.is_active = 1
         ORDER BY c.name ASC'
    )->fetchAll();
}

/**
 * Icono y tonos de cada categoría para las tarjetas. Es solo presentación:
 * no añade datos nuevos al catálogo y funciona también con categorías creadas
 * después por el administrador (usa el ID conocido o palabras del nombre).
 */
function categoriaIcono(int $id, string $nombre): string
{
    $porId = [
        1 => 'fa-wine-bottle', 2 => 'fa-basket-shopping', 3 => 'fa-mug-hot',
        4 => 'fa-coffee', 5 => 'fa-broom', 6 => 'fa-snowflake',
        7 => 'fa-baby', 8 => 'fa-paw',
    ];
    if (isset($porId[$id])) {
        return $porId[$id];
    }

    $nombre = mb_strtolower($nombre);
    $porNombre = [
        'bebida' => 'fa-wine-bottle', 'licor' => 'fa-wine-bottle',
        'despensa' => 'fa-basket-shopping', 'abarrote' => 'fa-basket-shopping',
        'lácteo' => 'fa-mug-hot', 'leche' => 'fa-mug-hot',
        'café' => 'fa-coffee', 'té' => 'fa-coffee', 'mate' => 'fa-coffee',
        'limpieza' => 'fa-broom', 'congelado' => 'fa-snowflake',
        'bebé' => 'fa-baby', 'mascota' => 'fa-paw',
    ];
    foreach ($porNombre as $clave => $icono) {
        if (mb_strpos($nombre, $clave) !== false) {
            return $icono;
        }
    }
    return 'fa-box';
}

/** Devuelve [clases del icono, clases del degradado] para una categoría. */
function categoriaTono(int $id, string $nombre): array
{
    $porId = [
        1 => ['bg-amber-50 text-amber-600', 'from-amber-100 via-orange-50 to-white'],
        2 => ['bg-orange-50 text-orange-600', 'from-orange-100 via-amber-50 to-white'],
        3 => ['bg-sky-50 text-sky-600', 'from-sky-100 via-cyan-50 to-white'],
        4 => ['bg-[#f5efe6] text-[#9a6b3f]', 'from-[#f5efe6] via-amber-50 to-white'],
        5 => ['bg-cyan-50 text-cyan-600', 'from-cyan-100 via-sky-50 to-white'],
        6 => ['bg-blue-50 text-blue-600', 'from-blue-100 via-sky-50 to-white'],
        7 => ['bg-rose-50 text-rose-500', 'from-rose-100 via-pink-50 to-white'],
        8 => ['bg-violet-50 text-violet-600', 'from-violet-100 via-purple-50 to-white'],
    ];
    return $porId[$id] ?? ['bg-brand-50 text-brand-600', 'from-brand-100 via-brand-50 to-white'];
}

/** Productos activos, con filtro opcional por categoría y búsqueda. */
function listarProductos(?int $categoria = null, string $q = ''): array
{
    $sql = 'SELECT p.*, c.name AS categoria, i.image_url
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images i ON i.product_id = p.id AND i.is_primary = 1
            WHERE p.is_active = 1';
    $params = [];

    if ($categoria !== null && $categoria > 0) {
        // Marcadores distintos: PDO con prepared statements nativos no permite repetir uno.
        $sql .= ' AND p.category_id = :cat';
        $params[':cat'] = $categoria;
    }
    if ($q !== '') {
        $sql .= ' AND (p.name LIKE :q1 OR p.description LIKE :q2)';
        $params[':q1'] = '%' . $q . '%';
        $params[':q2'] = '%' . $q . '%';
    }
    $sql .= ' ORDER BY p.id DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Un producto activo por ID, o null. */
function productoPorId(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS categoria, i.image_url, i.image_public_id
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN product_images i ON i.product_id = p.id AND i.is_primary = 1
         WHERE p.id = :id AND p.is_active = 1
         LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch();
    return $producto === false ? null : $producto;
}

/** Productos recientes para la portada. El límite se sanea antes de interpolarlo. */
function productosDestacados(int $limite = 6): array
{
    $limite = max(1, min(12, $limite));
    $stmt = db()->query(
        'SELECT p.*, c.name AS categoria, i.image_url
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN product_images i ON i.product_id = p.id AND i.is_primary = 1
         WHERE p.is_active = 1
         ORDER BY p.id DESC
         LIMIT ' . $limite
    );
    return $stmt->fetchAll();
}
