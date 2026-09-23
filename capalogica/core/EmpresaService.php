<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../seguridad/AuditoriaService.php';

final class EmpresaService extends BaseService
{
    private const CAMPOS = [
        'razon_social', 'ruc', 'eslogan', 'representante_nombre', 'representante_cargo',
        'representante_documento', 'domicilio_legal', 'ciudad', 'pie_direccion',
        'pie_telefonos', 'pie_correo', 'titulo_contrato',
    ];

    public function obtener(): array
    {
        try {
            $empresa = $this->fila($this->call('CALL sp_empresa_obtener()'));
            $empresa['medios_pago'] = $this->normalizarMediosPago($empresa['medios_pago'] ?? []) ?? [];
            return $this->ok('Empresa obtenida.', $empresa);
        } catch (Throwable $e) {
            error_log('EmpresaService obtener: ' . $e->getMessage());
            return $this->error('No se pudo consultar la configuración de la empresa.');
        }
    }

    public function guardar(array $datos): array
    {
        $empresa = [];
        foreach (self::CAMPOS as $campo) {
            $empresa[$campo] = trim((string)($datos[$campo] ?? ''));
        }
        $medios = $this->normalizarMediosPago($datos['medios_pago'] ?? []);
        if ($medios === null) return $this->error('Medios de pago inválidos.', ['medios_pago' => 'Complete medio, titular y número; el número y CCI solo deben contener dígitos.']);
        $empresa['medios_pago'] = (string)json_encode($medios, JSON_UNESCAPED_UNICODE);
        if ($empresa['razon_social'] === '') return $this->error('Datos de la empresa incompletos.', ['razon_social' => 'La razón social es obligatoria.']);
        if ($empresa['ruc'] !== '' && !preg_match('/^\d{11}$/', $empresa['ruc'])) return $this->error('RUC inválido.', ['ruc' => 'El RUC debe tener 11 dígitos.']);
        if ($empresa['representante_documento'] !== '' && !preg_match('/^\d{8}$/', $empresa['representante_documento'])) return $this->error('DNI inválido.', ['representante_documento' => 'El DNI del representante debe tener 8 dígitos.']);
        if ($empresa['pie_correo'] !== '' && !filter_var($empresa['pie_correo'], FILTER_VALIDATE_EMAIL)) return $this->error('Correo inválido.', ['pie_correo' => 'Revise el correo de contacto.']);
        try {
            $antes = $this->fila($this->call('CALL sp_empresa_obtener()'));
            $this->call('CALL sp_empresa_guardar(?,?,?,?,?,?,?,?,?,?,?,?,?)', str_repeat('s', count($empresa)), array_values($empresa));
            $despues = $this->fila($this->call('CALL sp_empresa_obtener()'));
            (new AuditoriaService($this->db))->registrar('EDITAR', 'empresa', 1, $antes, $despues);
            $despues['medios_pago'] = $this->normalizarMediosPago($despues['medios_pago'] ?? []) ?? [];
            return $this->ok('Configuración de la empresa guardada.', $despues);
        } catch (Throwable $e) {
            error_log('EmpresaService guardar: ' . $e->getMessage());
            return $this->error('No se pudo guardar la configuración de la empresa.');
        }
    }

    private function normalizarMediosPago(mixed $valor): ?array
    {
        if (is_string($valor)) $valor = json_decode($valor, true);
        if (!is_array($valor)) return null;
        $medios = [];
        foreach ($valor as $fila) {
            if (!is_array($fila)) return null;
            $medio = trim((string)($fila['medio'] ?? ''));
            $titular = trim((string)($fila['titular'] ?? ''));
            $numero = preg_replace('/\s+/', '', trim((string)($fila['numero'] ?? ''))) ?? '';
            $cci = preg_replace('/\s+/', '', trim((string)($fila['cci'] ?? ''))) ?? '';
            if ($medio === '' && $titular === '' && $numero === '' && $cci === '') continue;
            if ($medio === '' || $titular === '' || !preg_match('/^\d{6,30}$/', $numero) || ($cci !== '' && !preg_match('/^\d{6,30}$/', $cci))) return null;
            $medios[] = ['medio' => mb_substr($medio, 0, 80), 'titular' => mb_substr($titular, 0, 180), 'numero' => $numero, 'cci' => $cci];
        }
        return $medios;
    }
}
