<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../seguridad/AuditoriaService.php';

final class ServicioService extends BaseService
{
    public function listar(): array
    {
        try { return $this->ok('Servicios obtenidos.', $this->call('CALL sp_servicio_listar()')); }
        catch (Throwable $e) { return $this->error('No se pudo listar servicios.'); }
    }

    public function guardar(array $datos): array
    {
        $nombre = trim((string)($datos['nombre'] ?? ''));
        $precio = (float)($datos['precio_referencia'] ?? $datos['precio'] ?? 0);
        if ($nombre === '') return $this->error('Nombre obligatorio.', ['nombre' => 'Ingrese el servicio.']);
        if ($precio < 0) return $this->error('Precio inválido.', ['precio' => 'No puede ser negativo.']);
        try {
            $fila = $this->fila($this->call('CALL sp_servicio_guardar(?,?,?,?,?)', 'issdi', [
                (int)($datos['id_servicio'] ?? 0), $nombre, trim((string)($datos['descripcion'] ?? '')),
                $precio, (int)($datos['estado'] ?? 1)
            ]));
            (new AuditoriaService($this->db))->registrar('GUARDAR', 'servicio', (int)($fila['id_servicio'] ?? 0), [], $datos);
            return $this->ok((string)($fila['mensaje'] ?? 'Servicio guardado.'), $fila);
        } catch (Throwable $e) { error_log($e->getMessage()); return $this->error('No se pudo guardar el servicio.'); }
    }
}

