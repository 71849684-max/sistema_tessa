<?php
declare(strict_types=1);

function validarPasswordInicial(string $password): ?string
{
    return strlen($password) < 12 ? 'La contraseña debe tener al menos 12 caracteres.' : null;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if (PHP_SAPI !== 'cli') { fwrite(STDERR, "Solo CLI\n"); exit(1); }
    [, $modo, $usuario, $nombres, $apellidos, $dni] = array_pad($argv, 6, '');
    $password = $modo === '--password-stdin' ? trim((string)fgets(STDIN)) : '';
    if (!preg_match('/^[a-zA-Z0-9._-]{4,80}$/', $usuario) || validarPasswordInicial($password) !== null
        || trim($nombres) === '' || trim($apellidos) === '' || !preg_match('/^\d{8}$/', $dni)) {
        fwrite(STDERR, "Uso: escriba la contraseña por STDIN: password | php scripts/inicializar_admin.php --password-stdin usuario nombres apellidos dni8\n"); exit(1);
    }
    require_once __DIR__ . '/../capaconexion/Conexion.php';
    try {
        (new Conexion())->call('CALL sp_admin_inicializar(?,?,?,?,?)', 'sssss', [$usuario,password_hash($password,PASSWORD_DEFAULT),$nombres,$apellidos,$dni]);
        fwrite(STDOUT, "Administrador inicializado.\n");
    } catch (Throwable) { fwrite(STDERR, "No se pudo inicializar el administrador.\n"); exit(1); }
}
