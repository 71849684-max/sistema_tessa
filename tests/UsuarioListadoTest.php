<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/rrhh/UsuarioService.php';

$db = new class {
    public array $llamadas = [];
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        $this->llamadas[] = $sql;
        if ($sql === 'CALL sp_personal_listar()') {
            return [['id_personal' => 3, 'id_usuario' => 8, 'usu_nombre' => 'analista', 'usu_estado' => 1, 'cargo' => 'Ventas']];
        }
        throw new RuntimeException('Consulta inesperada: ' . $sql);
    }
};
$respuesta = (new UsuarioService($db))->listar();
probar($respuesta['exito'] && count($respuesta['datos']) === 1, 'Usuarios debe listar cuentas asociadas a personal.');
probar($db->llamadas === ['CALL sp_personal_listar()'], 'El listado debe usar el procedimiento existente.');
echo "UsuarioListadoTest OK\n";
