<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/ventas/ContratoService.php';
require_once __DIR__ . '/../../capalogica/core/RespuestaHelper.php';

$in = ControllerHelper::input();
$accion = (string)($in['accion'] ?? 'listar');
$lecturas = ['listar', 'detalle', 'catalogos', 'clientes_buscar', 'docx'];
if (!in_array($accion, $lecturas, true)) ControllerHelper::exigirPostCsrf();
$permiso = in_array($accion, $lecturas, true) ? 'ver' : ($accion === 'anular' ? 'anular' : ((int)($in['id_contrato'] ?? 0) > 0 ? 'editar' : 'crear'));
PermisoService::requiere('contratos', $permiso);

$service = new ContratoService();
if ($accion === 'docx') $service->descargarDocx((int)($in['id_contrato'] ?? $_GET['id_contrato'] ?? 0));

$respuesta = match ($accion) {
    'listar' => $service->listar(),
    'catalogos' => $service->catalogos(),
    'clientes_buscar' => $service->buscarClientes((string)($in['buscar'] ?? '')),
    'detalle' => $service->detalle((int)($in['id_contrato'] ?? 0)),
    'anular' => $service->anular((int)($in['id_contrato'] ?? 0), (string)($in['motivo'] ?? '')),
    'guardar' => ((int)($in['id_contrato'] ?? 0) > 0 ? $service->actualizar($in) : $service->guardar($in)),
    default => RespuestaHelper::error('Acción no reconocida.'),
};
ControllerHelper::responder($respuesta, $respuesta['exito'] ? 200 : 422);
