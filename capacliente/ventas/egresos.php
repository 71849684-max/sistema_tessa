<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/_modulo.php';

$campos = <<<'HTML'
<input type="hidden" name="accion" value="registrar">
<div class="form-grid">
    <label>Fecha<input name="fecha" type="date" required></label>
    <label class="wide">Concepto<textarea name="concepto" required></textarea></label>
    <label>Monto<input name="monto" type="number" min="0.01" step="0.01" required></label>
</div>
HTML;
$filtros = <<<'HTML'
<form data-filter-form class="filter-grid">
    <label>Desde<input name="fecha_inicio" type="date"></label>
    <label>Hasta<input name="fecha_fin" type="date"></label>
    <label>Estado<select name="estado"><option value="">Todos</option><option>REGISTRADO</option><option>ANULADO</option></select></label>
    <button class="secondary" type="submit">Filtrar</button>
    <button class="secondary outline" type="button" data-filter-clear>Limpiar</button>
</form>
HTML;

moduloCrud(
    'Egresos', 'egresos', 'controllers/ventas/egreso_controller.php',
    ['fecha' => 'Fecha', 'concepto' => 'Concepto', 'monto' => 'Monto', 'estado' => 'Estado'],
    $campos, 'Registro y control de gastos generales y operativos.',
    ['id' => 'id_egreso', 'sinEdicion' => true, 'anulacion' => true, 'grupo' => 'VENTAS',
     'metricas' => [
         ['label' => 'Egresos registrados', 'type' => 'sum', 'field' => 'monto', 'state' => 'REGISTRADO', 'currency' => true, 'color' => 'accent-orange'],
         ['label' => 'Promedio por egreso', 'type' => 'avg', 'field' => 'monto', 'state' => 'REGISTRADO', 'currency' => true, 'color' => 'accent-blue'],
         ['label' => 'Anulados', 'type' => 'count', 'state' => 'ANULADO', 'color' => 'accent-purple'],
     ], 'filtros' => $filtros]
);
