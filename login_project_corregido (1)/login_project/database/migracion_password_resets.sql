-- =====================================================================
-- MIGRACIÓN: tabla de tokens para recuperación de contraseña
-- Base de datos: proyecto_2 | MariaDB/MySQL 5.7+
-- (El sistema también la crea automáticamente al primer uso)
-- =====================================================================

USE `proyecto_2`;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expiracion` DATETIME NOT NULL,
    `usado` TINYINT(1) NOT NULL DEFAULT 0,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pwr_token` (`token`),
    KEY `idx_pwr_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;