<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/integraciones/ReniecService.php';

$llamadas = 0;
$transporte = static function (string $url, array $headers, int $timeout) use (&$llamadas): array {
    $llamadas++;
    probar(str_contains($url, 'numero=12345678'), 'El DNI debe viajar como parámetro del proveedor.');
    probar(in_array('Authorization: Bearer token-de-prueba', $headers, true), 'El token solo debe ir del servidor al proveedor.');
    return ['http' => 200, 'body' => '{"nombres":"ANA","apellido_paterno":"PAZ","apellido_materno":"RUIZ"}'];
};
$service = new ReniecService(['token' => 'token-de-prueba', 'endpoint' => 'https://api.decolecta.com/v1/reniec/dni'], $transporte);
probar(!$service->consultar('123')['exito'] && $llamadas === 0, 'Debe rechazar DNI inválido antes de consumir la API.');
$datos = $service->consultar('12345678');
probar($datos['exito'] && $datos['datos']['nombres'] === 'ANA' && $datos['datos']['apellidos'] === 'PAZ RUIZ', 'Debe normalizar nombres y apellidos.');
probar(!isset($datos['datos']['raw']) && $llamadas === 1, 'No debe exponer respuesta cruda ni repetir consultas.');
$sinToken = new ReniecService(['token' => '', 'endpoint' => 'https://api.decolecta.com/v1/reniec/dni'], $transporte);
probar(!$sinToken->consultar('12345678')['exito'] && $llamadas === 1, 'Sin token debe ofrecer captura manual y no llamar al proveedor.');
echo "ReniecServiceTest OK\n";
