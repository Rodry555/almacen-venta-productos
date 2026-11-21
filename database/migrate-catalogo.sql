-- Migración para bases de datos que ya existían antes del catálogo nuevo.
-- No ejecutar sobre una BD recién creada: para eso está database/seed.sql.
--
-- ATENCIÓN: borra reservas, items, movimientos de stock, imágenes, productos y
-- categorías anteriores. Conserva los usuarios administradores (tabla users).
-- Las reservas antiguas no se pueden conservar porque sus IDs de producto
-- dejarían de existir. Haz una copia de seguridad antes de ejecutar este archivo.

USE `test`;
SET NAMES utf8mb4;

START TRANSACTION;

-- Orden importante por las claves foráneas: primero lo que referencia a products.
DELETE FROM stock_movements;
DELETE FROM reservation_items;
DELETE FROM reservations;
DELETE FROM product_images;
DELETE FROM products;
DELETE FROM categories;

INSERT INTO categories (id, name, description, is_active) VALUES
  (1, 'Bebidas & Licores', NULL, TRUE),
  (2, 'Despensa & Abarrotes', NULL, TRUE),
  (3, 'Lácteos', NULL, TRUE),
  (4, 'Café, Té & Mate', NULL, TRUE),
  (5, 'Limpieza del Hogar', NULL, TRUE),
  (6, 'Congelados', NULL, TRUE),
  (7, 'Bebés', NULL, TRUE),
  (8, 'Mascotas', NULL, TRUE);

INSERT INTO products (id, name, description, price, stock, stock_min, category_id, is_active) VALUES
  (1,  'Jugo de Manzana Ades 1 L', NULL, 13.00, 20, 5, 1, TRUE),
  (2,  'Fernet Branca 750 Ml', NULL, 103.00, 20, 5, 1, TRUE),
  (3,  'Gaseosa Coca-Cola Original 3 Lt', NULL, 22.00, 20, 5, 1, TRUE),
  (4,  'Cerveza Zero Grau Conti 269 ML', NULL, 6.90, 20, 5, 1, TRUE),
  (5,  'Gaseosa Sprite 2 L', NULL, 13.70, 20, 5, 1, TRUE),
  (6,  'Cerveza Paceña Pilsener Lata 269 ML', NULL, 7.80, 20, 5, 1, TRUE),
  (7,  'Néctar de Manzana Frutts Pura Vida 2 Lt', NULL, 18.00, 20, 5, 1, TRUE),
  (8,  'Néctar de Durazno Del Valle 1 L', NULL, 12.00, 20, 5, 1, TRUE),

  (9,  'Ají Panca Picante Sibarita 85 G', NULL, 0.60, 20, 5, 2, TRUE),
  (10, 'Gelatina Cereza Yeli 110 G', NULL, 2.20, 20, 5, 2, TRUE),
  (11, 'Gelatina de Naranja Yeli 110 G', NULL, 2.20, 20, 5, 2, TRUE),
  (12, 'Gelatina de Piña Yeli 110 G', NULL, 2.30, 20, 5, 2, TRUE),
  (13, 'Gelatina de Frambuesa Yeli 110 G', NULL, 2.30, 20, 5, 2, TRUE),
  (14, 'Gelatina de Frutilla Yeli 120 G', NULL, 2.30, 20, 5, 2, TRUE),
  (15, 'Ketchup Kris 60 G', NULL, 2.60, 20, 5, 2, TRUE),
  (16, 'Té con Canela Miki 20 Uni', NULL, 3.30, 20, 5, 2, TRUE),

  (17, 'Leche Fresca Natural Pil UHT 900 Ml', NULL, 10.50, 20, 5, 3, TRUE),
  (18, 'Mantequilla con Sal Pil 200 G', NULL, 26.00, 20, 5, 3, TRUE),
  (19, 'Yogurt Durazno Delizia 2 L', NULL, 30.50, 20, 5, 3, TRUE),
  (20, 'Pilfrut Manzana 200 Ml', NULL, 2.00, 20, 5, 3, TRUE),
  (21, 'Dulce de Leche Pil 250 G', NULL, 14.50, 20, 5, 3, TRUE),
  (22, 'Margarina Regia 850 G', NULL, 36.50, 20, 5, 3, TRUE),
  (23, 'Leche Soy Chocolate Pil 946 Ml', NULL, 8.50, 20, 5, 3, TRUE),
  (24, 'Yogurt Frutilla Delizia 2 L', NULL, 30.50, 20, 5, 3, TRUE),

  (25, 'Café Iguazú Clásico 200 G', NULL, 95.00, 20, 5, 4, TRUE),
  (26, 'Té de Canela y Clavo Ceylan 20 Uni', NULL, 3.60, 20, 5, 4, TRUE),
  (27, 'Té con Canela Windsor 20 Uni', NULL, 9.00, 20, 5, 4, TRUE),
  (28, 'Café Iguazú 50 G', NULL, 22.50, 20, 5, 4, TRUE),
  (29, 'Yerba Buen Día 250 G', NULL, 10.50, 20, 5, 4, TRUE),
  (30, 'Mate de Manzanilla Windsor 20 Uni', NULL, 11.00, 20, 5, 4, TRUE),
  (31, 'Té con Canela y Clavo Windsor 130 Uni', NULL, 51.00, 20, 5, 4, TRUE),
  (32, 'Té con Canela y Clavo Windsor 64 Uni', NULL, 26.50, 20, 5, 4, TRUE),

  (33, 'Servilleta de Papel Perlita 500 Unid', NULL, 15.00, 20, 5, 5, TRUE),
  (34, 'Antisarro Ola Maximus 550 Ml', NULL, 25.00, 20, 5, 5, TRUE),
  (35, 'Jabón Blanco Brillante Uno 220 G', NULL, 7.00, 20, 5, 5, TRUE),
  (36, 'Antigrasa Ola Maximus 890 Ml', NULL, 25.00, 20, 5, 5, TRUE),
  (37, 'Desinfectante Floral Ola Aromatic 1.8 Lt', NULL, 36.00, 20, 5, 5, TRUE),
  (38, 'Servilleta de Papel Scott 50 U', NULL, 2.00, 20, 5, 5, TRUE),
  (39, 'Esponjas Multiuso Bristar 4 Uni', NULL, 8.40, 20, 5, 5, TRUE),
  (40, 'Detergente en Polvo Hortencia Surf 800 G', NULL, 16.00, 20, 5, 5, TRUE),

  (41, 'Hamburguesa de Res Express Sofia 12 U', NULL, 38.70, 20, 5, 6, TRUE),
  (42, 'Papa Fritas Congelada Simplot 700 G', NULL, 29.00, 20, 5, 6, TRUE),
  (43, 'Nuggets Patitas Sofia 350 G', NULL, 25.00, 20, 5, 6, TRUE),
  (44, 'Milanesa de Res Sofia 500 G', NULL, 44.00, 20, 5, 6, TRUE),
  (45, 'Hielo en Cubito Oriental 3 K', NULL, 9.50, 20, 5, 6, TRUE),
  (46, 'Tortilla Harina de Trigo Don Taco 360 G', NULL, 15.50, 20, 5, 6, TRUE),
  (47, 'Hamburguesa de Res en Tubo Fridosita 12 Uni', NULL, 61.00, 20, 5, 6, TRUE),
  (48, 'Hamburguesa Clásica de Pollo Sofia 2 Uni', NULL, 10.80, 20, 5, 6, TRUE),

  (49, 'Jabón Líquido Simonds Afrecho 750 ml', NULL, 70.00, 20, 5, 7, TRUE),
  (50, 'Toallitas Húmedas Huggies Clásica 80 und', NULL, 18.90, 20, 5, 7, TRUE),
  (51, 'Pañales Huggies Triple Protección XXG 36 und', NULL, 89.00, 20, 5, 7, TRUE),
  (52, 'Shampoo Baby Simond''s 360 ml', NULL, 52.50, 20, 5, 7, TRUE),
  (53, 'Shampoo Johnson''s Baby Clásico 400 ml', NULL, 75.00, 20, 5, 7, TRUE),
  (54, 'Pañales Pampers Premium Care T/G 40 und', NULL, 122.50, 20, 5, 7, TRUE),

  (55, 'Alimento Knino Cachorro Raza Media/Grande 1.5 kg', NULL, 39.20, 20, 5, 8, TRUE),
  (56, 'Alimento Dog Chow Cachorro Pollo 100 Gr', NULL, 9.80, 20, 5, 8, TRUE),
  (57, 'Alimento Pedigree Rodeo Adulto Pollo 70 Gr', NULL, 25.20, 20, 5, 8, TRUE),
  (58, 'Snack Liofilizado Hígado de Res y Manzana 50 gr', NULL, 35.00, 20, 5, 8, TRUE),
  (59, 'Alimento Cat Chow Adulto Pollo 85 Gr', NULL, 9.80, 20, 5, 8, TRUE),
  (60, 'Alimento Felix Purina c/Atún 85 Gr', NULL, 8.90, 20, 5, 8, TRUE);

COMMIT;

-- Los INSERT usan IDs explícitos; dejamos el autoincremento listo para el siguiente alta.
ALTER TABLE categories AUTO_INCREMENT = 9;
ALTER TABLE products AUTO_INCREMENT = 61;
