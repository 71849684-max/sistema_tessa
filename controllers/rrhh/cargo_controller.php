<?php
declare(strict_types=1);
require_once __DIR__.'/../core/ControllerHelper.php'; require_once __DIR__.'/../../capalogica/seguridad/PermisoService.php'; require_once __DIR__.'/../../capalogica/rrhh/CargoService.php';
$in=ControllerHelper::input(); $a=$in['accion']??'listar'; if($a!=='listar') ControllerHelper::exigirPostCsrf(); PermisoService::requiere('cargos',$a==='listar'?'ver':((int)($in['id_cargo']??0)>0?'editar':'crear')); $s=new CargoService(); $r=$a==='listar'?$s->listar():$s->guardar($in); ControllerHelper::responder($r,$r['exito']?200:422);
