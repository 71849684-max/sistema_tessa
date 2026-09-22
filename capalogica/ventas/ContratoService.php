<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../seguridad/AuditoriaService.php';
require_once __DIR__ . '/ClienteService.php';
require_once __DIR__ . '/ServicioService.php';

final class ContratoService extends BaseService
{
    public function catalogos(): array
    {
        try {
            return $this->ok('Catálogos obtenidos.', [
                'servicios' => $this->call('CALL sp_servicio_listar()'),
                'personal' => $this->call('CALL sp_personal_listar()'),
            ]);
        } catch (Throwable $e) { error_log('ContratoService catálogos: ' . $e->getMessage()); return $this->error('No se pudieron obtener los catálogos.'); }
    }

    public function buscarClientes(string $consulta): array
    {
        return (new ClienteService($this->db))->buscar($consulta);
    }

    public function listar(): array
    {
        try { return $this->ok('Contratos obtenidos.', $this->call('CALL sp_contrato_listar()')); }
        catch (Throwable $e) { return $this->error('No se pudo listar contratos.'); }
    }

    public function detalle(int $idContrato): array
    {
        if ($idContrato < 1) return $this->error('Contrato inválido.', ['id_contrato' => 'Requerido.']);
        try { return $this->ok('Contrato obtenido.', $this->call('CALL sp_contrato_detalle(?)', 'i', [$idContrato])); }
        catch (Throwable $e) { return $this->error('No se pudo obtener contrato.'); }
    }

    public function guardar(array $datos): array
    {
        $cliente = (int)($datos['id_cliente'] ?? 0);
        $servicio = (int)($datos['id_servicio'] ?? 0);
        $responsable = (int)($datos['id_responsable'] ?? $_SESSION['personal_id'] ?? 0);
        $tipo = strtoupper(trim((string)($datos['tipo'] ?? 'INDIVIDUAL')));
        $bruto = round((float)($datos['bruto'] ?? 0), 2);
        $descuento = round((float)($datos['descuento'] ?? 0), 2);
        $hitos = is_string($datos['hitos'] ?? null) ? json_decode((string)$datos['hitos'], true) : ($datos['hitos'] ?? []);
        $integrantes = is_string($datos['integrantes'] ?? null) ? json_decode((string)$datos['integrantes'], true) : ($datos['integrantes'] ?? []);
        if ($cliente < 1 || $servicio < 1 || $responsable < 1 || !in_array($tipo, ['INDIVIDUAL','GRUPAL'], true)) return $this->error('Datos contractuales incompletos.', ['contrato' => 'Cliente, servicio y responsable son requeridos.']);
        if ($bruto <= 0 || $descuento < 0 || $descuento > $bruto) return $this->error('Montos inválidos.', ['monto' => 'Revise bruto y descuento.']);
        if (!is_array($hitos) || $hitos === []) return $this->error('Debe registrar cuotas.', ['hitos' => 'Agregue al menos una cuota.']);
        $idsIntegrantes = [];
        if ($tipo === 'GRUPAL') {
            if (!is_array($integrantes)) return $this->error('Contrato grupal incompleto.', ['integrantes' => 'Agregue al menos un integrante.']);
            foreach ($integrantes as $integrante) {
                $id = (int)(is_array($integrante) ? ($integrante['id_cliente'] ?? 0) : $integrante);
                if ($id < 1 || $id === $cliente) return $this->error('Integrante inválido.', ['integrantes' => 'Seleccione clientes distintos del titular.']);
                $idsIntegrantes[$id] = $id;
            }
            if ($idsIntegrantes === []) return $this->error('Contrato grupal incompleto.', ['integrantes' => 'Agregue al menos un integrante.']);
        }
        $neto = round($bruto - $descuento, 2);
        $suma = 0.0;
        foreach ($hitos as $hito) {
            $monto = round((float)($hito['monto'] ?? 0), 2);
            if ($monto <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($hito['fecha_vencimiento'] ?? ''))) return $this->error('Cuota inválida.', ['hitos' => 'Cada cuota requiere fecha y monto positivo.']);
            $suma += $monto;
        }
        if (abs($neto - round($suma, 2)) > 0.001) return $this->error('Las cuotas no coinciden con el monto neto.', ['hitos' => 'La suma de cuotas debe ser igual al monto neto.']);
        try {
            if ($this->db instanceof Conexion) $this->db->iniciarTransaccion();
            $fila = $this->fila($this->call('CALL sp_contrato_crear(?,?,?,?,?,?,?,?)', 'iiissddi', [$cliente,$servicio,$responsable,$tipo,(string)($datos['fecha'] ?? date('Y-m-d')),$bruto,$descuento,$this->idUsuarioSesion()]));
            $idContrato = (int)($fila['id_contrato'] ?? 0);
            if ($idContrato < 1) throw new RuntimeException('SP no devolvió contrato.');
        } catch (Throwable $e) {
            if ($this->db instanceof Conexion) $this->db->revertir();
            error_log('ContratoService: ' . $e->getMessage());
            return $this->error('No se pudo guardar el contrato.');
        }
        return $this->guardarHitosEIntegrantes($idContrato, $hitos, array_values($idsIntegrantes), $datos, $fila);
    }

    private function guardarHitosEIntegrantes(int $idContrato, array $hitos, array $integrantes, array $datos, array $fila): array
    {
        try {
            foreach ($hitos as $indice => $hito) {
                $descripcion = trim((string)($hito['descripcion'] ?? ''));
                if ($descripcion === '') {
                    $descripcion = 'Cuota ' . ($indice + 1);
                }
                $this->call('CALL sp_contrato_hito_guardar(?,?,?,?,?)', 'iissd', [
                    $idContrato, (int)($hito['numero'] ?? $indice + 1), $descripcion,
                    (string)$hito['fecha_vencimiento'], (float)$hito['monto']
                ]);
            }
            if (strtoupper((string)$datos['tipo']) === 'GRUPAL') foreach ($integrantes as $id) {
                $this->call('CALL sp_contrato_integrante_guardar(?,?)', 'ii', [$idContrato,$id]);
            }
            $this->call('CALL sp_contrato_hitos_validar(?)', 'i', [$idContrato]);
            if ($this->db instanceof Conexion) $this->db->confirmar();
            (new AuditoriaService($this->db))->registrar('CREAR', 'contrato', $idContrato, [], ['numero' => $fila['con_numero'] ?? '']);
            return $this->ok('Contrato registrado.', $fila);
        } catch (Throwable $e) {
            if ($this->db instanceof Conexion) $this->db->revertir();
            error_log('ContratoService hito: ' . $e->getMessage());
            return $this->error('No se pudo guardar el cronograma.');
        }
    }

    public function anular(int $idContrato, string $motivo): array
    {
        if ($idContrato < 1 || trim($motivo) === '') return $this->error('Motivo obligatorio.', ['motivo' => 'Indique el motivo de anulación.']);
        try {
            $fila = $this->fila($this->call('CALL sp_contrato_anular(?,?)', 'is', [$idContrato, trim($motivo)]));
            (new AuditoriaService($this->db))->registrar('ANULAR', 'contrato', $idContrato, [], ['motivo' => $motivo]);
            return $this->ok((string)($fila['mensaje'] ?? 'Contrato anulado.'), $fila);
        } catch (Throwable $e) { return $this->error('No se pudo anular el contrato.'); }
    }
}
