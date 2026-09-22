<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';

final class AuditoriaService extends BaseService
{
    public function registrar(string $accion, string $entidad, int $idRegistro, array $antes = [], array $despues = []): void
    {
        try {
            $this->call('CALL sp_auditoria_registrar(?,?,?,?,?,?,?)', 'siissss', [
                $accion, $this->idUsuarioSesion(), $idRegistro, $entidad,
                json_encode($antes, JSON_UNESCAPED_UNICODE),
                json_encode($despues, JSON_UNESCAPED_UNICODE),
                (string)($_SERVER['REMOTE_ADDR'] ?? '')
            ]);
        } catch (Throwable $e) {
            error_log('AuditoriaService: ' . $e->getMessage());
            throw new RuntimeException('No se pudo registrar la auditoría.', 0, $e);
        }
    }

    public function listar(array $filtros = []): array
    {
        try {
            return $this->ok('Auditoría obtenida.', $this->call('CALL sp_auditoria_listar(?,?,?)', 'sss', [
                (string)($filtros['fecha_inicio'] ?? ''),
                (string)($filtros['fecha_fin'] ?? ''),
                (string)($filtros['buscar'] ?? '')
            ]));
        } catch (Throwable $e) {
            return $this->error('No se pudo consultar auditoría.');
        }
    }
}
