<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/SessionService.php';
require_once __DIR__ . '/CaptchaService.php';
require_once __DIR__ . '/AuditoriaService.php';

final class AuthService extends BaseService
{
    public function login(array $datos): array
    {
        SessionService::iniciar();
        $captcha = new CaptchaService($_SESSION);
        if (!$captcha->validar((string)($datos['captcha'] ?? ''))) {
            return $this->error('CAPTCHA inválido o vencido.', ['captcha' => 'Renueve el código e intente otra vez.']);
        }

        $usuario = trim((string)($datos['usuario'] ?? ''));
        $clave = (string)($datos['clave'] ?? '');
        if ($usuario === '' || $clave === '') {
            return $this->error('Usuario y contraseña son obligatorios.', ['credenciales' => 'Datos incompletos.']);
        }

        try {
            $fila = $this->fila($this->call('CALL sp_login_obtener_hash(?)', 's', [$usuario]));
            if ($fila === [] || (int)($fila['usu_estado'] ?? 0) !== 1) {
                $this->call('CALL sp_login_registrar_intento(?,?)', 'si', [$usuario, 0]);
                return $this->error('Usuario o contraseña incorrectos.');
            }
            if (!empty($fila['locked_until']) && strtotime((string)$fila['locked_until']) > time()) {
                return $this->error('La cuenta está temporalmente bloqueada. Intente nuevamente en 15 minutos.');
            }
            $valido = password_verify($clave, (string)($fila['password_hash'] ?? ''));
            $this->call('CALL sp_login_registrar_intento(?,?)', 'si', [$usuario, $valido ? 1 : 0]);
            if (!$valido) {
                return $this->error('Usuario o contraseña incorrectos.');
            }

            $permisos = $this->call('CALL sp_permiso_efectivo_listar(?)', 'i', [(int)$fila['id_personal']]);
            $_SESSION['usuario_id'] = (int)$fila['id_usuario'];
            $_SESSION['personal_id'] = (int)$fila['id_personal'];
            $_SESSION['usuario'] = (string)$fila['usu_nombre'];
            $_SESSION['nombre'] = (string)$fila['nombre_completo'];
            $_SESSION['permisos'] = [
                'cargo' => array_values(array_filter($permisos, static fn(array $p): bool => ($p['origen'] ?? '') === 'CARGO')),
                'individuales' => array_values(array_filter($permisos, static fn(array $p): bool => ($p['origen'] ?? '') === 'PERSONA')),
            ];
            session_regenerate_id(true);
            (new AuditoriaService($this->db))->registrar('LOGIN', 'usuario', (int)$fila['id_usuario']);
            return $this->ok('Autenticación correcta.', ['redirigir' => 'capacliente/app/dashboard.php']);
        } catch (Throwable $e) {
            error_log('AuthService::login ' . $e->getMessage());
            return $this->error('No se pudo procesar el inicio de sesión.');
        }
    }

    public function captcha(): string
    {
        SessionService::iniciar();
        $captcha = new CaptchaService($_SESSION);
        return $captcha->generar();
    }
}
