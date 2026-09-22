<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../capalogica/core/MenuService.php';
require_once __DIR__ . '/../capalogica/ventas/DashboardVentasService.php';

$permisos = new PermisoService([
    ['modulo' => 'dashboard', 'accion' => 'ver', 'permitido' => 1],
    ['modulo' => 'clientes', 'accion' => 'ver', 'permitido' => 1],
    ['modulo' => 'contratos', 'accion' => 'ver', 'permitido' => 1],
    ['modulo' => 'personal', 'accion' => 'ver', 'permitido' => 1],
], []);
$menu = (new MenuService($permisos))->construir();
probar(count($menu) === 2, 'El menú debe ocultar grupos sin opciones permitidas.');
probar($menu[0]['codigo'] === 'ventas' && count($menu[0]['opciones']) === 3, 'Ventas debe incluir dashboard y solo sus vistas permitidas.');
probar($menu[1]['codigo'] === 'administracion' && $menu[1]['opciones'][0]['codigo'] === 'personal', 'Administración debe mostrar solo Personal permitido.');

$db = new class {
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        if ($sql === 'CALL sp_dashboard_ventas_resumen(?,?)') {
            return [['cobrado' => '125.50', 'por_cobrar' => '300.00', 'contratos_activos' => '2', 'efectividad' => '29.49']];
        }
        return [];
    }
};
$dashboard = new DashboardVentasService($db);
$resumen = $dashboard->resumen('2026-09-01', '2026-09-30');
probar($resumen['exito'] && (float)$resumen['datos']['cobrado'] === 125.50, 'El dashboard debe exponer su resumen de ventas.');
echo "DashboardMenuTest OK\n";
