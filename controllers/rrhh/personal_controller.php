<?php
declare(strict_types=1);
require_once __DIR__.'/../core/ControllerHelper.php'; require_once __DIR__.'/../../capalogica/seguridad/PermisoService.php'; require_once __DIR__.'/../../capalogica/rrhh/PersonalService.php';
$in=ControllerHelper::input(); $a=$in['accion']??'listar'; if(!in_array($a,['listar','cargos'],true)) ControllerHelper::exigirPostCsrf(); PermisoService::requiere('personal',in_array($a,['listar','cargos'],true)?'ver':((int)($in['id_personal']??0)>0?'editar':'crear')); $s=new PersonalService(); $r=match($a){'listar'=>$s->listar(),'cargos'=>$s->cargos(),default=>$s->guardar($in)}; ControllerHelper::responder($r,$r['exito']?200:422);
