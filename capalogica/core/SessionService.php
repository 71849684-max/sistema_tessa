<?php
declare(strict_types=1);

final class SessionService
{
    private const CSRF_KEY = 'csrf_token';
    public static function iniciar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'path' => '/',
            ]);
            session_start();
        }
    }

    public static function requiereSesion(): void
    {
        self::iniciar();
        if (empty($_SESSION['usuario_id'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(RespuestaHelper::error('Sesión no válida.', ['auth' => 'Debe iniciar sesión.']), JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function cerrar(): void
    {
        self::iniciar();
        $_SESSION = [];
        session_destroy();
    }

    public static function csrfToken(): string
    {
        self::iniciar();
        if (empty($_SESSION[self::CSRF_KEY]) || !is_string($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CSRF_KEY];
    }

    public static function validarCsrf(?string $token): bool
    {
        self::iniciar();
        $esperado = $_SESSION[self::CSRF_KEY] ?? null;
        return is_string($token) && is_string($esperado) && hash_equals($esperado, $token);
    }
}
