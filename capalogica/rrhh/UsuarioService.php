<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../seguridad/AuditoriaService.php';

final class UsuarioService extends BaseService
{
    public function listar(): array
    {
        try {
            $personas = $this->call('CALL sp_personal_listar()');
            return $this->ok('Usuarios obtenidos.', array_values(array_filter($personas, static fn(array $persona): bool => (int)($persona['id_usuario'] ?? 0) > 0)));
        } catch (Throwable $e) {
            error_log('UsuarioService listar: ' . $e->getMessage());
            return $this->error('No se pudieron listar los usuarios.');
        }
    }

    public function personas(): array
    {
        try { return $this->ok('Personal obtenido.', $this->call('CALL sp_personal_listar()')); }
        catch (Throwable $e) { error_log('UsuarioService personas: ' . $e->getMessage()); return $this->error('No se pudo consultar el personal.'); }
    }

    public function guardar(array $datos): array
    {
        $id = (int)($datos['id_usuario'] ?? 0);
        $personal = (int)($datos['id_personal'] ?? 0);
        $nombre = trim((string)($datos['usuario'] ?? $datos['usu_nombre'] ?? ''));
        $clave = (string)($datos['clave'] ?? '');
        if ($personal < 1 || !preg_match('/^[a-zA-Z0-9._-]{4,80}$/', $nombre)) return $this->error('Usuario inválido.', ['usuario' => 'Use entre 4 y 80 caracteres válidos.']);
        if ($id === 0 && strlen($clave) < 12) return $this->error('Contraseña insegura.', ['clave' => 'La contraseña inicial debe tener al menos 12 caracteres.']);
        if ($id > 0 && $clave !== '' && strlen($clave) < 12) return $this->error('Contraseña insegura.', ['clave' => 'La contraseña debe tener al menos 12 caracteres.']);
        try {
            $hash = $clave === '' ? '' : password_hash($clave, PASSWORD_DEFAULT);
            $fila = $this->fila($this->call('CALL sp_usuario_guardar(?,?,?,?,?)', 'iissi', [$id,$personal,$nombre,$hash,(int)($datos['estado'] ?? 1)]));
            (new AuditoriaService($this->db))->registrar('GUARDAR','usuario',(int)($fila['id_usuario'] ?? 0),[],['usuario'=>$nombre]);
            return $this->ok((string)($fila['mensaje'] ?? 'Usuario guardado.'),$fila);
        } catch (Throwable $e) { return $this->error('No se pudo guardar usuario.'); }
    }

    public function guardarPermiso(array $datos): array
    {
        $personal=(int)($datos['id_personal'] ?? 0); $permiso=(int)($datos['id_permiso'] ?? 0); $permitido=(int)($datos['permitido'] ?? -1);
        if ($personal<1 || $permiso<1 || !in_array($permitido,[0,1],true)) return $this->error('Permiso inválido.', ['permiso'=>'Datos requeridos.']);
        try {
            $fila=$this->fila($this->call('CALL sp_persona_permiso_guardar(?,?,?)','iii',[$personal,$permiso,$permitido]));
            (new AuditoriaService($this->db))->registrar('PERMISO','persona',$personal,[],['id_permiso'=>$permiso,'permitido'=>$permitido]);
            return $this->ok((string)($fila['mensaje'] ?? 'Permiso guardado.'),$fila);
        } catch (Throwable $e) { return $this->error('No se pudo guardar permiso.'); }
    }

    public function quitarPermiso(array $datos): array
    {
        $personal = (int)($datos['id_personal'] ?? 0);
        $permiso = (int)($datos['id_permiso'] ?? 0);
        if ($personal < 1 || $permiso < 1) return $this->error('Permiso inválido.', ['permiso' => 'Persona y permiso requeridos.']);
        try {
            $fila = $this->fila($this->call('CALL sp_persona_permiso_quitar(?,?)', 'ii', [$personal, $permiso]));
            (new AuditoriaService($this->db))->registrar('PERMISO_HEREDAR', 'persona', $personal, [], ['id_permiso' => $permiso]);
            return $this->ok((string)($fila['mensaje'] ?? 'Excepción retirada.'), $fila);
        } catch (Throwable $e) { error_log('UsuarioService quitarPermiso: ' . $e->getMessage()); return $this->error('No se pudo retirar la excepción.'); }
    }

    public function guardarPermisoCargo(array $datos): array
    {
        $cargo=(int)($datos['id_cargo'] ?? 0); $permiso=(int)($datos['id_permiso'] ?? 0); $permitido=(int)($datos['permitido'] ?? -1);
        if ($cargo<1 || $permiso<1 || !in_array($permitido,[0,1],true)) return $this->error('Permiso inválido.', ['permiso'=>'Datos requeridos.']);
        try {
            $fila=$this->fila($this->call('CALL sp_cargo_permiso_guardar(?,?,?)','iii',[$cargo,$permiso,$permitido]));
            (new AuditoriaService($this->db))->registrar('PERMISO','cargo',$cargo,[],['id_permiso'=>$permiso,'permitido'=>$permitido]);
            return $this->ok((string)($fila['mensaje'] ?? 'Permiso de cargo guardado.'),$fila);
        } catch (Throwable $e) { return $this->error('No se pudo guardar permiso de cargo.'); }
    }
}
