<?php
declare(strict_types=1);

require_once __DIR__ . '/../../capaconexion/Conexion.php';
require_once __DIR__ . '/RespuestaHelper.php';

abstract class BaseService
{
    protected object $db;

    public function __construct(?object $db = null)
    {
        $this->db = $db ?? new Conexion();
    }

    protected function ok(string $mensaje, mixed $datos = null): array
    {
        return RespuestaHelper::ok($mensaje, $datos);
    }

    protected function error(string $mensaje, array $errores = [], mixed $datos = null): array
    {
        return RespuestaHelper::error($mensaje, $errores, $datos);
    }

    protected function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        return $this->db->call($sql, $tipos, $parametros);
    }

    protected function idUsuarioSesion(): int
    {
        return (int)($_SESSION['usuario_id'] ?? 0);
    }

    protected function fila(array $filas): array
    {
        return $filas[0] ?? [];
    }
}

