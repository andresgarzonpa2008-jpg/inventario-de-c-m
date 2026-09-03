USE proyecto_2;

ALTER TABLE productos
	ADD COLUMN IF NOT EXISTS PRO_descripcion TEXT NULL AFTER PRO_nombre_producto,
	ADD COLUMN IF NOT EXISTS PRO_marca VARCHAR(100) NULL AFTER PRO_descripcion,
	ADD COLUMN IF NOT EXISTS PRO_imagen_url VARCHAR(255) NULL AFTER PRO_marca;

INSERT INTO categoria (nombre_categoria, descripcion)
SELECT 'Abrasivos', 'Discos, lijas y herramientas abrasivas industriales'
WHERE NOT EXISTS (SELECT 1 FROM categoria WHERE nombre_categoria = 'Abrasivos');

SET @categoria = (SELECT id_categoria FROM categoria WHERE nombre_categoria = 'Abrasivos' LIMIT 1);

INSERT INTO productos
(PRO_codigo, PRO_nombre_producto, PRO_descripcion, PRO_marca, PRO_imagen_url,
 PRO_precio_unitario, PRO_stock_actual, PRO_stock_minimo, PRO_cantidad_disponible,
 id_categoria, id_tipo_material, PRO_costo_base)
SELECT 1001, 'Disco de corte 4 1/2 pulgadas', 'Disco abrasivo para corte de acero y metal.', '3M', NULL, 8500, 50, 10, 50, @categoria, 1, 6000
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE PRO_codigo = 1001);

INSERT INTO productos
(PRO_codigo, PRO_nombre_producto, PRO_descripcion, PRO_marca, PRO_imagen_url,
 PRO_precio_unitario, PRO_stock_actual, PRO_stock_minimo, PRO_cantidad_disponible,
 id_categoria, id_tipo_material, PRO_costo_base)
SELECT 1002, 'Disco de desbaste 4 1/2 pulgadas', 'Disco de alto rendimiento para desbaste de metales.', 'Norton', NULL, 12500, 35, 8, 35, @categoria, 1, 9000
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE PRO_codigo = 1002);

INSERT INTO productos
(PRO_codigo, PRO_nombre_producto, PRO_descripcion, PRO_marca, PRO_imagen_url,
 PRO_precio_unitario, PRO_stock_actual, PRO_stock_minimo, PRO_cantidad_disponible,
 id_categoria, id_tipo_material, PRO_costo_base)
SELECT 1003, 'Lija de agua grano 120', 'Lija para acabado fino de superficies metálicas y pintura.', 'Fandeli', NULL, 3200, 80, 15, 80, @categoria, 1, 2100
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE PRO_codigo = 1003);

INSERT INTO productos
(PRO_codigo, PRO_nombre_producto, PRO_descripcion, PRO_marca, PRO_imagen_url,
 PRO_precio_unitario, PRO_stock_actual, PRO_stock_minimo, PRO_cantidad_disponible,
 id_categoria, id_tipo_material, PRO_costo_base)
SELECT 1004, 'Disco flap grano 40', 'Disco laminado para lijado y desbaste de acero.', 'Carborundum', NULL, 18500, 24, 6, 24, @categoria, 1, 13500
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE PRO_codigo = 1004);

INSERT INTO productos
(PRO_codigo, PRO_nombre_producto, PRO_descripcion, PRO_marca, PRO_imagen_url,
 PRO_precio_unitario, PRO_stock_actual, PRO_stock_minimo, PRO_cantidad_disponible,
 id_categoria, id_tipo_material, PRO_costo_base)
SELECT 1005, 'Cepillo circular de alambre', 'Cepillo para remover óxido, pintura y residuos metálicos.', 'Truper', NULL, 22000, 18, 5, 18, @categoria, 1, 16000
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE PRO_codigo = 1005);

INSERT INTO productos
(PRO_codigo, PRO_nombre_producto, PRO_descripcion, PRO_marca, PRO_imagen_url,
 PRO_precio_unitario, PRO_stock_actual, PRO_stock_minimo, PRO_cantidad_disponible,
 id_categoria, id_tipo_material, PRO_costo_base)
SELECT 1006, 'Banda lijadora 75 x 457 mm', 'Banda abrasiva para lijado de madera y metal.', 'Abracol', NULL, 9800, 40, 10, 40, @categoria, 1, 7000
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE PRO_codigo = 1006);
