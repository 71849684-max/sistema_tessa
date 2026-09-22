<?php
declare(strict_types=1);

if (getenv('TESSA_RUN_DB_TESTS') !== '1') {
    fwrite(STDOUT, "SmokeDatabaseTest omitido; defina TESSA_RUN_DB_TESTS=1.\n");
    exit(0);
}

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capaconexion/Conexion.php';
require_once __DIR__ . '/../capalogica/seguridad/CaptchaService.php';
require_once __DIR__ . '/../capalogica/seguridad/AuthService.php';
require_once __DIR__ . '/../capalogica/ventas/ClienteService.php';
require_once __DIR__ . '/../capalogica/ventas/ServicioService.php';
require_once __DIR__ . '/../capalogica/ventas/ContratoService.php';
require_once __DIR__ . '/../capalogica/ventas/CobranzaService.php';
require_once __DIR__ . '/../capalogica/ventas/EgresoService.php';
require_once __DIR__ . '/../capalogica/seguridad/VoucherService.php';

$db = new Conexion();
$hash = password_hash('ClaveTemporalSegura!2026', PASSWORD_DEFAULT);
$db->call('CALL sp_admin_inicializar(?,?,?,?,?)', 'sssss', ['tessa_prueba', $hash, 'Prueba', 'Temporal', '87654321']);
$personal = $db->call('CALL sp_personal_listar()')[0];
$_SESSION = ['usuario_id' => 1, 'personal_id' => (int)$personal['id_personal']];

$sesion = [];
$captcha = new CaptchaService($sesion, static fn(): string => 'Z9YX');
$captcha->generar();
probar($captcha->validar('Z9YX') && !$captcha->validar('Z9YX'), 'CAPTCHA de un uso no validado.');

ini_set('session.save_path', sys_get_temp_dir());
session_start();
$_SESSION = [];
$captchaLogin = new CaptchaService($_SESSION, static fn(): string => 'K7LM');
$captchaLogin->generar();
$login = (new AuthService($db))->login(['usuario'=>'tessa_prueba','clave'=>'ClaveTemporalSegura!2026','captcha'=>'K7LM']);
probar($login['exito'] && (int)($_SESSION['usuario_id'] ?? 0) === 1, 'Login con contraseña hash y CAPTCHA no validado.');

for ($intento = 0; $intento < 5; $intento++) {
    $db->call('CALL sp_login_registrar_intento(?,?)', 'si', ['tessa_prueba', 0]);
}
$bloqueado = $db->call('CALL sp_login_obtener_hash(?)', 's', ['tessa_prueba'])[0] ?? [];
probar(!empty($bloqueado['locked_until']), 'No se bloqueó la cuenta tras cinco fallos dentro de la ventana.');

$cliente = (new ClienteService($db))->guardar(['tipo'=>'NATURAL','documento'=>'12345678','nombres'=>'Cliente','apellidos'=>'Prueba']);
probar($cliente['exito'], 'No se creó cliente.');
$servicio = (new ServicioService($db))->guardar(['nombre'=>'Servicio prueba','precio_referencia'=>100]);
probar($servicio['exito'], 'No se creó servicio.');

$contrato = (new ContratoService($db))->guardar([
    'id_cliente'=>(int)$cliente['datos']['id_cliente'], 'id_servicio'=>(int)$servicio['datos']['id_servicio'],
    'id_responsable'=>(int)$personal['id_personal'], 'tipo'=>'INDIVIDUAL','fecha'=>'2026-09-22',
    'bruto'=>100,'descuento'=>0,'hitos'=>[['numero'=>1,'descripcion'=>'Cuota','fecha_vencimiento'=>'2026-10-10','monto'=>100]]
]);
probar($contrato['exito'], 'No se creó contrato.');
$idContrato=(int)$contrato['datos']['id_contrato'];
$hito=$db->call('CALL sp_hitos_cobranza_listar(?)','i',[$idContrato])[0];

$cobranza = new CobranzaService($db);
$directorio=__DIR__.'/../storage/vouchers';
$temporal=tempnam(sys_get_temp_dir(),'tessa-png');
file_put_contents($temporal,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL+XQAAAABJRU5ErkJggg=='));
$voucher=(new VoucherService($directorio))->guardar(['tmp_name'=>$temporal,'name'=>'voucher.png','size'=>filesize($temporal),'error'=>UPLOAD_ERR_OK]);
probar($voucher['exito'], 'No se validó voucher PNG.');
$pago=$cobranza->registrar(['id_contrato'=>$idContrato,'fecha'=>'2026-09-22 10:00:00','medio_pago'=>'YAPE','detalles'=>[['id_hito'=>(int)$hito['id_hito'],'monto'=>40]]],$voucher['datos']);
probar($pago['exito'], 'No se registró cobranza parcial con voucher.');
$voucherDb=$db->call('CALL sp_voucher_obtener(?)','i',[1])[0] ?? [];
probar((int)($voucherDb['id_cobranza'] ?? 0)===(int)$pago['datos']['id_cobranza'], 'El voucher no se registró junto con la cobranza.');

$anulado=$cobranza->anular((int)$pago['datos']['id_cobranza'],'Prueba de reversión');
probar($anulado['exito'], 'No se anuló cobranza.');
$hitoRevertido=$db->call('CALL sp_hitos_cobranza_listar(?)','i',[$idContrato])[0];
probar((float)$hitoRevertido['saldo']===100.0, 'La anulación no revirtió la cuota.');

$egresos=new EgresoService($db);
$egreso=$egresos->registrar(['fecha'=>'2026-09-22','concepto'=>'Validación','monto'=>15]);
probar($egreso['exito'], 'No se registró egreso.');
probar($egresos->anular((int)$egreso['datos']['id_egreso'],'Prueba de anulación')['exito'], 'No se anuló egreso.');
probar((new ContratoService($db))->anular($idContrato,'Prueba final')['exito'], 'No se anuló contrato.');
echo "SmokeDatabaseTest OK\n";
