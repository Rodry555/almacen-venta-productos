-- Actualiza ÚNICAMENTE los precios de los 60 productos del catálogo.
-- No toca stock, reservas, movimientos ni imágenes: se puede ejecutar más de una vez.
-- Fuente: Fidalga (Santa Cruz de la Sierra), noviembre de 2026. Se usa el precio
-- final mostrado por la tienda (promocional cuando aplica).
--
-- Úsalo cuando la BD ya tenga el catálogo cargado con price = 0.00. Para una BD
-- nueva, ejecuta directamente database/seed.sql.

USE `test`;
SET NAMES utf8mb4;

START TRANSACTION;

UPDATE products
SET price = CASE id
    WHEN 1  THEN 13.00
    WHEN 2  THEN 103.00
    WHEN 3  THEN 22.00
    WHEN 4  THEN 6.90
    WHEN 5  THEN 13.70
    WHEN 6  THEN 7.80
    WHEN 7  THEN 18.00
    WHEN 8  THEN 12.00
    WHEN 9  THEN 0.60
    WHEN 10 THEN 2.20
    WHEN 11 THEN 2.20
    WHEN 12 THEN 2.30
    WHEN 13 THEN 2.30
    WHEN 14 THEN 2.30
    WHEN 15 THEN 2.60
    WHEN 16 THEN 3.30
    WHEN 17 THEN 10.50
    WHEN 18 THEN 26.00
    WHEN 19 THEN 30.50
    WHEN 20 THEN 2.00
    WHEN 21 THEN 14.50
    WHEN 22 THEN 36.50
    WHEN 23 THEN 8.50
    WHEN 24 THEN 30.50
    WHEN 25 THEN 95.00
    WHEN 26 THEN 3.60
    WHEN 27 THEN 9.00
    WHEN 28 THEN 22.50
    WHEN 29 THEN 10.50
    WHEN 30 THEN 11.00
    WHEN 31 THEN 51.00
    WHEN 32 THEN 26.50
    WHEN 33 THEN 15.00
    WHEN 34 THEN 25.00
    WHEN 35 THEN 7.00
    WHEN 36 THEN 25.00
    WHEN 37 THEN 36.00
    WHEN 38 THEN 2.00
    WHEN 39 THEN 8.40
    WHEN 40 THEN 16.00
    WHEN 41 THEN 38.70
    WHEN 42 THEN 29.00
    WHEN 43 THEN 25.00
    WHEN 44 THEN 44.00
    WHEN 45 THEN 9.50
    WHEN 46 THEN 15.50
    WHEN 47 THEN 61.00
    WHEN 48 THEN 10.80
    WHEN 49 THEN 70.00
    WHEN 50 THEN 18.90
    WHEN 51 THEN 89.00
    WHEN 52 THEN 52.50
    WHEN 53 THEN 75.00
    WHEN 54 THEN 122.50
    WHEN 55 THEN 39.20
    WHEN 56 THEN 9.80
    WHEN 57 THEN 25.20
    WHEN 58 THEN 35.00
    WHEN 59 THEN 9.80
    WHEN 60 THEN 8.90
END
WHERE id BETWEEN 1 AND 60;

COMMIT;
