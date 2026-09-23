<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$ruta = __DIR__ . '/../bd/actualizar_kpis_flujo_caja.sql';
probar(is_file($ruta), 'Debe existir una migración para el resumen de flujo de caja.');
$migracion = file_get_contents($ruta);
probar(str_contains($migracion, 'egresos') && str_contains($migracion, 'proyectado') && str_contains($migracion, 'neto'), 'El resumen debe exponer egresos, proyección y flujo neto.');
probar(str_contains($migracion, 'FROM egreso'), 'Los egresos registrados deben participar en el resumen.');
echo "DashboardCashflowDataTest OK\n";
