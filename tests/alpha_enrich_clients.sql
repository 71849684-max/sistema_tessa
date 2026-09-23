USE tessa_control_pagos_alpha_20260923;

DELIMITER $$
DROP PROCEDURE IF EXISTS enrich_alpha_clients$$
CREATE PROCEDURE enrich_alpha_clients()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_cliente INT UNSIGNED;

    START TRANSACTION;
    WHILE i <= 5000 DO
        INSERT INTO cliente(tipo,documento,nombres,apellidos,correo,telefono,direccion,estado)
        VALUES(
            'NATURAL', CONCAT('9', LPAD(i, 10, '0')), 'Cliente', CONCAT('Alpha ', LPAD(i, 6, '0')),
            CONCAT('alpha', i, '@example.test'), CONCAT('900', LPAD(i, 6, '0')), 'Dirección simulada', 1
        );
        SET v_cliente = LAST_INSERT_ID();
        UPDATE contrato SET id_cliente = v_cliente WHERE con_numero = CONCAT('ALPHA-2026-', LPAD(i, 6, '0'));
        SET i = i + 1;
    END WHILE;
    COMMIT;
END$$
DELIMITER ;

CALL enrich_alpha_clients();
DROP PROCEDURE enrich_alpha_clients;
