<?php
declare(strict_types=1);
require_once __DIR__.'/../app/_modulo.php';
moduloCrud('Auditoría','auditoria','controllers/rrhh/auditoria_controller.php',['created_at'=>'Fecha','usu_nombre'=>'Usuario','accion'=>'Acción','entidad'=>'Entidad','id_registro'=>'Registro','ip'=>'IP'],'','Historial de acciones sensibles del sistema.', ['soloLectura'=>true,'grupo'=>'ADMINISTRACIÓN','filtros'=>'<form data-filter-form class="filter-grid"><label>Desde<input name="fecha_inicio" type="date"></label><label>Hasta<input name="fecha_fin" type="date"></label><label>Buscar<input name="buscar" placeholder="Acción o entidad"></label><button class="secondary" type="submit">Filtrar</button><button class="secondary outline" type="button" data-filter-clear>Limpiar</button></form>']); ?>
