<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/documentos/DocxService.php';

if (!class_exists('ZipArchive')) {
    echo "DocxContratoTest omitido; falta ext-zip.\n";
    exit(0);
}

$clausulas = tempnam(sys_get_temp_dir(), 'tessa-clausulas-');
$medios = tempnam(sys_get_temp_dir(), 'tessa-medios-');
file_put_contents($clausulas, json_encode([[
    'titulo' => 'PRIMERO: OBJETO DEL CONTRATO',
    'parrafos' => ['Contenido legal aprobado de prueba para {NOMBRE_CLIENTE}.'],
], [
    'titulo' => 'DÉCIMO TERCERO: DISPOSICIONES FINALES',
    'parrafos' => ['Cierre aprobado en {CIUDAD}.'],
]], JSON_UNESCAPED_UNICODE));
file_put_contents($medios, json_encode([['medio' => 'Banco aprobado', 'titular' => 'Titular configurado', 'numero' => '0001', 'cci' => '0002']]));
putenv('TESSA_CONTRATO_CLAUSULAS=' . $clausulas);
putenv('TESSA_CONTRATO_MEDIOS_PAGO=' . $medios);
putenv('TESSA_CONTRATO_PLANTILLA=' . __DIR__ . '/../storage/plantillas/contrato-tessa.docx');

$resultado = (new DocxService())->contrato([
    'id_contrato' => 1, 'con_numero' => 'TES-2026-000001', 'cliente_label' => 'Cliente Real',
    'documento' => '76121019', 'direccion' => 'Domicilio del cliente', 'correo' => 'cliente@example.test',
    'tipo' => 'GRUPAL', 'hitos' => [['numero' => 1, 'descripcion' => 'Primera cuota', 'fecha_vencimiento' => '2026-10-01', 'monto' => 100]],
    'integrantes' => [['nombres' => 'No debe aparecer', 'documento' => '00000000']],
], [
    'razon_social' => 'Empresa Tessa Configurada', 'ruc' => '20123456789', 'domicilio_legal' => 'Domicilio legal',
    'representante_nombre' => 'Representante Configurado', 'representante_cargo' => 'Gerente',
    'representante_documento' => '12345678', 'ciudad' => 'Lima', 'titulo_contrato' => 'PROGRAMA CONFIGURADO',
    'pie_telefonos' => '+51 993 389 800 | +51 937 711 229 | +51 999 999 999',
    'medios_pago' => [
        ['medio' => 'BCP - Soles', 'titular' => 'Titular configurado', 'numero' => '35540210891098', 'cci' => '00235514021089109862'],
        ['medio' => 'Yape', 'titular' => 'Titular configurado', 'numero' => '999999999', 'cci' => ''],
    ],
]);

$docx = tempnam(sys_get_temp_dir(), 'tessa-docx-test-');
file_put_contents($docx, $resultado['contenido']);
$zip = new ZipArchive();
probar($zip->open($docx) === true, 'El resultado debe ser un DOCX real.');
$documentXml = $zip->getFromName('word/document.xml');
$footerXml = $zip->getFromName('word/footer1.xml');
$media = [];
for ($indice = 0; $indice < $zip->numFiles; $indice++) {
    $nombre = $zip->getNameIndex($indice);
    if (is_string($nombre) && str_starts_with($nombre, 'word/media/')) {
        $media[] = $zip->getFromIndex($indice);
    }
}
probar(str_contains((string)$documentXml, '76121019') && str_contains((string)$documentXml, 'cliente@example.test'), 'El documento debe usar datos del usuario.');
probar(str_contains((string)$documentXml, 'Conste por el presente documento') && str_contains((string)$documentXml, 'EL CONTRATO tiene por objeto brindar'), 'El documento debe conservar el formato legal de la plantilla oficial.');
probar(str_contains((string)$documentXml, 'DÉCIMO TERCERO') && str_contains((string)$documentXml, 'BCP - Soles') && str_contains((string)$documentXml, '00235514021089109862') && str_contains((string)$documentXml, 'Yape') && !str_contains((string)$documentXml, 'Banco aprobado'), 'Debe incluir los medios oficiales configurados en filas independientes.');
probar(str_contains((string)$documentXml, 'S/ 100.00') && str_contains((string)$documentXml, '1 de octubre de 2026'), 'Debe incluir los montos y vencimientos reales del contrato.');
probar(!str_contains((string)$documentXml, 'No debe aparecer'), 'Los integrantes no deben incluirse en la plantilla.');
probar(str_contains((string)$documentXml, 'Empresa Tessa Configurada') && str_contains((string)$documentXml, 'Representante Configurado'), 'El documento debe usar los datos configurados de la empresa.');
probar(substr_count((string)$documentXml, '+51 999 999 999') === 2, 'El documento debe mostrar todos los teléfonos configurados en cada cláusula de contacto.');
probar($media !== [], 'La cabecera debe incrustar el logo.');
$logoContrato = __DIR__ . '/../assets/recursos/imagenes/tessa-logo-contrato.png';
probar(is_file($logoContrato), 'La cabecera debe usar el logo institucional del contrato.');
$hashLogo = hash_file('sha256', $logoContrato);
probar(in_array($hashLogo, array_map(static fn(string $imagen): string => hash('sha256', $imagen), $media), true), 'El Word debe incrustar el logo institucional del contrato.');
probar(count($media) === 1, 'El Word debe incluir un solo logo institucional.');
$zip->close();
unlink($docx);
unlink($clausulas);
unlink($medios);
echo "DocxContratoTest OK\n";
