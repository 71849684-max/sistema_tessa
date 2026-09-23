<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$vista = file_get_contents(__DIR__ . '/../capacliente/ventas/cobranzas.php');
probar(str_contains($vista, 'data-cobranza-buscar'), 'El formulario debe buscar contrato por número, DNI o apellidos.');
probar(!str_contains($vista, '<select name="id_contrato"'), 'El contrato no debe cargarse en un selector masivo.');
echo "CobranzasVistaTest OK\n";
