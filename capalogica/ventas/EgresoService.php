<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../seguridad/AuditoriaService.php';

final class EgresoService extends BaseService
{
    public function listar(array $filtros = []): array
    {
        try { return $this->ok('Egresos obtenidos.', $this->call('CALL sp_egreso_listar(?,?,?)', 'sss', [(string)($filtros['fecha_inicio'] ?? ''),(string)($filtros['fecha_fin'] ?? ''),(string)($filtros['estado'] ?? '')])); }
        catch (Throwable $e) { return $this->error('No se pudo listar egresos.'); }
    }

    public function registrar(array $datos): array
    {
        $fecha = (string)($datos['fecha'] ?? '');
        $monto = (float)($datos['monto'] ?? 0);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) return $this->error('Fecha inválida.', ['fecha' => 'Use AAAA-MM-DD.']);
        if ($monto <= 0) return $this->error('Monto inválido.', ['monto' => 'El monto debe ser mayor que cero.']);
        if (trim((string)($datos['concepto'] ?? '')) === '') return $this->error('Concepto obligatorio.', ['concepto' => 'Ingrese el concepto.']);
        try {
            $fila = $this->fila($this->call('CALL sp_egreso_registrar(?,?,?,?,?,?)', 'ssssdi', [$fecha,'GENERAL','',trim((string)$datos['concepto']),$monto,(int)($_SESSION['personal_id'] ?? 0)]));
            (new AuditoriaService($this->db))->registrar('CREAR', 'egreso', (int)($fila['id_egreso'] ?? 0), [], $datos);
            return $this->ok((string)($fila['mensaje'] ?? 'Egreso registrado.'), $fila);
        } catch (Throwable $e) { return $this->error('No se pudo registrar el egreso.'); }
    }

    public function anular(int $idEgreso, string $motivo): array
    {
        if ($idEgreso < 1 || trim($motivo) === '') return $this->error('Motivo obligatorio.', ['motivo' => 'Indique el motivo de anulación.']);
        try {
            $fila = $this->fila($this->call('CALL sp_egreso_anular(?,?)', 'is', [$idEgreso,trim($motivo)]));
            (new AuditoriaService($this->db))->registrar('ANULAR', 'egreso', $idEgreso, [], ['motivo' => $motivo]);
            return $this->ok((string)($fila['mensaje'] ?? 'Egreso anulado.'), $fila);
        } catch (Throwable $e) { return $this->error('No se pudo anular el egreso.'); }
    }
}
