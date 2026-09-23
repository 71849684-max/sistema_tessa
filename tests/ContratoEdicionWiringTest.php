<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

$vista = file_get_contents(__DIR__ . '/../capacliente/ventas/contratos.php');
$controlador = file_get_contents(__DIR__ . '/../controllers/ventas/contrato_controller.php');
probar(str_contains($vista, 'name="id_contrato"'), 'El modal debe incluir el ID de contrato para editar.');
probar(str_contains($vista, '>Total<input name="bruto"'), 'El importe editable debe llamarse Total.');
probar(str_contains($controlador, "'guardar' => ((int)(\$in['id_contrato'] ?? 0) > 0 ? \$service->actualizar(\$in) : \$service->guardar(\$in))"), 'Guardar debe actualizar cuando el formulario contiene un ID.');
$js = file_get_contents(__DIR__ . '/../resources/js/contratos.js');
$posFecha = strpos($js, 'data-hito-fecha');
$posPorcentaje = strpos($js, 'data-hito-porcentaje');
$posMonto = strpos($js, 'data-hito-monto');
probar($posFecha !== false && $posPorcentaje !== false && $posMonto !== false, 'La cuota debe incluir vencimiento, porcentaje y monto.');
probar($posFecha < $posPorcentaje && $posPorcentaje < $posMonto, 'El porcentaje debe ir después del vencimiento y antes del monto.');
echo "ContratoEdicionWiringTest OK\n";

