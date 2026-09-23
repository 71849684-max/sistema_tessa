<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/ventas/CobranzaService.php';
require_once __DIR__ . '/../../capalogica/seguridad/VoucherService.php';

$in = ControllerHelper::input();
$accion = $in['accion'] ?? 'listar';
if (!in_array($accion, ['listar', 'hitos', 'contratos', 'participantes'], true)) {
    ControllerHelper::exigirPostCsrf();
}
PermisoService::requiere('cobranzas', in_array($accion, ['listar', 'hitos', 'contratos', 'participantes'], true) ? 'ver' : ($accion === 'anular' ? 'anular' : 'crear'));
$service = new CobranzaService();

if ($accion === 'listar') {
    $respuesta = $service->listar($in);
} elseif ($accion === 'contratos') {
    $respuesta = $service->contratos((string)($in['buscar'] ?? ''));
} elseif ($accion === 'hitos') {
    $respuesta = $service->hitos((int)($in['id_contrato'] ?? 0));
} elseif ($accion === 'participantes') {
    $respuesta = $service->participantesPago((int)($in['id_contrato'] ?? 0));
} elseif ($accion === 'anular') {
    $respuesta = $service->anular((int)($in['id_cobranza'] ?? 0), (string)($in['motivo'] ?? ''));
} else {
    $vouchers = new VoucherService(dirname(__DIR__, 2) . '/storage/vouchers');
    $voucher = null;
    if (isset($_FILES['voucher']) && (int)$_FILES['voucher']['error'] !== UPLOAD_ERR_NO_FILE) {
        $guardado = $vouchers->guardar($_FILES['voucher']);
        if (!$guardado['exito']) {
            ControllerHelper::responder($guardado, 422);
        }
        $voucher = $guardado['datos'];
    }
    $respuesta = $service->registrar($in, $voucher);
    if (!$respuesta['exito'] && is_array($voucher)) {
        $vouchers->eliminar((string)$voucher['nombre_interno']);
    }
}

ControllerHelper::responder($respuesta, $respuesta['exito'] ? 200 : 422);
