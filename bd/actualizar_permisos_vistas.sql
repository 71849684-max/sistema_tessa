USE tessa_control_pagos;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_cargo_permiso_configuracion_listar$$
CREATE PROCEDURE sp_cargo_permiso_configuracion_listar(IN p_cargo INT UNSIGNED) BEGIN SELECT pr.id_permiso,m.codigo modulo,pr.accion,cp.permitido plantilla FROM permiso pr JOIN modulo m ON m.id_modulo=pr.id_modulo LEFT JOIN cargo_permiso cp ON cp.id_permiso=pr.id_permiso AND cp.id_cargo=p_cargo ORDER BY m.nombre,pr.accion; END$$
DROP PROCEDURE IF EXISTS sp_persona_permiso_configuracion_listar$$
CREATE PROCEDURE sp_persona_permiso_configuracion_listar(IN p_persona INT UNSIGNED) BEGIN SELECT pr.id_permiso,m.codigo modulo,pr.accion,cp.permitido plantilla,pp.permitido excepcion,COALESCE(pp.permitido,cp.permitido,0) efectivo FROM personal pe CROSS JOIN permiso pr JOIN modulo m ON m.id_modulo=pr.id_modulo LEFT JOIN cargo_permiso cp ON cp.id_cargo=pe.id_cargo AND cp.id_permiso=pr.id_permiso LEFT JOIN persona_permiso pp ON pp.id_personal=pe.id_personal AND pp.id_permiso=pr.id_permiso WHERE pe.id_personal=p_persona ORDER BY m.nombre,pr.accion; END$$
DROP PROCEDURE IF EXISTS sp_persona_permiso_quitar$$
CREATE PROCEDURE sp_persona_permiso_quitar(IN p_persona INT UNSIGNED,IN p_permiso INT UNSIGNED) BEGIN DELETE FROM persona_permiso WHERE id_personal=p_persona AND id_permiso=p_permiso; SELECT 1 resultado,'Excepción retirada; se aplica la plantilla del cargo.' mensaje; END$$
DELIMITER ;
