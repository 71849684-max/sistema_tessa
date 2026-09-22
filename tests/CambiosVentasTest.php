<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/ventas/ClienteService.php';
require_once __DIR__ . '/../capalogica/ventas/EgresoService.php';

$db = new class {
    public array $llamadas = [];
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        $this->llamadas[] = [$sql, $tipos, $parametros];
        return match (true) {
            str_starts_with($sql, 'CALL sp_cliente_guardar(') => [['id_cliente' => 7, 'mensaje' => 'Guardado']],
            str_starts_with($sql, 'CALL sp_cliente_buscar(') => [['id_cliente' => 7, 'documento' => '12345678', 'nombres' => 'Ana', 'apellidos' => 'Paz']],
            str_starts_with($sql, 'CALL sp_egreso_registrar(') => [['id_egreso' => 9, 'mensaje' => 'Registrado']],
            str_starts_with($sql, 'CALL sp_auditoria_registrar(') => [],
            default => throw new RuntimeException('Procedimiento inesperado: ' . $sql),
        };
    }
};

$clientes = new ClienteService($db);
$invalido = $clientes->guardar(['tipo' => 'NATURAL', 'documento' => '12345678', 'nombres' => 'Ana']);
probar(!$invalido['exito'] && isset($invalido['errores']['apellidos']), 'La persona natural requiere apellidos.');
$guardado = $clientes->guardar(['tipo' => 'NATURAL', 'documento' => '12345678', 'nombres' => 'Ana', 'apellidos' => 'Paz']);
probar($guardado['exito'], 'Debe guardar nombres y apellidos por separado.');
probar($db->llamadas[0][0] === 'CALL sp_cliente_guardar(?,?,?,?,?,?,?,?,?,?)' && $db->llamadas[0][2][4] === 'Paz', 'El procedimiento debe recibir el apellido.');
$buscado = $clientes->buscar('Paz');
probar($buscado['exito'] && $buscado['datos'][0]['apellidos'] === 'Paz', 'Se debe encontrar el cliente por apellido.');
probar($db->llamadas[2] === ['CALL sp_cliente_buscar(?)', 's', ['Paz']], 'La búsqueda usa un procedimiento preparado.');

$egreso = (new EgresoService($db))->registrar(['fecha' => '2026-09-22', 'concepto' => 'Gastos de oficina', 'monto' => '100.00']);
probar($egreso['exito'], 'Egresos debe aceptar únicamente fecha, concepto y monto.');
probar($db->llamadas[3][2][1] === 'GENERAL' && $db->llamadas[3][2][2] === '', 'Los campos internos heredados no deben exigirse al usuario.');
echo "CambiosVentasTest OK\n";
