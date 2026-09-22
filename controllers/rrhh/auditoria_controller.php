<?php
declare(strict_types=1);
require_once __DIR__.'/../core/ControllerHelper.php'; require_once __DIR__.'/../../capalogica/seguridad/PermisoService.php'; require_once __DIR__.'/../../capalogica/seguridad/AuditoriaService.php';
PermisoService::requiere('auditoria','ver'); $r=(new AuditoriaService())->listar(ControllerHelper::input()); ControllerHelper::responder($r,$r['exito']?200:422);

