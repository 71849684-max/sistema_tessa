<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/rrhh/PermisoCatalogoService.php';
require_once __DIR__ . '/../capalogica/rrhh/UsuarioService.php';

$db = new class {
    public array $llamadas = [];
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        $this->llamadas[] = [$sql, $tipos, $parametros];
        if ($sql === 'CALL sp_persona_permiso_configuracion_listar(?)') {
            return [['id_permiso' => 1, 'modulo' => 'clientes', 'accion' => 'ver', 'plantilla' => 1, 'excepcion' => 0, 'efectivo' => 0]];
        }
        throw new RuntimeException('Procedimiento inesperado.');
    }
};
$respuesta = (new PermisoCatalogoService($db))->estadoPersona(7);
probar($respuesta['exito'] && (int)$respuesta['datos'][0]['efectivo'] === 0, 'Debe mostrar la denegación individual efectiva.');
probar($db->llamadas === [['CALL sp_persona_permiso_configuracion_listar(?)', 'i', [7]]], 'Debe consultar el estado por persona con CALL preparado.');
$catalogosDb = new class {
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        return match ($sql) {
            'CALL sp_cargo_listar()' => [['id_cargo' => 2, 'nombre' => 'Ventas']],
            'CALL sp_personal_listar()' => [['id_personal' => 7, 'nombres' => 'Ana', 'apellidos' => 'Paz']],
            default => throw new RuntimeException('Procedimiento inesperado.'),
        };
    }
};
$catalogos = (new PermisoCatalogoService($catalogosDb))->catalogos();
probar($catalogos['exito'] && $catalogos['datos']['cargos'][0]['nombre'] === 'Ventas' && $catalogos['datos']['personas'][0]['nombres'] === 'Ana', 'La administración de permisos debe ofrecer catálogos seleccionables.');
echo "PermisosEstadoTest OK\n";

$dbQuitar = new class {
    public array $llamadas = [];
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        $this->llamadas[] = [$sql, $tipos, $parametros];
        if ($sql === 'CALL sp_persona_permiso_quitar(?,?)') return [['mensaje' => 'Excepción retirada.']];
        if ($sql === 'CALL sp_auditoria_registrar(?,?,?,?,?,?,?)') return [];
        throw new RuntimeException('Procedimiento inesperado.');
    }
};
$quitado = (new UsuarioService($dbQuitar))->quitarPermiso(['id_personal' => 7, 'id_permiso' => 1]);
probar($quitado['exito'], 'Debe poder volver a heredar permisos del cargo.');
probar($dbQuitar->llamadas[0] === ['CALL sp_persona_permiso_quitar(?,?)', 'ii', [7, 1]], 'La excepción se quita con procedimiento preparado.');
