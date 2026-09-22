<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/ventas/DashboardVentasService.php';

PermisoService::requiere('dashboard', 'ver');
$input = ControllerHelper::input();
$desde = (string)($input['desde'] ?? date('Y-m-01'));
$hasta = (string)($input['hasta'] ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta) || $desde > $hasta) {
    ControllerHelper::responder(RespuestaHelper::error('El rango de fechas no es válido.', ['fechas' => 'Revise Desde y Hasta.']), 422);
}
$respuesta = (new DashboardVentasService())->datos($desde, $hasta);
ControllerHelper::responder($respuesta, $respuesta['exito'] ? 200 : 422);
