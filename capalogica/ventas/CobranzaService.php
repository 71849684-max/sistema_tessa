<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../seguridad/AuditoriaService.php';

final class CobranzaService extends BaseService
{
    public function contratos(string $buscar = ''): array
    {
        try {
            $filas = trim($buscar) === '' ? $this->call('CALL sp_contrato_listar()') : $this->call('CALL sp_contrato_buscar_activos(?)', 's', [trim($buscar)]);
            return $this->ok('Contratos obtenidos.', $filas);
        }
        catch (Throwable $e) { error_log('CobranzaService contratos: ' . $e->getMessage()); return $this->error('No se pudieron consultar contratos.'); }
    }

    public function voucherDatos(int $cobranza, int $hito): array
    {
        try { return $this->fila($this->call('CALL sp_cobranza_voucher_datos(?,?)', 'ii', [$cobranza, $hito])); }
        catch (Throwable $e) { error_log('CobranzaService voucher: ' . $e->getMessage()); return []; }
    }

    public function listar(array $filtros = []): array
    {
        try { return $this->ok('Cobranzas obtenidas.', $this->call('CALL sp_cobranza_listar(?,?,?)', 'sss', [(string)($filtros['fecha_inicio'] ?? ''),(string)($filtros['fecha_fin'] ?? ''),(string)($filtros['estado'] ?? '')])); }
        catch (Throwable $e) { return $this->error('No se pudo listar cobranzas.'); }
    }

    public function hitos(int $contrato): array
    {
        try { return $this->ok('Cuotas obtenidas.', $this->call('CALL sp_hitos_cobranza_listar(?)', 'i', [$contrato])); }
        catch (Throwable $e) { return $this->error('No se pudieron obtener cuotas.'); }
    }

    public function participantesPago(int $contrato): array
    {
        if ($contrato < 1) return $this->error('Contrato inválido.');
        try {
            $fila = $this->fila($this->call('CALL sp_contrato_participantes_pago(?)', 'i', [$contrato]));
            if (is_string($fila['participantes'] ?? null)) {
                $participantes = json_decode($fila['participantes'], true);
                $fila['participantes'] = is_array($participantes) ? $participantes : [];
            }
            return $this->ok('Participantes obtenidos.', $fila);
        }
        catch (Throwable $e) { return $this->error('No se pudieron obtener los participantes.'); }
    }

    public function registrar(array $datos, ?array $voucher = null): array
    {
        $contrato = (int)($datos['id_contrato'] ?? 0);
        $detalles = is_string($datos['detalles'] ?? null) ? json_decode((string)$datos['detalles'], true) : ($datos['detalles'] ?? []);
        if ($contrato < 1 || !is_array($detalles) || $detalles === []) return $this->error('Pago incompleto.', ['detalles' => 'Seleccione cuotas y montos.']);
        foreach ($detalles as $detalle) if ((int)($detalle['id_hito'] ?? 0) < 1 || (float)($detalle['monto'] ?? 0) <= 0) return $this->error('Detalle de pago inválido.', ['detalles' => 'Monto positivo requerido.']);
        $medio = strtoupper(trim((string)($datos['medio_pago'] ?? 'EFECTIVO')));
        if (!in_array($medio, ['EFECTIVO', 'TRANSFERENCIA', 'YAPE', 'PLIN', 'TARJETA'], true)) return $this->error('Medio de pago inválido.');
        if ($medio !== 'EFECTIVO' && (trim((string)($datos['numero_operacion'] ?? '')) === '' || $voucher === null)) return $this->error('Falta comprobante.', ['voucher' => 'Ingrese operación y voucher para este medio.']);
        if ($medio === 'EFECTIVO') $voucher = null;
        try {
            $fila = $this->fila($this->call('CALL sp_cobranza_registrar(?,?,?,?,?,?,?,?,?,?,?)', 'iisssisssis', [
                $contrato,(int)($_SESSION['personal_id'] ?? 0),(string)($datos['fecha'] ?? date('Y-m-d H:i:s')),
                $medio,trim((string)($datos['numero_operacion'] ?? '')),(int)($datos['id_cliente_pagador'] ?? 0),
                json_encode(array_values($detalles), JSON_UNESCAPED_UNICODE),
                (string)($voucher['nombre_interno'] ?? ''), (string)($voucher['mime'] ?? ''),
                (int)($voucher['tamano'] ?? 0), (string)($voucher['hash'] ?? '')
            ]));
            (new AuditoriaService($this->db))->registrar('CREAR', 'cobranza', (int)($fila['id_cobranza'] ?? 0), [], ['contrato' => $contrato]);
            return $this->ok((string)($fila['mensaje'] ?? 'Cobranza registrada.'), $fila);
        } catch (Throwable $e) { error_log($e->getMessage()); return $this->error('No se pudo registrar la cobranza.'); }
    }

    public function anular(int $idCobranza, string $motivo): array
    {
        if ($idCobranza < 1) return $this->error('Cobranza inválida.', ['id_cobranza' => 'Requerido.']);
        if (trim($motivo) === '') return $this->error('Motivo obligatorio.', ['motivo' => 'El motivo de anulación es obligatorio.']);
        try {
            $fila = $this->fila($this->call('CALL sp_cobranza_anular(?,?)', 'is', [$idCobranza, trim($motivo)]));
            (new AuditoriaService($this->db))->registrar('ANULAR', 'cobranza', $idCobranza, [], ['motivo' => $motivo]);
            return $this->ok((string)($fila['mensaje'] ?? 'Cobranza anulada.'), $fila);
        } catch (Throwable $e) { error_log($e->getMessage()); return $this->error('No se pudo anular la cobranza.'); }
    }
}
