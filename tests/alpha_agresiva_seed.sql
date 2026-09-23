USE tessa_control_pagos_alpha_agresiva_20260923;

DELIMITER $$
DROP PROCEDURE IF EXISTS seed_alpha_agresiva$$
CREATE PROCEDURE seed_alpha_agresiva()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_cursor INT DEFAULT 0;
    DECLARE v_inicio_clientes INT UNSIGNED;
    DECLARE v_titular INT UNSIGNED;
    DECLARE v_miembro_b INT UNSIGNED;
    DECLARE v_miembro_c INT UNSIGNED;
    DECLARE v_servicio_a INT UNSIGNED;
    DECLARE v_servicio_b INT UNSIGNED;
    DECLARE v_personal INT UNSIGNED;
    DECLARE v_contrato INT UNSIGNED;
    DECLARE v_hito_1 INT UNSIGNED;
    DECLARE v_hito_2 INT UNSIGNED;
    DECLARE v_cobranza INT UNSIGNED;
    DECLARE v_fecha DATE;
    DECLARE v_tipo VARCHAR(12);
    DECLARE v_medio VARCHAR(60);
    DECLARE v_operacion VARCHAR(100);

    SELECT MIN(id_servicio), MAX(id_servicio) INTO v_servicio_a, v_servicio_b FROM servicio;
    SELECT MIN(id_personal) INTO v_personal FROM personal;
    IF v_servicio_a IS NULL OR v_personal IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Faltan referencias para la prueba alfa agresiva.';
    END IF;

    START TRANSACTION;
    WHILE i <= 40000 DO
        INSERT INTO cliente(tipo,documento,nombres,apellidos,correo,telefono,direccion,estado)
        VALUES('NATURAL', CONCAT('8', LPAD(i, 10, '0')), 'Cliente', CONCAT('Estrés ', LPAD(i, 6, '0')),
               CONCAT('stress', i, '@example.test'), CONCAT('800', LPAD(i, 6, '0')), 'Dirección de prueba', 1);
        IF i = 1 THEN SET v_inicio_clientes = LAST_INSERT_ID(); END IF;
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 20000 DO
        SET v_fecha = DATE_ADD('2025-01-01', INTERVAL MOD(i, 620) DAY);
        SET v_tipo = IF(MOD(i, 10) < 4, 'INDIVIDUAL', 'GRUPAL');
        SET v_titular = v_inicio_clientes + v_cursor;
        SET v_cursor = v_cursor + 1;
        SET v_miembro_b = NULL;
        SET v_miembro_c = NULL;
        IF MOD(i, 10) BETWEEN 4 AND 6 THEN
            SET v_miembro_b = v_inicio_clientes + v_cursor;
            SET v_cursor = v_cursor + 1;
        ELSEIF MOD(i, 10) >= 7 THEN
            SET v_miembro_b = v_inicio_clientes + v_cursor;
            SET v_cursor = v_cursor + 1;
            SET v_miembro_c = v_inicio_clientes + v_cursor;
            SET v_cursor = v_cursor + 1;
        END IF;

        INSERT INTO contrato(con_numero,id_cliente,id_servicio,id_responsable,tipo,fecha,monto_bruto,descuento,monto_neto,estado,created_by)
        VALUES(CONCAT('ALPHA-STRESS-', LPAD(i, 6, '0')), v_titular,
               IF(MOD(i, 3) = 0, v_servicio_b, v_servicio_a), v_personal, v_tipo,
               v_fecha, 10000.00, 0.00, 10000.00, 'ACTIVO', v_personal);
        SET v_contrato = LAST_INSERT_ID();
        IF v_miembro_b IS NOT NULL THEN INSERT INTO contrato_integrante(id_contrato,id_cliente) VALUES(v_contrato, v_miembro_b); END IF;
        IF v_miembro_c IS NOT NULL THEN INSERT INTO contrato_integrante(id_contrato,id_cliente) VALUES(v_contrato, v_miembro_c); END IF;

        SET v_medio = ELT(MOD(i, 5) + 1, 'EFECTIVO', 'TRANSFERENCIA', 'YAPE', 'PLIN', 'TARJETA');
        SET v_operacion = IF(v_medio = 'EFECTIVO', NULL, CONCAT('ALPHA-OP-', i, '-1'));

        INSERT INTO contrato_hito(id_contrato,numero,descripcion,fecha_vencimiento,monto,monto_pagado,estado)
        VALUES(v_contrato, 1, 'Cuota 1', DATE_ADD(v_fecha, INTERVAL 30 DAY), 2400.00, 2400.00, 'PAGADO');
        SET v_hito_1 = LAST_INSERT_ID();
        INSERT INTO contrato_hito(id_contrato,numero,descripcion,fecha_vencimiento,monto,monto_pagado,estado)
        VALUES(v_contrato, 2, 'Cuota 2', DATE_ADD(v_fecha, INTERVAL 90 DAY), 3200.00,
               IF(v_tipo = 'INDIVIDUAL', 1500.00, IF(v_miembro_c IS NULL, 1300.00, 2000.00)), 'PARCIAL'),
              (v_contrato, 3, 'Cuota 3', DATE_ADD(v_fecha, INTERVAL 180 DAY), 4400.00, 0.00, 'PENDIENTE');
        SET v_hito_2 = LAST_INSERT_ID();

        IF v_tipo = 'INDIVIDUAL' THEN
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_titular, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 31 DAY), ' 09:00:00'), v_medio, v_operacion, 2400.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_1, 2400.00);
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_titular, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 91 DAY), ' 09:00:00'), v_medio, v_operacion, 1500.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_2, 1500.00);
        ELSEIF v_miembro_c IS NULL THEN
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_titular, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 31 DAY), ' 09:00:00'), v_medio, v_operacion, 700.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_1, 700.00);
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_miembro_b, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 31 DAY), ' 10:00:00'), v_medio, v_operacion, 1700.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_1, 1700.00);
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_titular, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 91 DAY), ' 09:00:00'), v_medio, v_operacion, 600.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_2, 600.00);
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_miembro_b, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 91 DAY), ' 10:00:00'), v_medio, v_operacion, 700.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_2, 700.00);
        ELSE
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_titular, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 31 DAY), ' 09:00:00'), v_medio, v_operacion, 500.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_1, 500.00);
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_miembro_b, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 31 DAY), ' 10:00:00'), v_medio, v_operacion, 800.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_1, 800.00);
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_miembro_c, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 31 DAY), ' 11:00:00'), v_medio, v_operacion, 1100.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_1, 1100.00);
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_titular, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 91 DAY), ' 09:00:00'), v_medio, v_operacion, 400.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_2, 400.00);
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_miembro_b, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 91 DAY), ' 10:00:00'), v_medio, v_operacion, 600.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_2, 600.00);
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total,estado)
            VALUES(v_contrato, v_miembro_c, v_personal, CONCAT(DATE_ADD(v_fecha, INTERVAL 91 DAY), ' 11:00:00'), v_medio, v_operacion, 1000.00, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID(); INSERT INTO cobranza_detalle VALUES(v_cobranza, v_hito_2, 1000.00);
        END IF;

        IF MOD(i, 2) = 0 THEN
            INSERT INTO egreso(fecha,categoria,beneficiario,concepto,monto,id_registrado_por,estado)
            VALUES(DATE_ADD(v_fecha, INTERVAL 15 DAY), 'OPERACION', 'Proveedor de estrés', CONCAT('Egreso alfa agresivo ', i),
                   100 + MOD(i, 20) * 30, v_personal, 'REGISTRADO');
        END IF;
        IF MOD(i, 97) = 0 THEN
            UPDATE contrato SET estado = 'ANULADO', motivo_anulacion = 'Caso de prueba alfa' WHERE id_contrato = v_contrato;
            UPDATE contrato_hito SET estado = 'CANCELADO' WHERE id_contrato = v_contrato AND monto_pagado = 0;
        END IF;
        SET i = i + 1;
    END WHILE;
    COMMIT;
END$$
DELIMITER ;

CALL seed_alpha_agresiva();
DROP PROCEDURE seed_alpha_agresiva;
