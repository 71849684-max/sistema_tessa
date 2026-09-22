<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/AuthService.php';
require_once __DIR__ . '/../../capalogica/core/SessionService.php';

$input=ControllerHelper::input();
if (($input['accion'] ?? '') === 'logout') { ControllerHelper::exigirPostCsrf(); SessionService::cerrar(); ControllerHelper::responder(RespuestaHelper::ok('Sesión cerrada.')); }
if (!ControllerHelper::post()) ControllerHelper::responder(RespuestaHelper::error('Método no permitido.'),405);
$respuesta=(new AuthService())->login($input);
ControllerHelper::responder($respuesta,$respuesta['exito'] ? 200 : 422);
