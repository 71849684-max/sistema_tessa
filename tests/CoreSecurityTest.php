<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/core/RespuestaHelper.php';
require_once __DIR__ . '/../capalogica/seguridad/CaptchaService.php';
require_once __DIR__ . '/../capalogica/seguridad/PermisoService.php';

$r = RespuestaHelper::ok('Correcto', ['id' => 7]);
probar($r === ['exito' => true, 'mensaje' => 'Correcto', 'datos' => ['id' => 7], 'errores' => []], 'Contrato JSON uniforme inválido.');

$sesion = [];
$captcha = new CaptchaService($sesion, static fn(): string => 'ABCD');
$svg = $captcha->generar();
probar(str_starts_with($svg, '<svg'), 'Captcha debe ser SVG.');
probar($captcha->validar('ABCD'), 'Captcha válido fue rechazado.');
probar(!$captcha->validar('ABCD'), 'Captcha debe usarse una sola vez.');

$permisos = new PermisoService(
    [['modulo' => 'cobranzas', 'accion' => 'anular', 'permitido' => 1]],
    [['modulo' => 'cobranzas', 'accion' => 'anular', 'permitido' => 0]]
);
probar(!$permisos->autorizar('cobranzas', 'anular'), 'La denegación individual debe prevalecer.');

require_once __DIR__ . '/../capalogica/core/SessionService.php';
ini_set('session.save_path', sys_get_temp_dir());
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$_SESSION = [];
$token = SessionService::csrfToken();
probar(SessionService::validarCsrf($token), 'Token CSRF válido fue rechazado.');
probar(!SessionService::validarCsrf('token-invalido'), 'Token CSRF inválido fue aceptado.');
echo "CoreSecurityTest OK\n";
