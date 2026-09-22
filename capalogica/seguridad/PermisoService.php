<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/SessionService.php';
require_once __DIR__ . '/../core/BaseService.php';

final class PermisoService
{
    /** @param list<array<string,mixed>> $cargo @param list<array<string,mixed>> $individuales */
    public function __construct(private array $cargo = [], private array $individuales = [])
    {
    }

    public function autorizar(string $modulo, string $accion): bool
    {
        $modulo = strtolower(trim($modulo));
        $accion = strtolower(trim($accion));

        foreach ($this->individuales as $permiso) {
            if (strtolower((string)$permiso['modulo']) === $modulo
                && strtolower((string)$permiso['accion']) === $accion
                && (int)$permiso['permitido'] === 0) {
                return false;
            }
        }
        foreach ($this->individuales as $permiso) {
            if (strtolower((string)$permiso['modulo']) === $modulo
                && strtolower((string)$permiso['accion']) === $accion
                && (int)$permiso['permitido'] === 1) {
                return true;
            }
        }
        foreach ($this->cargo as $permiso) {
            if (strtolower((string)$permiso['modulo']) === $modulo
                && strtolower((string)$permiso['accion']) === $accion
                && (int)$permiso['permitido'] === 1) {
                return true;
            }
        }
        return false;
    }

    public static function requiere(string $modulo, string $accion = 'ver'): void
    {
        SessionService::requiereSesion();
        $service = self::efectivo();
        if (!$service->autorizar($modulo, $accion)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['exito' => false, 'mensaje' => 'No tiene permiso para esta acción.', 'datos' => null, 'errores' => ['permiso' => $modulo . '.' . $accion]], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function puede(string $modulo, string $accion = 'ver'): bool
    {
        if (empty($_SESSION['personal_id'])) {
            return false;
        }
        try {
            return self::efectivo()->autorizar($modulo, $accion);
        } catch (Throwable $e) {
            error_log('PermisoService: ' . $e->getMessage());
            return false;
        }
    }

    public static function efectivo(): self
    {
        $filas = (new Conexion())->call('CALL sp_permiso_efectivo_listar(?)', 'i', [(int)$_SESSION['personal_id']]);
        $cargo = [];
        $individuales = [];
        foreach ($filas as $permiso) {
            if (($permiso['origen'] ?? '') === 'PERSONA') $individuales[] = $permiso;
            else $cargo[] = $permiso;
        }
        return new self($cargo, $individuales);
    }
}
