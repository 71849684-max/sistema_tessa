<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/core/EmpresaService.php';
require_once __DIR__ . '/../../capalogica/core/RespuestaHelper.php';

$in = ControllerHelper::input();
$accion = (string)($in['accion'] ?? 'obtener');
$lecturas = ['obtener'];
if (!in_array($accion, $lecturas, true)) ControllerHelper::exigirPostCsrf();
PermisoService::requiere('configuracion', $accion === 'obtener' ? 'ver' : 'editar');

$service = new EmpresaService();
$respuesta = match ($accion) {
    'obtener' => $service->obtener(),
    'guardar' => $service->guardar($in),
    default => RespuestaHelper::error('Acción no reconocida.'),
};
ControllerHelper::responder($respuesta, $respuesta['exito'] ? 200 : 422);
