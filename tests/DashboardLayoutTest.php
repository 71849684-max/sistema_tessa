<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$vista = file_get_contents(__DIR__ . '/../capacliente/ventas/dashboard.php');
$estilos = file_get_contents(__DIR__ . '/../resources/css/vistas.css');
probar(str_contains($vista, 'dashboard-trend'), 'El dashboard debe declarar un panel principal de tendencia.');
probar(str_contains($vista, 'dashboard-state'), 'El dashboard debe declarar un panel de estado de cuotas.');
probar(str_contains($vista, 'dashboard-services') && str_contains($vista, 'dashboard-alerts'), 'El dashboard debe separar servicios y alertas en paneles inferiores.');
probar(str_contains($vista, 'dashboard-period') && str_contains($vista, 'dashboard-quota-health'), 'El dashboard debe incluir período y el panel compuesto de estado de cuotas.');
probar(str_contains($vista, 'dashboard-monthly'), 'El dashboard debe incluir el gráfico inferior de cobranza por período.');
probar(str_contains($estilos, '.sales-dashboard .dashboard-services .service-bars{display:flex'), 'Las barras de servicios deben disponerse horizontalmente.');
echo "DashboardLayoutTest OK\n";
