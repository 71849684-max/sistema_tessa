<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../seguridad/AuditoriaService.php';

final class PersonalService extends BaseService
{
    public function listar(): array
    {
        try { return $this->ok('Personal obtenido.', $this->call('CALL sp_personal_listar()')); }
        catch (Throwable $e) { return $this->error('No se pudo listar personal.'); }
    }

    public function cargos(): array
    {
        try { return $this->ok('Cargos obtenidos.', $this->call('CALL sp_cargo_listar()')); }
        catch (Throwable $e) { error_log('PersonalService cargos: ' . $e->getMessage()); return $this->error('No se pudieron consultar los cargos.'); }
    }

    public function guardar(array $datos): array
    {
        $dni = trim((string)($datos['dni'] ?? ''));
        $nombres = trim((string)($datos['nombres'] ?? ''));
        $apellidos = trim((string)($datos['apellidos'] ?? ''));
        $cargo = (int)($datos['id_cargo'] ?? 0);
        if (!preg_match('/^\d{8}$/', $dni)) return $this->error('DNI inválido.', ['dni' => 'El DNI debe tener 8 dígitos.']);
        if ($nombres === '' || $apellidos === '' || $cargo < 1) return $this->error('Datos obligatorios incompletos.', ['personal' => 'Nombres, apellidos y cargo son requeridos.']);
        try {
            $fila = $this->fila($this->call('CALL sp_personal_guardar(?,?,?,?,?,?,?,?)', 'isssssii', [
                (int)($datos['id_personal'] ?? 0),$dni,$nombres,$apellidos,trim((string)($datos['correo'] ?? '')),
                trim((string)($datos['telefono'] ?? '')),$cargo,(int)($datos['estado'] ?? 1)
            ]));
            (new AuditoriaService($this->db))->registrar('GUARDAR','personal',(int)($fila['id_personal'] ?? 0),[],$datos);
            return $this->ok((string)($fila['mensaje'] ?? 'Personal guardado.'),$fila);
        } catch (Throwable $e) { return $this->error('No se pudo guardar personal.'); }
    }
}
