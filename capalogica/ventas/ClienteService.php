<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../seguridad/AuditoriaService.php';

final class ClienteService extends BaseService
{
    public function listar(): array
    {
        try { return $this->ok('Clientes obtenidos.', $this->call('CALL sp_cliente_listar()')); }
        catch (Throwable $e) { error_log($e->getMessage()); return $this->error('No se pudo listar clientes.'); }
    }

    public function buscar(string $consulta): array
    {
        $consulta = trim($consulta);
        if (mb_strlen($consulta) < 2 || mb_strlen($consulta) > 100) return $this->error('Búsqueda inválida.', ['buscar' => 'Ingrese entre 2 y 100 caracteres.']);
        try { return $this->ok('Clientes encontrados.', $this->call('CALL sp_cliente_buscar(?)', 's', [$consulta])); }
        catch (Throwable $e) { error_log('ClienteService buscar: ' . $e->getMessage()); return $this->error('No se pudieron buscar clientes.'); }
    }

    public function guardar(array $datos): array
    {
        $tipo = strtoupper(trim((string)($datos['tipo'] ?? 'NATURAL')));
        $documento = trim((string)($datos['documento'] ?? ''));
        $nombres = trim((string)($datos['nombres'] ?? ''));
        $apellidos = trim((string)($datos['apellidos'] ?? ''));
        $razon = trim((string)($datos['razon_social'] ?? ''));
        if (!in_array($tipo, ['NATURAL', 'JURIDICA'], true)) return $this->error('Tipo de cliente inválido.', ['tipo' => 'Seleccione un tipo.']);
        if (($tipo === 'NATURAL' && !preg_match('/^\d{8}$/', $documento))
            || ($tipo === 'JURIDICA' && !preg_match('/^\d{11}$/', $documento))) {
            return $this->error('Documento inválido.', ['documento' => $tipo === 'NATURAL' ? 'El DNI debe tener 8 dígitos.' : 'El RUC debe tener 11 dígitos.']);
        }
        if ($tipo === 'NATURAL' && $nombres === '') return $this->error('Faltan datos del cliente.', ['nombres' => 'Ingrese los nombres.']);
        if ($tipo === 'NATURAL' && $apellidos === '') return $this->error('Faltan datos del cliente.', ['apellidos' => 'Ingrese los apellidos.']);
        if ($tipo === 'JURIDICA' && $razon === '') return $this->error('Faltan datos del cliente.', ['razon_social' => 'Ingrese la razón social.']);
        if ($tipo === 'JURIDICA') { $nombres = ''; $apellidos = ''; }
        else $razon = '';
        try {
            $fila = $this->fila($this->call('CALL sp_cliente_guardar(?,?,?,?,?,?,?,?,?,?)', 'issssssssi', [
                (int)($datos['id_cliente'] ?? 0), $tipo, $documento, $nombres, $apellidos, $razon,
                trim((string)($datos['correo'] ?? '')), trim((string)($datos['telefono'] ?? '')),
                trim((string)($datos['direccion'] ?? '')), (int)($datos['estado'] ?? 1)
            ]));
            (new AuditoriaService($this->db))->registrar('GUARDAR', 'cliente', (int)($fila['id_cliente'] ?? 0), [], $datos);
            return $this->ok((string)($fila['mensaje'] ?? 'Cliente guardado.'), $fila);
        } catch (Throwable $e) { error_log($e->getMessage()); return $this->error('No se pudo guardar el cliente.'); }
    }
}
