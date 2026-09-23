<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/core/EmpresaService.php';

$db = new class {
    public array $llamadas = [];
    public array $filaEmpresa = [];
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        $this->llamadas[] = [$sql, $tipos, $parametros];
        if (str_starts_with($sql, 'CALL sp_empresa_obtener')) return $this->filaEmpresa === [] ? [] : [$this->filaEmpresa];
        if (str_starts_with($sql, 'CALL sp_empresa_guardar')) return [['resultado' => 1, 'mensaje' => 'Configuración de la empresa guardada.']];
        if (str_starts_with($sql, 'CALL sp_auditoria_registrar')) return [];
        throw new RuntimeException('Procedimiento inesperado: ' . $sql);
    }
};

$service = new EmpresaService($db);

$sinRazon = $service->guardar(['ruc' => '20123456789']);
probar(!$sinRazon['exito'] && isset($sinRazon['errores']['razon_social']), 'La razón social es obligatoria.');

$ruccorto = $service->guardar(['razon_social' => 'TESSA S.A.C.', 'ruc' => '123']);
probar(!$ruccorto['exito'] && isset($ruccorto['errores']['ruc']), 'El RUC debe tener 11 dígitos.');

$dnicorto = $service->guardar(['razon_social' => 'TESSA S.A.C.', 'representante_documento' => '123']);
probar(!$dnicorto['exito'] && isset($dnicorto['errores']['representante_documento']), 'El DNI debe tener 8 dígitos.');

$correoMal = $service->guardar(['razon_social' => 'TESSA S.A.C.', 'pie_correo' => 'no-es-correo']);
probar(!$correoMal['exito'] && isset($correoMal['errores']['pie_correo']), 'El correo de contacto debe ser válido.');

$db->filaEmpresa = ['razon_social' => 'TESSA S.A.C.'];
$guardado = $service->guardar([
    'razon_social' => '  TESSA S.A.C. ', 'ruc' => '20123456789', 'eslogan' => 'Asesoría profesional',
    'medios_pago' => [['medio' => 'BCP - Soles', 'titular' => 'Titular oficial', 'numero' => '35540210891098', 'cci' => '00235514021089109862']],
]);
probar($guardado['exito'], 'La configuración válida debe guardarse.');

$guardarLlamada = null;
foreach ($db->llamadas as $llamada) {
    if (str_starts_with($llamada[0], 'CALL sp_empresa_guardar')) { $guardarLlamada = $llamada; break; }
}
probar($guardarLlamada !== null, 'Debe invocar sp_empresa_guardar.');
probar($guardarLlamada[1] === 'sssssssssssss' && count($guardarLlamada[2]) === 13, 'El procedimiento recibe los datos de empresa y los medios de pago.');
probar($guardarLlamada[2][0] === 'TESSA S.A.C.', 'El primer parámetro es la razón social sin espacios.');
probar(str_contains((string)$guardarLlamada[2][12], 'BCP - Soles') && str_contains((string)$guardarLlamada[2][12], '00235514021089109862'), 'Los medios de pago oficiales se guardan como filas configurables.');

$db->filaEmpresa = [
    'razon_social' => 'TESSA S.A.C.',
    'medios_pago' => '[{"medio":"BCP - Soles","titular":"Titular oficial","numero":"35540210891098","cci":"00235514021089109862"}]',
];
$obtenido = $service->obtener();
probar($obtenido['exito'] && ($obtenido['datos']['razon_social'] ?? '') === 'TESSA S.A.C.', 'La empresa se puede consultar.');
probar(is_array($obtenido['datos']['medios_pago'] ?? null) && ($obtenido['datos']['medios_pago'][0]['medio'] ?? '') === 'BCP - Soles', 'La consulta debe devolver los medios de pago como filas para que la configuración los muestre.');

$menu = file_get_contents(__DIR__ . '/../capalogica/core/MenuService.php');
probar(str_contains($menu, "'codigo' => 'configuracion'"), 'El menú debe incluir la sección de configuración.');
probar(str_contains($menu, 'capacliente/rrhh/configuracion.php'), 'El menú debe enlazar a la vista de configuración.');

$permisosJs = file_get_contents(__DIR__ . '/../resources/js/permisos.js');
probar(str_contains($permisosJs, "configuracion: ['ver', 'editar']"), 'Los permisos de configuración son ver y editar.');

$vista = file_get_contents(__DIR__ . '/../capacliente/rrhh/configuracion.php');
probar(str_contains($vista, "'configuracion'"), 'La vista usa la sección configuracion.');
probar(str_contains($vista, 'data-empresa-form'), 'La vista declara el formulario de empresa.');
probar(str_contains($vista, 'data-medios-pago'), 'La vista muestra la sección de medios de pago oficiales.');

$jsConfig = file_get_contents(__DIR__ . '/../resources/js/configuracion.js');
probar(str_contains($jsConfig, "'razon_social'") && str_contains($jsConfig, "'titulo_contrato'"), 'El JS lista los campos de la empresa.');
probar(str_contains($jsConfig, 'medios_pago'), 'El JS administra los medios de pago de la empresa.');

$controller = file_get_contents(__DIR__ . '/../controllers/core/empresa_controller.php');
probar(str_contains($controller, "PermisoService::requiere('configuracion'"), 'El controlador exige permiso de la sección configuracion.');
probar(str_contains($controller, 'exigirPostCsrf'), 'El controlador exige CSRF en las escrituras.');

echo "ConfiguracionEmpresaTest OK\n";
