USE tessa_control_pagos;

INSERT INTO modulo(codigo,nombre) VALUES ('dashboard','Dashboard de ventas') ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT IGNORE INTO permiso(id_modulo,accion) SELECT id_modulo,'ver' FROM modulo WHERE codigo='dashboard';
INSERT INTO cargo_permiso(id_cargo,id_permiso,permitido)
SELECT c.id_cargo,p.id_permiso,1 FROM cargo c JOIN permiso p JOIN modulo m ON m.id_modulo=p.id_modulo
WHERE c.nombre='Administrador' AND m.codigo='dashboard'
ON DUPLICATE KEY UPDATE permitido=1;

DROP PROCEDURE IF EXISTS sp_dashboard_ventas_resumen;
DROP PROCEDURE IF EXISTS sp_dashboard_ventas_evolucion;
DROP PROCEDURE IF EXISTS sp_dashboard_ventas_embudo;
DROP PROCEDURE IF EXISTS sp_dashboard_ventas_servicios;
DROP PROCEDURE IF EXISTS sp_dashboard_ventas_pendientes;

DELIMITER $$
CREATE PROCEDURE sp_dashboard_ventas_resumen(IN p_desde DATE,IN p_hasta DATE) BEGIN DECLARE v_cobrado DECIMAL(12,2) DEFAULT 0; DECLARE v_pendiente DECIMAL(12,2) DEFAULT 0; DECLARE v_activos INT DEFAULT 0; SELECT COALESCE(SUM(monto_total),0) INTO v_cobrado FROM cobranza WHERE estado='REGISTRADO' AND DATE(fecha) BETWEEN p_desde AND p_hasta; SELECT COALESCE(SUM(h.monto-h.monto_pagado),0) INTO v_pendiente FROM contrato_hito h JOIN contrato c ON c.id_contrato=h.id_contrato WHERE c.estado IN('ACTIVO','FINALIZADO') AND h.estado IN('PENDIENTE','PARCIAL','VENCIDO'); SELECT COUNT(*) INTO v_activos FROM contrato WHERE estado='ACTIVO'; SELECT v_cobrado cobrado,v_pendiente por_cobrar,v_activos contratos_activos,ROUND(IFNULL(v_cobrado/NULLIF(v_cobrado+v_pendiente,0)*100,0),2) efectividad; END$$
CREATE PROCEDURE sp_dashboard_ventas_evolucion(IN p_desde DATE,IN p_hasta DATE) BEGIN SELECT DATE(fecha) fecha,COALESCE(SUM(monto_total),0) cobrado FROM cobranza WHERE estado='REGISTRADO' AND DATE(fecha) BETWEEN p_desde AND p_hasta GROUP BY DATE(fecha) ORDER BY fecha; END$$
CREATE PROCEDURE sp_dashboard_ventas_embudo(IN p_desde DATE,IN p_hasta DATE) BEGIN SELECT h.estado,COUNT(*) cantidad,COALESCE(SUM(h.monto-h.monto_pagado),0) monto FROM contrato_hito h JOIN contrato c ON c.id_contrato=h.id_contrato WHERE c.estado<>'ANULADO' AND h.fecha_vencimiento BETWEEN p_desde AND p_hasta GROUP BY h.estado ORDER BY FIELD(h.estado,'PENDIENTE','PARCIAL','VENCIDO','PAGADO'); END$$
CREATE PROCEDURE sp_dashboard_ventas_servicios(IN p_desde DATE,IN p_hasta DATE) BEGIN SELECT s.nombre,COALESCE(SUM(c.monto_neto),0) contratado,COALESCE((SELECT SUM(co.monto_total) FROM cobranza co JOIN contrato cx ON cx.id_contrato=co.id_contrato WHERE co.estado='REGISTRADO' AND cx.id_servicio=s.id_servicio AND DATE(co.fecha) BETWEEN p_desde AND p_hasta),0) cobrado FROM servicio s LEFT JOIN contrato c ON c.id_servicio=s.id_servicio AND c.estado<>'ANULADO' AND c.fecha BETWEEN p_desde AND p_hasta GROUP BY s.id_servicio,s.nombre HAVING contratado>0 OR cobrado>0 ORDER BY contratado DESC,s.nombre LIMIT 8; END$$
CREATE PROCEDURE sp_dashboard_ventas_pendientes(IN p_desde DATE,IN p_hasta DATE) BEGIN SELECT h.id_hito,c.con_numero,COALESCE(cl.razon_social,cl.nombres) cliente,h.descripcion,h.fecha_vencimiento,h.monto-h.monto_pagado saldo,h.estado FROM contrato_hito h JOIN contrato c ON c.id_contrato=h.id_contrato JOIN cliente cl ON cl.id_cliente=c.id_cliente WHERE c.estado='ACTIVO' AND h.estado IN('PENDIENTE','PARCIAL','VENCIDO') AND h.fecha_vencimiento BETWEEN p_desde AND p_hasta ORDER BY h.fecha_vencimiento,h.id_hito LIMIT 30; END$$
DELIMITER ;
