<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/rrhh/PersonalService.php';

$db = new class {
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        if ($sql === 'CALL sp_cargo_listar()') return [['id_cargo' => 4, 'nombre' => 'Asesor', 'estado' => 1]];
        throw new RuntimeException('Consulta inesperada.');
    }
};
$resultado = (new PersonalService($db))->cargos();
probar($resultado['exito'] && $resultado['datos'][0]['id_cargo'] === 4, 'El formulario de personal debe cargar los cargos existentes.');
echo "CatalogosPersonalTest OK\n";
