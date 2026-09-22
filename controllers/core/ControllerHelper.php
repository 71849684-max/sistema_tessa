<?php
declare(strict_types=1);

require_once __DIR__ . '/../../capalogica/core/RespuestaHelper.php';
require_once __DIR__ . '/../../capalogica/core/SessionService.php';

final class ControllerHelper
{
    private static ?array $entrada = null;

    public static function input(): array
    {
        if (self::$entrada !== null) {
            return self::$entrada;
        }
        $contenido = file_get_contents('php://input') ?: '';
        $json = str_contains((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')
            ? json_decode($contenido, true) : null;
        return self::$entrada = is_array($json) ? $json : array_merge($_GET, $_POST);
    }

    public static function responder(array $respuesta, int $estado = 200): never
    {
        http_response_code($estado);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function post(): bool
    {
        return strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    public static function exigirPostCsrf(): void
    {
        if (!self::post()) {
            self::responder(RespuestaHelper::error('Método no permitido.'), 405);
        }
        $token = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? (self::input()['csrf_token'] ?? ''));
        if (!SessionService::validarCsrf($token)) {
            self::responder(RespuestaHelper::error('La solicitud expiró. Actualice la página e intente otra vez.'), 419);
        }
    }
}
