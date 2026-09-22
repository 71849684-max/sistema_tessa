<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/integraciones/ReniecService.php';

PermisoService::requiere('clientes', 'ver');
ControllerHelper::exigirPostCsrf();
$ahora = time();
$ventana = $_SESSION['reniec_ventana'] ?? 0;
if ($ahora - (int)$ventana >= 300) { $_SESSION['reniec_ventana'] = $ahora; $_SESSION['reniec_consultas'] = 0; }
if ((int)($_SESSION['reniec_consultas'] ?? 0) >= 20) {
    ControllerHelper::responder(RespuestaHelper::error('Límite de consultas alcanzado. Intente en unos minutos o ingrese los datos manualmente.'), 429);
}
$_SESSION['reniec_consultas'] = (int)($_SESSION['reniec_consultas'] ?? 0) + 1;
$entrada = ControllerHelper::input();
$respuesta = (new ReniecService())->consultar((string)($entrada['dni'] ?? ''));
ControllerHelper::responder($respuesta, $respuesta['exito'] ? 200 : 422);
