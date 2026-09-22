USE tessa_control_pagos;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_contrato_integrante_guardar$$
CREATE PROCEDURE sp_contrato_integrante_guardar(IN p_contrato INT UNSIGNED,IN p_cliente INT UNSIGNED)
BEGIN
    IF NOT EXISTS(SELECT 1 FROM cliente WHERE id_cliente=p_cliente AND estado=1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El integrante no es un cliente activo.';
    END IF;
    INSERT INTO contrato_integrante VALUES(p_contrato,p_cliente);
END$$
DELIMITER ;
