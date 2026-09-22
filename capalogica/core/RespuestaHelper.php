<?php
declare(strict_types=1);

final class RespuestaHelper
{
    public static function ok(string $mensaje, mixed $datos = null): array
    {
        return ['exito' => true, 'mensaje' => $mensaje, 'datos' => $datos, 'errores' => []];
    }

    public static function error(string $mensaje, array $errores = [], mixed $datos = null): array
    {
        return ['exito' => false, 'mensaje' => $mensaje, 'datos' => $datos, 'errores' => $errores];
    }
}

