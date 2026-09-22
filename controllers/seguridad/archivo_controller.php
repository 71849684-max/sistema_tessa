<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/seguridad/ArchivoService.php';

PermisoService::requiere('cobranzas','ver');
$id=(int)($_GET['id_voucher'] ?? 0);
try {
    $fila=(new ArchivoService())->voucher($id);
    $ruta=dirname(__DIR__,2).'/storage/vouchers/'.basename((string)($fila['nombre_interno'] ?? ''));
    if ($fila === [] || !is_file($ruta)) ControllerHelper::responder(RespuestaHelper::error('Archivo no encontrado.'),404);
    header('Content-Type: '.(string)$fila['mime']);
    header('Content-Length: '.filesize($ruta));
    header('Content-Disposition: inline; filename="voucher-'.$id.'"');
    header('X-Content-Type-Options: nosniff');
    readfile($ruta); exit;
} catch (Throwable) { ControllerHelper::responder(RespuestaHelper::error('No se pudo obtener el archivo.'),500); }
