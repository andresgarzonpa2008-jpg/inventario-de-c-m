-- =====================================================================
-- MIGRACIÓN: campos para novedades de órdenes de compra (modales proveedor)
-- Base de datos: proyecto_2 | MariaDB/MySQL 5.7+
-- =====================================================================

USE `proyecto_2`;

-- 1) Agregar a orden_compra la columna de "marcada como con retraso/novedad"
--    (TINYINT 0/1) y el campo de observaciones/notas libres.
ALTER TABLE `orden_compra`
    ADD COLUMN `ORD_retrasada` TINYINT(1) NOT NULL DEFAULT 0 AFTER `ORD_total`,
    ADD COLUMN `ORD_notas` TEXT NULL AFTER `ORD_retrasada`;

-- 2) Tabla de facturas subidas por proveedor (PDF/XML) para una orden de compra.
--    Se crea si todavía no existe (la crea el código automáticamente si falta).
CREATE TABLE IF NOT EXISTS `factura_orden_compra` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ORD_id_orden` INT NOT NULL,
    `archivo` VARCHAR(255) NOT NULL,
    `fecha_subida` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_foc_orden` (`ORD_id_orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;