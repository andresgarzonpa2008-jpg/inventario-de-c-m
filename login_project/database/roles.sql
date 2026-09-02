USE proyecto_2;

ALTER TABLE usuarios
MODIFY rol ENUM('usuario', 'cliente', 'proveedor', 'inventario', 'gerente', 'admin')
NOT NULL DEFAULT 'cliente';