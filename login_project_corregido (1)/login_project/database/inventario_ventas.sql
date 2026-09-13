USE proyecto_2;

ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS PRO_descripcion TEXT NULL AFTER PRO_nombre_producto,
    ADD COLUMN IF NOT EXISTS PRO_marca VARCHAR(100) NULL AFTER PRO_descripcion,
    ADD COLUMN IF NOT EXISTS PRO_imagen_url VARCHAR(255) NULL AFTER PRO_marca;

CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    documento VARCHAR(40) NOT NULL UNIQUE,
    telefono VARCHAR(30) NULL,
    correo VARCHAR(150) NULL,
    direccion VARCHAR(200) NULL,
    ciudad VARCHAR(100) NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS proveedores (
    id_proveedor INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    nit VARCHAR(40) NOT NULL UNIQUE,
    telefono VARCHAR(30) NULL,
    correo VARCHAR(150) NULL,
    direccion VARCHAR(200) NULL,
    ciudad VARCHAR(100) NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ventas (
    id_venta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT UNSIGNED NOT NULL,
    fecha_venta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    estado ENUM('Pendiente','Pagada','Cancelada') NOT NULL DEFAULT 'Pendiente',
    CONSTRAINT fk_ventas_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    INDEX idx_ventas_fecha (fecha_venta),
    INDEX idx_ventas_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS venta_detalle (
    id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_venta INT UNSIGNED NOT NULL,
    codigo_producto INT NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_detalle_venta FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    INDEX idx_detalle_producto (codigo_producto)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT UNSIGNED NOT NULL,
    fecha_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    estado ENUM('Pendiente','Procesando','Enviado','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
    CONSTRAINT fk_pedidos_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    INDEX idx_pedidos_estado (estado),
    INDEX idx_pedidos_fecha (fecha_pedido)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS auditoria_productos_borrados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_producto INT NOT NULL,
    nombre_producto VARCHAR(255) NOT NULL,
    fecha_borrado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_auditoria_fecha (fecha_borrado)
) ENGINE=InnoDB;
