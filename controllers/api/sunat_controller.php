<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/integraciones/SunatService.php';

PermisoService::requiere('clientes', 'ver');
ControllerHelper::exigirPostCsrf();
$ahora = time();
$ventana = $_SESSION['sunat_ventana'] ?? 0;
if ($ahora - (int)$ventana >= 300) { $_SESSION['sunat_ventana'] = $ahora; $_SESSION['sunat_consultas'] = 0; }
if ((int)($_SESSION['sunat_consultas'] ?? 0) >= 20) {
    ControllerHelper::responder(RespuestaHelper::error('Límite de consultas alcanzado. Intente más tarde o ingrese los datos manualmente.'), 429);
}
$_SESSION['sunat_consultas'] = (int)($_SESSION['sunat_consultas'] ?? 0) + 1;
$entrada = ControllerHelper::input();
$respuesta = (new SunatService())->consultar((string)($entrada['ruc'] ?? ''));
ControllerHelper::responder($respuesta, $respuesta['exito'] ? 200 : 422);
