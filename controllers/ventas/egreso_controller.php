<?php
declare(strict_types=1);
require_once __DIR__.'/../core/ControllerHelper.php'; require_once __DIR__.'/../../capalogica/seguridad/PermisoService.php'; require_once __DIR__.'/../../capalogica/ventas/EgresoService.php';
$in=ControllerHelper::input(); $a=$in['accion'] ?? 'listar'; if($a!=='listar') ControllerHelper::exigirPostCsrf(); PermisoService::requiere('egresos',$a==='listar'?'ver':($a==='anular'?'anular':'crear')); $s=new EgresoService(); $r=$a==='listar'?$s->listar($in):($a==='anular'?$s->anular((int)($in['id_egreso']??0),(string)($in['motivo']??'')):$s->registrar($in)); ControllerHelper::responder($r,$r['exito']?200:422);
