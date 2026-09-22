<?php
declare(strict_types=1);
require_once __DIR__.'/../core/ControllerHelper.php'; require_once __DIR__.'/../../capalogica/seguridad/PermisoService.php'; require_once __DIR__.'/../../capalogica/ventas/ClienteService.php';
$in=ControllerHelper::input(); $accion=$in['accion'] ?? 'listar'; if($accion!=='listar') ControllerHelper::exigirPostCsrf(); PermisoService::requiere('clientes',$accion==='listar'?'ver':($accion==='anular'?'anular':((int)($in['id_cliente']??0)>0?'editar':'crear'))); $s=new ClienteService(); $r=$accion==='listar'?$s->listar():$s->guardar($in); ControllerHelper::responder($r,$r['exito']?200:422);
