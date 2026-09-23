<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/_modulo.php';

$campos = <<<'HTML'
<input type="hidden" name="accion" value="guardar">
<input type="hidden" name="id_cliente" value="0">
<div class="form-grid">
    <label>Tipo de cliente<select name="tipo" data-cliente-tipo><option value="NATURAL">Persona natural</option><option value="JURIDICA">Empresa</option></select></label>
    <label><span data-documento-etiqueta>DNI</span><input name="documento" data-cliente-documento inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required></label>
    <div class="wide reniec-action" data-reniec-action><button class="secondary outline compact" type="button" data-consultar-reniec aria-label="Consultar DNI en RENIEC" title="Consultar DNI en RENIEC"><span aria-hidden="true">⌕</span></button><span data-reniec-status role="status" aria-live="polite">También puede ingresar los datos manualmente.</span></div>
    <label data-natural-fields>Nombres<input name="nombres" required></label>
    <label data-natural-fields>Apellidos<input name="apellidos" required></label>
    <label class="wide" data-juridica-fields hidden>Razón social<input name="razon_social"></label>
    <label>Correo<input name="correo" type="email"></label>
    <label>Teléfono<input name="telefono"></label>
    <label class="wide">Dirección<input name="direccion"></label>
    <label>Estado<select name="estado"><option value="1">Activo</option><option value="0">Inactivo</option></select></label>
</div>
HTML;

moduloCrud(
    'Clientes', 'clientes', 'controllers/ventas/cliente_controller.php',
    ['documento' => 'Documento', 'nombre' => 'Nombre / razón social', 'telefono' => 'Teléfono', 'estado' => 'Estado'],
    $campos, 'Gestión de personas naturales y empresas.',
    ['id' => 'id_cliente', 'grupo' => 'VENTAS', 'scripts' => ['resources/js/reniec.js', 'resources/js/sunat.js', 'resources/js/clientes.js']]
);
