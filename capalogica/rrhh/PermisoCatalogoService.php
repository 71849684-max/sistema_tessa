<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';

final class PermisoCatalogoService extends BaseService
{
    public function listar(): array
    {
        try {
            return $this->ok('Permisos obtenidos.', $this->call('CALL sp_permiso_listar()'));
        } catch (Throwable $e) {
            return $this->error('No se pudieron obtener permisos.');
        }
    }

    public function estadoCargo(int $idCargo): array
    {
        if ($idCargo < 1) return $this->error('Cargo inválido.', ['id_cargo' => 'Requerido.']);
        try { return $this->ok('Plantilla obtenida.', $this->call('CALL sp_cargo_permiso_configuracion_listar(?)', 'i', [$idCargo])); }
        catch (Throwable $e) { error_log('Permisos cargo: ' . $e->getMessage()); return $this->error('No se pudo consultar la plantilla.'); }
    }

    public function catalogos(): array
    {
        try {
            return $this->ok('Catálogos obtenidos.', [
                'cargos' => $this->call('CALL sp_cargo_listar()'),
                'personas' => $this->call('CALL sp_personal_listar()'),
            ]);
        } catch (Throwable $e) { error_log('Permisos catálogos: ' . $e->getMessage()); return $this->error('No se pudieron consultar cargos y personas.'); }
    }

    public function estadoPersona(int $idPersonal): array
    {
        if ($idPersonal < 1) return $this->error('Persona inválida.', ['id_personal' => 'Requerido.']);
        try { return $this->ok('Permisos obtenidos.', $this->call('CALL sp_persona_permiso_configuracion_listar(?)', 'i', [$idPersonal])); }
        catch (Throwable $e) { error_log('Permisos persona: ' . $e->getMessage()); return $this->error('No se pudieron consultar los permisos.'); }
    }
}
