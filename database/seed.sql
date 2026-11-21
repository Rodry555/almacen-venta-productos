USE `test`;
SET NAMES utf8mb4;

-- Administrador de producción.
-- Hash bcrypt con coste 12, compatible con password_verify().
INSERT INTO users (email, password, name) VALUES
  ('admin@gmail.com', '$2y$12$MBQRzYzStniZNm.alMkypuEFMkvMS2ljtlNibyjP5Myqge/kn0bUu', 'Administrador');

INSERT INTO categories (id, name, description, is_active) VALUES
  (1, 'Papelería', 'Cuadernos, lápices y útiles de escritura', TRUE),
  (2, 'Oficina', 'Material de oficina y organización', TRUE),
  (3, 'Limpieza', 'Productos de limpieza', FALSE);

INSERT INTO products (id, name, description, price, stock, stock_min, category_id, is_active) VALUES
  (1, 'Cuaderno A5', 'Cuaderno de 100 hojas, tapas duras', 10.00, 5, 3, 1, TRUE),
  (2, 'Lápiz HB', 'Lápiz de madera, mina HB', 2.50, 20, 5, 1, TRUE),
  (3, 'Resaltador amarillo', 'Resaltador de tinta fluorescente', 3.00, 2, 5, 2, TRUE);

-- Imagen de ejemplo alojada en el cloud de demostración de Cloudinary.
INSERT INTO product_images (product_id, image_url, image_public_id, is_primary, sort_order) VALUES
  (1, 'https://res.cloudinary.com/demo/image/upload/sample.jpg', 'sample', TRUE, 0);
