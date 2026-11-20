<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/** Categorías visibles en el catálogo público. */
function categoriasActivas(): array
{
    return db()->query(
        'SELECT id, name, description FROM categories WHERE is_active = 1 ORDER BY name ASC'
    )->fetchAll();
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
