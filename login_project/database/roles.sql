USE proyecto_2;

ALTER TABLE usuarios
MODIFY rol ENUM('usuario', 'cliente', 'proveedor', 'inventario', 'gerente', 'admin')
NOT NULL DEFAULT 'cliente';

ALTER TABLE productos
ADD COLUMN IF NOT EXISTS PRO_imagen_url VARCHAR(255) NULL AFTER PRO_nombre_producto;