<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/ventas/ContratoService.php';
require_once __DIR__ . '/../capalogica/ventas/CobranzaService.php';

$db = new class {
    public array $llamadas = [];
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        $this->llamadas[] = $sql;
        return match ($sql) {
            'CALL sp_cliente_listar()' => [['id_cliente' => 1, 'nombres' => 'Ana', 'estado' => 1]],
            'CALL sp_servicio_listar()' => [['id_servicio' => 2, 'nombre' => 'Asesoría', 'estado' => 1]],
            'CALL sp_personal_listar()' => [['id_personal' => 3, 'nombres' => 'Luis', 'estado' => 1]],
            'CALL sp_cliente_buscar(?)' => [['id_cliente' => 1, 'nombres' => 'Ana', 'apellidos' => 'Paz', 'estado' => 1]],
            default => throw new RuntimeException('Consulta inesperada.'),
        };
    }
};
$service = new ContratoService($db);
$r = $service->catalogos();
probar($r['exito'] && count($r['datos']['servicios']) === 1, 'El formulario debe recibir catálogos legibles.');
probar($db->llamadas === ['CALL sp_servicio_listar()', 'CALL sp_personal_listar()'], 'El formulario no debe descargar todo el catálogo de clientes.');
$buscados = $service->buscarClientes('Paz');
probar($buscados['exito'] && $buscados['datos'][0]['apellidos'] === 'Paz', 'El contrato debe poder buscar clientes por apellido.');
echo "CatalogosContratoTest OK\n";

$cobranzaDb = new class {
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        if ($sql === 'CALL sp_contrato_listar()') return [['id_contrato' => 5, 'con_numero' => 'TES-2026-000005', 'estado' => 'ACTIVO']];
        throw new RuntimeException('Consulta inesperada.');
    }
};
$contratos = (new CobranzaService($cobranzaDb))->contratos();
probar($contratos['exito'] && $contratos['datos'][0]['id_contrato'] === 5, 'Cobranza debe poder seleccionar un contrato por número.');
