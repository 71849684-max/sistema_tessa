<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/_modulo.php';

$campos = <<<'HTML'
<input type="hidden" name="accion" value="guardar">
<input type="hidden" name="hitos" data-hitos-json>
<input type="hidden" name="integrantes" data-integrantes-json>
<div class="form-grid">
    <div class="catalog-search wide">
        <label>Cliente principal <input data-cliente-buscar type="search" placeholder="Buscar por DNI, nombre o apellidos" required></label>
        <input type="hidden" name="id_cliente" data-cliente-id>
        <div class="buscador-resultados" data-cliente-resultados hidden></div>
        <div class="catalog-selected" data-cliente-seleccion hidden><span></span><button type="button" data-cliente-quitar aria-label="Quitar cliente principal">×</button></div>
    </div>
    <label>Servicio<select name="id_servicio" data-servicios required><option value="">Cargando servicios…</option></select></label>
    <label>Responsable<select name="id_responsable" data-personal required><option value="">Cargando personal…</option></select></label>
    <label>Tipo<select name="tipo" data-contract-type><option value="INDIVIDUAL">Individual</option><option value="GRUPAL">Grupal</option></select></label>
    <label>Fecha<input name="fecha" type="date" required></label>
    <label>Bruto<input name="bruto" type="number" min="0.01" step="0.01" required></label>
    <label>Descuento<input name="descuento" type="number" min="0" step="0.01" value="0"></label>
</div>
<section class="line-editor">
    <div class="editor-heading"><div><strong>Cuotas e hitos</strong><span>La suma debe coincidir con el monto neto.</span></div><button class="secondary compact" type="button" data-add-hito>Agregar cuota</button></div>
    <div data-hitos-list></div>
</section>
<section class="line-editor" data-integrantes-editor hidden>
    <div class="editor-heading"><div><strong>Integrantes del grupo</strong><span>Busque y agregue al menos un cliente adicional.</span></div></div>
    <div class="catalog-search">
        <label>Buscar integrante<input data-integrante-buscar type="search" placeholder="DNI, nombre o apellidos"></label>
        <div class="buscador-resultados" data-integrante-resultados hidden></div>
    </div>
    <div class="member-list" data-integrantes-list></div>
</section>
HTML;

moduloCrud(
    'Contratos', 'contratos', 'controllers/ventas/contrato_controller.php',
    ['con_numero' => 'N° contrato', 'cliente_nombre' => 'Cliente', 'servicio_nombre' => 'Servicio', 'monto_neto' => 'Neto', 'saldo' => 'Saldo', 'estado' => 'Estado'],
    $campos,
    'Registra el acuerdo, sus hitos de pago y los integrantes si corresponde.',
    ['id' => 'id_contrato', 'sinEdicion' => true, 'anulacion' => true, 'grupo' => 'VENTAS', 'formularioTitulo' => 'Nuevo contrato', 'scripts' => ['resources/js/componentes/buscador_catalogo.js', 'resources/js/contratos.js']]
);
