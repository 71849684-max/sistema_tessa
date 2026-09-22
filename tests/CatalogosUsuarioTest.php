<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/rrhh/UsuarioService.php';

$db = new class {
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        if ($sql === 'CALL sp_personal_listar()') return [['id_personal' => 2, 'nombres' => 'Ana', 'apellidos' => 'Paz', 'estado' => 1, 'id_usuario' => null]];
        throw new RuntimeException('Consulta inesperada.');
    }
};
$resultado = (new UsuarioService($db))->personas();
probar($resultado['exito'] && $resultado['datos'][0]['id_personal'] === 2, 'El formulario de usuarios debe permitir elegir una persona por nombre.');
echo "CatalogosUsuarioTest OK\n";
