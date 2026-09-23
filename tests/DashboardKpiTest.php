<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$vista = file_get_contents(__DIR__ . '/../capacliente/ventas/dashboard.php');
probar(str_contains($vista, 'data-kpi="proyectado"'), 'El KPI de proyección debe ser independiente del saldo pendiente.');
probar(str_contains($vista, 'data-kpi="egresos"') && str_contains($vista, 'data-kpi="neto"'), 'Los KPI deben mostrar egresos y flujo neto.');
probar(str_contains($vista, 'data-flujo-caja'), 'El dashboard debe mostrar flujo de caja con barras, no una línea para datos escasos.');
echo "DashboardKpiTest OK\n";
