<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/rrhh/UsuarioService.php';

$entrada = ControllerHelper::input();
$accion = (string)($entrada['accion'] ?? 'listar');
if (!in_array($accion, ['listar', 'personas'], true)) ControllerHelper::exigirPostCsrf();
$permiso = match ($accion) {
    'listar' => 'ver',
    'personas' => 'ver',
    'permiso', 'permiso_cargo', 'permiso_quitar' => 'administrar',
    'guardar' => (int)($entrada['id_usuario'] ?? 0) > 0 ? 'editar' : 'crear',
    default => 'administrar',
};
PermisoService::requiere('usuarios', $permiso);
$service = new UsuarioService();
$respuesta = match ($accion) {
    'listar' => $service->listar(),
    'personas' => $service->personas(),
    'permiso' => $service->guardarPermiso($entrada),
    'permiso_quitar' => $service->quitarPermiso($entrada),
    'permiso_cargo' => $service->guardarPermisoCargo($entrada),
    'guardar' => $service->guardar($entrada),
    default => ['exito' => false, 'mensaje' => 'Acción no válida.', 'datos' => null, 'errores' => ['accion' => 'No reconocida.']],
};
ControllerHelper::responder($respuesta, $respuesta['exito'] ? 200 : 422);
