<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/ventas/ContratoService.php';
require_once __DIR__ . '/../capalogica/ventas/CobranzaService.php';
require_once __DIR__ . '/../capalogica/ventas/EgresoService.php';
require_once __DIR__ . '/../capalogica/seguridad/VoucherService.php';

$db = respuestaFalsa();
$contratos = new ContratoService($db);
$invalido = $contratos->guardar([
    'id_cliente' => 1, 'id_servicio' => 1, 'id_responsable' => 1, 'tipo' => 'INDIVIDUAL',
    'bruto' => '100.00', 'descuento' => '10.00',
    'hitos' => [['numero' => 1, 'descripcion' => 'Cuota', 'fecha_vencimiento' => '2026-10-01', 'monto' => '80.00']]
]);
probar(!$invalido['exito'] && isset($invalido['errores']['hitos']), 'Debe validar total de cuotas.');

$grupoSoloTitular = $contratos->guardar([
    'id_cliente' => 1, 'id_servicio' => 1, 'id_responsable' => 1, 'tipo' => 'GRUPAL',
    'bruto' => '100.00', 'descuento' => '0.00',
    'hitos' => [['numero' => 1, 'descripcion' => 'Cuota', 'fecha_vencimiento' => '2026-10-01', 'monto' => '100.00']],
    'integrantes' => [['id_cliente' => 1]],
]);
probar(!$grupoSoloTitular['exito'] && isset($grupoSoloTitular['errores']['integrantes']), 'Un contrato grupal necesita un integrante distinto del titular.');

$cobranza = new CobranzaService($db);
$sinMotivo = $cobranza->anular(8, '');
probar(!$sinMotivo['exito'] && isset($sinMotivo['errores']['motivo']), 'Anulación debe exigir motivo.');

$egresos = new EgresoService($db);
$montoCero = $egresos->registrar(['fecha' => '2026-09-22', 'concepto' => 'Taxi', 'monto' => '0']);
probar(!$montoCero['exito'] && isset($montoCero['errores']['monto']), 'Egreso debe requerir monto positivo.');

$directorio = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tessa-voucher-test';
@mkdir($directorio);
$archivo = $directorio . DIRECTORY_SEPARATOR . 'falso.jpg';
file_put_contents($archivo, 'no es una imagen');
$voucher = new VoucherService($directorio);
$invalido = $voucher->guardar(['tmp_name' => $archivo, 'name' => 'falso.jpg', 'size' => filesize($archivo), 'error' => UPLOAD_ERR_OK]);
probar(!$invalido['exito'], 'Voucher falso debe rechazarse.');
@unlink($archivo);
@rmdir($directorio);
echo "DomainServiceTest OK\n";
