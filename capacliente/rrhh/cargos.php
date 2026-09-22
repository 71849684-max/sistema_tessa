<?php
declare(strict_types=1);
require_once __DIR__.'/../app/_modulo.php';
moduloCrud('Cargos','cargos','controllers/rrhh/cargo_controller.php',['id_cargo'=>'ID','nombre'=>'Cargo','estado'=>'Estado'],'<input type="hidden" name="accion" value="guardar"><input type="hidden" name="id_cargo" value="0"><div class="form-grid"><label class="wide">Nombre del cargo<input name="nombre" required></label><label>Estado<select name="estado"><option value="1">Activo</option><option value="0">Inactivo</option></select></label></div>','Plantillas iniciales de permisos para el personal.', ['id'=>'id_cargo','grupo'=>'ADMINISTRACIÓN']); ?>
