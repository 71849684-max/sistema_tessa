<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../seguridad/AuditoriaService.php';

final class CargoService extends BaseService
{
    public function listar(): array
    {
        try { return $this->ok('Cargos obtenidos.', $this->call('CALL sp_cargo_listar()')); }
        catch (Throwable $e) { return $this->error('No se pudo listar cargos.'); }
    }

    public function guardar(array $datos): array
    {
        $nombre = trim((string)($datos['nombre'] ?? ''));
        if ($nombre === '') return $this->error('Nombre obligatorio.', ['nombre' => 'Ingrese el cargo.']);
        try {
            $fila = $this->fila($this->call('CALL sp_cargo_guardar(?,?,?)', 'isi', [(int)($datos['id_cargo'] ?? 0),$nombre,(int)($datos['estado'] ?? 1)]));
            (new AuditoriaService($this->db))->registrar('GUARDAR','cargo',(int)($fila['id_cargo'] ?? 0),[],$datos);
            return $this->ok((string)($fila['mensaje'] ?? 'Cargo guardado.'), $fila);
        } catch (Throwable $e) { return $this->error('No se pudo guardar cargo.'); }
    }
}

