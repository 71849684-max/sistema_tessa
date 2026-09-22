<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/RespuestaHelper.php';

final class VoucherService
{
    public function __construct(private string $directorio)
    {
    }

    public function guardar(array $archivo): array
    {
        if ((int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return RespuestaHelper::error('Debe adjuntar un voucher válido.', ['voucher' => 'Error de carga.']);
        }
        if ((int)($archivo['size'] ?? 0) < 1 || (int)$archivo['size'] > 5 * 1024 * 1024) {
            return RespuestaHelper::error('El voucher supera el tamaño permitido.', ['voucher' => 'Máximo 5 MiB.']);
        }
        $temporal = (string)($archivo['tmp_name'] ?? '');
        if (!is_file($temporal)) {
            return RespuestaHelper::error('Archivo temporal inválido.', ['voucher' => 'No encontrado.']);
        }

        $cabecera = file_get_contents($temporal, false, null, 0, 12) ?: '';
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($temporal);
        $valido = ($mime === 'image/jpeg' && str_starts_with($cabecera, "\xFF\xD8\xFF"))
            || ($mime === 'image/png' && str_starts_with($cabecera, "\x89PNG\r\n\x1A\n"))
            || ($mime === 'application/pdf' && str_starts_with($cabecera, '%PDF-'));
        if (!$valido) {
            return RespuestaHelper::error('Formato de voucher no permitido.', ['voucher' => 'Solo PDF, PNG o JPEG válidos.']);
        }

        if (!is_dir($this->directorio) && !mkdir($this->directorio, 0750, true) && !is_dir($this->directorio)) {
            return RespuestaHelper::error('No se pudo preparar el almacenamiento.');
        }
        $extension = $mime === 'application/pdf' ? 'pdf' : ($mime === 'image/png' ? 'png' : 'jpg');
        $nombre = bin2hex(random_bytes(20)) . '.' . $extension;
        $destino = rtrim($this->directorio, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombre;
        $movido = is_uploaded_file($temporal) ? move_uploaded_file($temporal, $destino) : rename($temporal, $destino);
        if (!$movido) {
            return RespuestaHelper::error('No se pudo guardar el voucher.');
        }
        chmod($destino, 0640);
        return RespuestaHelper::ok('Voucher validado.', [
            'nombre_interno' => $nombre,
            'mime' => $mime,
            'tamano' => filesize($destino),
            'hash' => hash_file('sha256', $destino),
        ]);
    }

    public function eliminar(string $nombreInterno): void
    {
        $nombre = basename($nombreInterno);
        $ruta = rtrim($this->directorio, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombre;
        if ($nombre !== '' && is_file($ruta)) {
            unlink($ruta);
        }
    }
}
