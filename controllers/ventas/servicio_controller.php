<?php
declare(strict_types=1);
require_once __DIR__.'/../core/ControllerHelper.php'; require_once __DIR__.'/../../capalogica/seguridad/PermisoService.php'; require_once __DIR__.'/../../capalogica/ventas/ServicioService.php';
$in=ControllerHelper::input(); $accion=$in['accion'] ?? 'listar'; if($accion!=='listar') ControllerHelper::exigirPostCsrf(); PermisoService::requiere('servicios',$accion==='listar'?'ver':((int)($in['id_servicio']??0)>0?'editar':'crear')); $s=new ServicioService(); $r=$accion==='listar'?$s->listar():$s->guardar($in); ControllerHelper::responder($r,$r['exito']?200:422);
