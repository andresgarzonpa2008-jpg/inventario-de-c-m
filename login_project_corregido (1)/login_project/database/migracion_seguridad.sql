-- =====================================================================
-- MIGRACIÓN DE SEGURIDAD Y CORRECCIONES ESTRUCTURALES - proyecto_2
-- Aplicar UNA sola vez sobre la base de datos existente.
-- Hazlo primero sobre una copia/backup antes de producción.
-- =====================================================================

USE `proyecto_2`;

-- ---------------------------------------------------------------------
-- 1. CLAVES PRIMARIAS FALTANTES
--    `productos`, `categoria` y `catalogo` no tienen PK ni AUTO_INCREMENT
--    en el dump actual. Sin esto no se garantiza unicidad de PRO_codigo
--    y el ORM/PDO no puede confiar en filas únicas.
-- ---------------------------------------------------------------------
ALTER TABLE `productos`
  ADD PRIMARY KEY (`PRO_codigo`);

ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `catalogo`
  ADD PRIMARY KEY (`CAT_id_producto`);

-- ---------------------------------------------------------------------
-- 2. ROL 'admin' E 'inventario' NO EXISTÍAN EN EL ENUM
--    El código en index.php enruta a estos roles pero la BD los rechaza.
-- ---------------------------------------------------------------------
ALTER TABLE `usuarios`
  MODIFY `rol` ENUM('cliente','proveedor','gerente','admin','inventario')
  NOT NULL DEFAULT 'cliente';

-- Corrige registros con rol vacío ('') detectados en el dump (usuario id=4)
UPDATE `usuarios` SET `rol` = 'cliente' WHERE `rol` = '' OR `rol` IS NULL;

-- ---------------------------------------------------------------------
-- 3. ELIMINACIÓN LÓGICA (SOFT DELETE)
--    Se agrega deleted_at a las tablas que hoy se borran físicamente.
-- ---------------------------------------------------------------------
ALTER TABLE `usuarios`
  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `rol`;

ALTER TABLE `productos`
  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `GER_id`;

ALTER TABLE `ventas`
  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `estado`;

-- ---------------------------------------------------------------------
-- 4. ÍNDICES PARA BÚSQUEDA / FILTROS / ORDENAMIENTO (rendimiento)
-- ---------------------------------------------------------------------
ALTER TABLE `usuarios`
  ADD INDEX `idx_usuarios_nombre` (`nombre`, `apellido`),
  ADD INDEX `idx_usuarios_correo` (`correo`),
  ADD INDEX `idx_usuarios_deleted` (`deleted_at`);

ALTER TABLE `productos`
  ADD INDEX `idx_productos_nombre` (`PRO_nombre_producto`),
  ADD INDEX `idx_productos_deleted` (`deleted_at`);

ALTER TABLE `ventas`
  ADD INDEX `idx_ventas_deleted` (`deleted_at`);

-- ---------------------------------------------------------------------
-- 5. TABLA DE AUDITORÍA FALTANTE
--    Los triggers `tr_auditoria_producto` y `tr_auditoria_producto_delete`
--    insertan en `auditoria_productos`, pero esa tabla NUNCA fue creada
--    en el dump original. Hoy, al borrar un producto, esos triggers
--    fallarían con error "tabla no existe" y el DELETE completo se
--    revertiría. Se crea la tabla que faltaba.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auditoria_productos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `codigo_producto` INT NOT NULL,
  `nombre_producto` VARCHAR(255) NOT NULL,
  `fecha_eliminacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accion` VARCHAR(30) NOT NULL DEFAULT 'ELIMINADO',
  INDEX `idx_auditoria_fecha` (`fecha_eliminacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 6. TRIGGER DUPLICADO DE AUDITORÍA
--    tr_auditoria_producto y tr_auditoria_producto_delete hacen lo mismo:
--    cada DELETE físico generaba DOS filas de auditoría. Se deja solo uno.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS `tr_auditoria_producto_delete`;

-- ---------------------------------------------------------------------
-- 7. NUEVO TRIGGER: auditar también el borrado LÓGICO
--    Como ahora el borrado marca deleted_at en vez de hacer DELETE,
--    el trigger antiguo (AFTER DELETE) ya no se dispara casi nunca.
--    Este nuevo trigger audita cuando deleted_at pasa de NULL a una fecha.
-- ---------------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `tr_auditoria_producto_soft_delete`
AFTER UPDATE ON `productos`
FOR EACH ROW
BEGIN
    IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL THEN
        INSERT INTO auditoria_productos (codigo_producto, nombre_producto, fecha_eliminacion, accion)
        VALUES (NEW.PRO_codigo, NEW.PRO_nombre_producto, NOW(), 'ELIMINADO_LOGICO');
    END IF;
END$$
DELIMITER ;

-- =====================================================================
-- FIN DE LA MIGRACIÓN
-- =====================================================================
