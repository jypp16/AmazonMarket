-- ============================================
-- PASO 1: Eliminar todos los usuarios excepto Admin y Alex
-- ============================================
DELETE FROM venta WHERE id_usuario NOT IN (1, 4);
DELETE FROM usuario WHERE id_usuario NOT IN (1, 4);

-- ============================================
-- PASO 2: Agregar 3 vendedores nuevos
-- ============================================
INSERT INTO usuario (id_rol, username, password_hash, nombre, dni, telefono, direccion, email, fecha_registro, estado) VALUES
(2, 'maria', '$2y$10$8ACcHCjjO1IMBUD4lEdZe.EXd7GJ7Z6ucM4dM8YAfga0Q3I2gXnr.', 'Maria Lopez', '45678901', '911222333', 'Av. Los Olivos 456', 'maria@mail.com', NOW(), 1),
(2, 'carlos', '$2y$10$8ACcHCjjO1IMBUD4lEdZe.EXd7GJ7Z6ucM4dM8YAfga0Q3I2gXnr.', 'Carlos Ramirez', '78901234', '922333444', 'Jr. San Martin 789', 'carlos@mail.com', NOW(), 1),
(2, 'ana', '$2y$10$8ACcHCjjO1IMBUD4lEdZe.EXd7GJ7Z6ucM4dM8YAfga0Q3I2gXnr.', 'Ana Torres', '34567890', '933444555', 'Calle Comercio 321', 'ana@mail.com', NOW(), 1);

-- ============================================
-- PASO 3: Eliminar ventas existentes
-- ============================================
DELETE FROM detalle_venta;
DELETE FROM venta;

-- ============================================
-- PASO 4: Generar 100 ventas distribuidas entre 4 vendedores
--   - Alex (id=4):     35 ventas
--   - Maria (id=6):    28 ventas
--   - Carlos (id=7):   22 ventas
--   - Ana (id=8):      15 ventas
-- ============================================

SET @counter = 0;

-- --- Alex: 35 ventas ---
WHILE @counter < 35 DO
  SET @counter = @counter + 1;
  SET @id_cliente = FLOOR(1 + RAND() * 13);
  SET @id_comprobante = IF(RAND() < 0.6, 1, 2);
  SET @id_metodo = IF(RAND() < 0.7, 1, FLOOR(1 + RAND() * 3));
  SET @serie = IF(@id_comprobante = 1, 'B001', 'F001');
  SET @numero = LPAD(@counter, 7, '0');
  SET @fecha = DATE_ADD('2026-01-01', INTERVAL FLOOR(RAND() * 180) DAY);
  SET @total = ROUND(5 + RAND() * 495, 2);

  INSERT INTO venta (id_cliente, id_usuario, id_tipo_comprobante, serie, numero, fecha_venta, total, id_metodo_pago, estado)
  VALUES (@id_cliente, 4, @id_comprobante, @serie, @numero, @fecha, @total, @id_metodo, 1);

  SET @id_venta = LAST_INSERT_ID();
  SET @num_items = FLOOR(1 + RAND() * 5);

  SET @k = 0;
  WHILE @k < @num_items DO
    SET @k = @k + 1;
    SET @id_prod = FLOOR(1 + RAND() * 35);
    SET @cant = ROUND(1 + RAND() * 9, 2);
    SET @precio = (SELECT precio_venta FROM producto WHERE id_producto = @id_prod);
    SET @sub = ROUND(@cant * @precio, 2);

    INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal)
    VALUES (@id_venta, @id_prod, @cant, @precio, @sub);
  END WHILE;
END WHILE;

-- --- Maria: 28 ventas ---
SET @counter = 0;
WHILE @counter < 28 DO
  SET @counter = @counter + 1;
  SET @id_cliente = FLOOR(1 + RAND() * 13);
  SET @id_comprobante = IF(RAND() < 0.6, 1, 2);
  SET @id_metodo = IF(RAND() < 0.7, 1, FLOOR(1 + RAND() * 3));
  SET @serie = IF(@id_comprobante = 1, 'B001', 'F001');
  SET @numero = LPAD(35 + @counter, 7, '0');
  SET @fecha = DATE_ADD('2026-01-01', INTERVAL FLOOR(RAND() * 180) DAY);
  SET @total = ROUND(5 + RAND() * 495, 2);

  INSERT INTO venta (id_cliente, id_usuario, id_tipo_comprobante, serie, numero, fecha_venta, total, id_metodo_pago, estado)
  VALUES (@id_cliente, 6, @id_comprobante, @serie, @numero, @fecha, @total, @id_metodo, 1);

  SET @id_venta = LAST_INSERT_ID();
  SET @num_items = FLOOR(1 + RAND() * 5);

  SET @k = 0;
  WHILE @k < @num_items DO
    SET @k = @k + 1;
    SET @id_prod = FLOOR(1 + RAND() * 35);
    SET @cant = ROUND(1 + RAND() * 9, 2);
    SET @precio = (SELECT precio_venta FROM producto WHERE id_producto = @id_prod);
    SET @sub = ROUND(@cant * @precio, 2);

    INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal)
    VALUES (@id_venta, @id_prod, @cant, @precio, @sub);
  END WHILE;
END WHILE;

-- --- Carlos: 22 ventas ---
SET @counter = 0;
WHILE @counter < 22 DO
  SET @counter = @counter + 1;
  SET @id_cliente = FLOOR(1 + RAND() * 13);
  SET @id_comprobante = IF(RAND() < 0.6, 1, 2);
  SET @id_metodo = IF(RAND() < 0.7, 1, FLOOR(1 + RAND() * 3));
  SET @serie = IF(@id_comprobante = 1, 'B001', 'F001');
  SET @numero = LPAD(63 + @counter, 7, '0');
  SET @fecha = DATE_ADD('2026-01-01', INTERVAL FLOOR(RAND() * 180) DAY);
  SET @total = ROUND(5 + RAND() * 495, 2);

  INSERT INTO venta (id_cliente, id_usuario, id_tipo_comprobante, serie, numero, fecha_venta, total, id_metodo_pago, estado)
  VALUES (@id_cliente, 7, @id_comprobante, @serie, @numero, @fecha, @total, @id_metodo, 1);

  SET @id_venta = LAST_INSERT_ID();
  SET @num_items = FLOOR(1 + RAND() * 5);

  SET @k = 0;
  WHILE @k < @num_items DO
    SET @k = @k + 1;
    SET @id_prod = FLOOR(1 + RAND() * 35);
    SET @cant = ROUND(1 + RAND() * 9, 2);
    SET @precio = (SELECT precio_venta FROM producto WHERE id_producto = @id_prod);
    SET @sub = ROUND(@cant * @precio, 2);

    INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal)
    VALUES (@id_venta, @id_prod, @cant, @precio, @sub);
  END WHILE;
END WHILE;

-- --- Ana: 15 ventas ---
SET @counter = 0;
WHILE @counter < 15 DO
  SET @counter = @counter + 1;
  SET @id_cliente = FLOOR(1 + RAND() * 13);
  SET @id_comprobante = IF(RAND() < 0.6, 1, 2);
  SET @id_metodo = IF(RAND() < 0.7, 1, FLOOR(1 + RAND() * 3));
  SET @serie = IF(@id_comprobante = 1, 'B001', 'F001');
  SET @numero = LPAD(85 + @counter, 7, '0');
  SET @fecha = DATE_ADD('2026-01-01', INTERVAL FLOOR(RAND() * 180) DAY);
  SET @total = ROUND(5 + RAND() * 495, 2);

  INSERT INTO venta (id_cliente, id_usuario, id_tipo_comprobante, serie, numero, fecha_venta, total, id_metodo_pago, estado)
  VALUES (@id_cliente, 8, @id_comprobante, @serie, @numero, @fecha, @total, @id_metodo, 1);

  SET @id_venta = LAST_INSERT_ID();
  SET @num_items = FLOOR(1 + RAND() * 5);

  SET @k = 0;
  WHILE @k < @num_items DO
    SET @k = @k + 1;
    SET @id_prod = FLOOR(1 + RAND() * 35);
    SET @cant = ROUND(1 + RAND() * 9, 2);
    SET @precio = (SELECT precio_venta FROM producto WHERE id_producto = @id_prod);
    SET @sub = ROUND(@cant * @precio, 2);

    INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal)
    VALUES (@id_venta, @id_prod, @cant, @precio, @sub);
  END WHILE;
END WHILE;

-- ============================================
-- VERIFICACION
-- ============================================
SELECT 'USUARIOS' as tabla;
SELECT id_usuario, username, nombre, id_rol FROM usuario;

SELECT 'VENTAS POR VENDEDOR' as info;
SELECT u.nombre, COUNT(v.id_venta) as num_ventas, SUM(v.total) as ingresos
FROM venta v
JOIN usuario u ON v.id_usuario = u.id_usuario
GROUP BY v.id_usuario;

SELECT 'TOTAL VENTAS' as info;
SELECT COUNT(*) as total FROM venta;

SELECT 'DETALLE' as info;
SELECT COUNT(*) as items FROM detalle_venta;
