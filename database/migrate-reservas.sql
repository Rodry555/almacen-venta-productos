-- El carrito ya no pide email. La columna se conserva por compatibilidad,
-- pero debe permitir NULL para que la API pueda crear reservas sin ese dato.
-- Ejecutar una vez sobre la BD desplegada (TiDB Cloud SQL Editor o cliente MySQL).
USE `test`;
SET NAMES utf8mb4;

ALTER TABLE reservations MODIFY COLUMN customer_email VARCHAR(255) NULL;
