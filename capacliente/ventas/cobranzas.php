<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/_modulo.php';

moduloCrud(
    'Cobranzas',
    'cobranzas',
    'controllers/ventas/cobranza_controller.php',
    ['id_cobranza' => 'ID', 'con_numero' => 'Contrato', 'fecha' => 'Fecha', 'monto_total' => 'Monto', 'estado' => 'Estado'],
    '<input type="hidden" name="accion" value="registrar"><input type="hidden" name="detalles" data-detalles-json><div class="form-grid"><label>Contrato<select name="id_contrato" data-cobranza-contrato required><option value="">Cargando contratos…</option></select></label><button class="secondary align-end" type="button" data-cargar-hitos>Cargar cuotas</button><label>Fecha<input name="fecha" type="datetime-local" required></label><label>Medio<select name="medio_pago"><option>TRANSFERENCIA</option><option>YAPE</option><option>PLIN</option><option>EFECTIVO</option><option>TARJETA</option></select></label><label class="wide">N° de operación<input name="numero_operacion"></label><label class="wide">Voucher (PDF, JPG o PNG, hasta 5 MiB)<input name="voucher" type="file" accept=".pdf,image/jpeg,image/png"></label></div><section class="line-editor"><div class="editor-heading"><div><strong>Aplicación del pago</strong><span>Seleccione una o más cuotas y especifique el monto.</span></div></div><div data-hitos-cobranza><p class="hint">Seleccione el contrato y cargue sus cuotas pendientes.</p></div></section>',
    'Registra pagos parciales o completos, con comprobante validado.',
    ['id'=>'id_cobranza','sinEdicion'=>true,'anulacion'=>true,'grupo'=>'VENTAS','formularioTitulo'=>'Registrar pago','metricas'=>[['label'=>'Cobrado','type'=>'sum','field'=>'monto_total','state'=>'REGISTRADO','currency'=>true,'color'=>'accent-green'],['label'=>'Operaciones','type'=>'count','state'=>'REGISTRADO','color'=>'accent-blue'],['label'=>'Anuladas','type'=>'count','state'=>'ANULADO','color'=>'accent-purple']],'filtros'=>'<form data-filter-form class="filter-grid"><label>Desde<input name="fecha_inicio" type="date"></label><label>Hasta<input name="fecha_fin" type="date"></label><label>Estado<select name="estado"><option value="">Todos</option><option>REGISTRADO</option><option>ANULADO</option></select></label><button class="secondary" type="submit">Filtrar</button><button class="secondary outline" type="button" data-filter-clear>Limpiar</button></form>','scripts'=>['resources/js/cobranzas.js']]
);
