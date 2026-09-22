<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/rrhh/PermisoCatalogoService.php';

PermisoService::requiere('usuarios', 'administrar');
$entrada = ControllerHelper::input();
$tipo = (string)($entrada['tipo'] ?? '');
$id = (int)($entrada['id'] ?? 0);
$service = new PermisoCatalogoService();
$respuesta = match ($tipo) {
    'cargo' => $service->estadoCargo($id),
    'persona' => $service->estadoPersona($id),
    default => ['exito' => false, 'mensaje' => 'Tipo de consulta inválido.', 'datos' => null, 'errores' => ['tipo' => 'Use cargo o persona.']],
};
ControllerHelper::responder($respuesta, $respuesta['exito'] ? 200 : 422);
