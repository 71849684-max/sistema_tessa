USE tessa_control_pagos_alpha_20260923;

DELIMITER $$
DROP PROCEDURE IF EXISTS seed_alpha$$
CREATE PROCEDURE seed_alpha()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_cliente_a INT UNSIGNED;
    DECLARE v_cliente_b INT UNSIGNED;
    DECLARE v_servicio_a INT UNSIGNED;
    DECLARE v_servicio_b INT UNSIGNED;
    DECLARE v_personal INT UNSIGNED;
    DECLARE v_contrato INT UNSIGNED;
    DECLARE v_hito INT UNSIGNED;
    DECLARE v_cobranza INT UNSIGNED;
    DECLARE v_fecha DATE;
    DECLARE v_pagado DECIMAL(12,2);

    SELECT MIN(id_cliente), MAX(id_cliente) INTO v_cliente_a, v_cliente_b FROM cliente;
    SELECT MIN(id_servicio), MAX(id_servicio) INTO v_servicio_a, v_servicio_b FROM servicio;
    SELECT MIN(id_personal) INTO v_personal FROM personal;
    IF v_cliente_a IS NULL OR v_cliente_b IS NULL OR v_servicio_a IS NULL OR v_personal IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La base clonada no contiene referencias para la simulación.';
    END IF;

    START TRANSACTION;
    WHILE i <= 5000 DO
        SET v_fecha = DATE_ADD('2025-01-01', INTERVAL MOD(i, 620) DAY);
        INSERT INTO contrato(con_numero,id_cliente,id_servicio,id_responsable,tipo,fecha,monto_bruto,descuento,monto_neto,estado,created_by)
        VALUES(
            CONCAT('ALPHA-2026-', LPAD(i, 6, '0')),
            IF(MOD(i, 2) = 0, v_cliente_a, v_cliente_b),
            IF(MOD(i, 3) = 0, v_servicio_b, v_servicio_a),
            v_personal,
            IF(MOD(i, 5) = 0, 'GRUPAL', 'INDIVIDUAL'),
            v_fecha, 6400.00, 0.00, 6400.00, 'ACTIVO', v_personal
        );
        SET v_contrato = LAST_INSERT_ID();
        IF MOD(i, 5) = 0 THEN
            INSERT INTO contrato_integrante(id_contrato,id_cliente)
            VALUES(v_contrato, IF(MOD(i, 2) = 0, v_cliente_b, v_cliente_a));
        END IF;

        SET v_pagado = IF(MOD(i, 4) = 0, 1500.00, IF(MOD(i, 4) = 1, 500.00, 0.00));
        INSERT INTO contrato_hito(id_contrato,numero,descripcion,fecha_vencimiento,monto,monto_pagado,estado)
        VALUES(v_contrato, 1, 'Cuota 1', DATE_ADD(v_fecha, INTERVAL 30 DAY), 1500.00, v_pagado,
            IF(v_pagado = 1500.00, 'PAGADO', IF(v_pagado > 0, 'PARCIAL', 'PENDIENTE')));
        SET v_hito = LAST_INSERT_ID();
        IF v_pagado > 0 THEN
            INSERT INTO cobranza(id_contrato,id_cliente_pagador,id_registrado_por,fecha,medio_pago,monto_total,estado)
            VALUES(v_contrato, IF(MOD(i, 2) = 0, v_cliente_a, v_cliente_b), v_personal,
                CONCAT(DATE_ADD(v_fecha, INTERVAL 31 DAY), ' 10:00:00'), 'EFECTIVO', v_pagado, 'REGISTRADO');
            SET v_cobranza = LAST_INSERT_ID();
            INSERT INTO cobranza_detalle(id_cobranza,id_hito,monto_aplicado) VALUES(v_cobranza, v_hito, v_pagado);
        END IF;

        INSERT INTO contrato_hito(id_contrato,numero,descripcion,fecha_vencimiento,monto,monto_pagado,estado)
        VALUES(v_contrato, 2, 'Cuota 2', DATE_ADD(v_fecha, INTERVAL 90 DAY), 2100.00, 0.00, 'PENDIENTE'),
              (v_contrato, 3, 'Cuota 3', DATE_ADD(v_fecha, INTERVAL 180 DAY), 2800.00, 0.00, 'PENDIENTE');

        IF MOD(i, 2) = 0 THEN
            INSERT INTO egreso(fecha,categoria,beneficiario,concepto,monto,id_registrado_por,estado)
            VALUES(DATE_ADD(v_fecha, INTERVAL 15 DAY), 'OPERACION', 'Proveedor Alpha', CONCAT('Egreso simulado ', i), 80 + MOD(i, 12) * 25, v_personal, 'REGISTRADO');
        END IF;
        SET i = i + 1;
    END WHILE;
    COMMIT;
END$$
DELIMITER ;

CALL seed_alpha();
DROP PROCEDURE seed_alpha;
