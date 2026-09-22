<?php
declare(strict_types=1);
require_once __DIR__.'/../core/ControllerHelper.php'; require_once __DIR__.'/../../capalogica/seguridad/PermisoService.php'; require_once __DIR__.'/../../capalogica/rrhh/PermisoCatalogoService.php';
PermisoService::requiere('usuarios','administrar');
$service = new PermisoCatalogoService();
$respuesta = (ControllerHelper::input()['accion'] ?? '') === 'catalogos' ? $service->catalogos() : $service->listar();
ControllerHelper::responder($respuesta, $respuesta['exito'] ? 200 : 500);
