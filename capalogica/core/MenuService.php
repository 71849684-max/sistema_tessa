<?php
declare(strict_types=1);

require_once __DIR__ . '/../seguridad/PermisoService.php';

final class MenuService
{
    /** @var array<int, array{codigo:string,etiqueta:string,opciones:array<int, array{codigo:string,etiqueta:string,ruta:string}>}> */
    private const GRUPOS = [
        [
            'codigo' => 'ventas',
            'etiqueta' => 'Ventas',
            'opciones' => [
                ['codigo' => 'dashboard', 'etiqueta' => 'Dashboard de ventas', 'ruta' => 'capacliente/ventas/dashboard.php'],
                ['codigo' => 'clientes', 'etiqueta' => 'Clientes', 'ruta' => 'capacliente/ventas/clientes.php'],
                ['codigo' => 'servicios', 'etiqueta' => 'Servicios', 'ruta' => 'capacliente/ventas/servicios.php'],
                ['codigo' => 'contratos', 'etiqueta' => 'Contratos', 'ruta' => 'capacliente/ventas/contratos.php'],
                ['codigo' => 'cobranzas', 'etiqueta' => 'Cobranzas', 'ruta' => 'capacliente/ventas/cobranzas.php'],
                ['codigo' => 'egresos', 'etiqueta' => 'Egresos', 'ruta' => 'capacliente/ventas/egresos.php'],
            ],
        ],
        [
            'codigo' => 'administracion',
            'etiqueta' => 'Administración',
            'opciones' => [
                ['codigo' => 'personal', 'etiqueta' => 'Personal', 'ruta' => 'capacliente/rrhh/personal.php'],
                ['codigo' => 'cargos', 'etiqueta' => 'Cargos', 'ruta' => 'capacliente/rrhh/cargos.php'],
                ['codigo' => 'usuarios', 'etiqueta' => 'Usuarios y permisos', 'ruta' => 'capacliente/rrhh/usuarios.php'],
                ['codigo' => 'auditoria', 'etiqueta' => 'Auditoría', 'ruta' => 'capacliente/rrhh/auditoria.php'],
                ['codigo' => 'configuracion', 'etiqueta' => 'Configuración', 'ruta' => 'capacliente/rrhh/configuracion.php'],
            ],
        ],
    ];

    public function __construct(private PermisoService $permisos)
    {
    }

    /** @return array<int, array{codigo:string,etiqueta:string,opciones:array<int, array{codigo:string,etiqueta:string,ruta:string}>}> */
    public function construir(): array
    {
        $resultado = [];
        foreach (self::GRUPOS as $grupo) {
            $opciones = array_values(array_filter(
                $grupo['opciones'],
                fn(array $opcion): bool => $this->permisos->autorizar($opcion['codigo'], 'ver')
            ));
            if ($opciones !== []) {
                $resultado[] = ['codigo' => $grupo['codigo'], 'etiqueta' => $grupo['etiqueta'], 'opciones' => $opciones];
            }
        }
        return $resultado;
    }
}
