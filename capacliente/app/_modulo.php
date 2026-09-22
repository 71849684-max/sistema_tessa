<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

/** @param array<string,string> $columnas @param array<string,mixed> $opciones */
function moduloCrud(string $titulo, string $seccion, string $controlador, array $columnas, string $campos, string $ayuda = '', array $opciones = []): void
{
    paginaPrivada($titulo, $seccion, static function () use ($titulo, $seccion, $controlador, $columnas, $campos, $ayuda, $opciones): void {
        $base = tessaBase();
        $permisos = PermisoService::efectivo();
        $crear = empty($opciones['soloLectura']) && $permisos->autorizar($seccion, 'crear');
        $editar = !empty($opciones['id']) && empty($opciones['sinEdicion']) && $permisos->autorizar($seccion, 'editar');
        $anular = !empty($opciones['anulacion']) && $permisos->autorizar($seccion, 'anular');
        $acciones = $editar || $anular;
        $config = ['id' => (string)($opciones['id'] ?? ''), 'crear' => $crear, 'editar' => $editar, 'anular' => $anular, 'cards' => !empty($opciones['cards']), 'metrics' => $opciones['metricas'] ?? []];
        ?>
        <section class="view-page" data-crud data-controller="<?= escapar($base . $controlador) ?>" data-columns="<?= escapar((string)json_encode($columnas, JSON_UNESCAPED_UNICODE)) ?>" data-config="<?= escapar((string)json_encode($config, JSON_UNESCAPED_UNICODE)) ?>">
            <header class="view-header panel"><div><p class="eyebrow"><?= escapar((string)($opciones['grupo'] ?? 'GESTIÓN')) ?></p><h2><?= escapar($titulo) ?></h2><p class="muted"><?= escapar($ayuda) ?></p></div><div class="view-actions"><button class="secondary outline" type="button" data-reload aria-label="Actualizar registros">↻ Actualizar</button><?php if ($crear): ?><button class="primary inline" type="button" data-dialog-open="crud-editor">+ Nuevo registro</button><?php endif; ?></div></header>
            <?php if (!empty($opciones['metricas'])): ?><div class="kpi-grid module-kpis"><?php foreach ($opciones['metricas'] as $index => $metrica): ?><article class="kpi-card <?= escapar((string)($metrica['color'] ?? 'accent-green')) ?>"><span><?= escapar((string)$metrica['label']) ?></span><strong data-metric="<?= (int)$index ?>"><?= !empty($metrica['currency']) ? 'S/ 0.00' : '0' ?></strong><small><?= escapar((string)($metrica['help'] ?? 'según filtros')) ?></small></article><?php endforeach; ?></div><?php endif; ?>
            <?php if (!empty($opciones['filtros'])): ?><section class="panel filter-panel"><div class="panel-heading"><div><p class="eyebrow">CONSULTA</p><h3>Filtros</h3></div></div><?= $opciones['filtros'] ?></section><?php endif; ?>
            <section class="panel listing-panel" data-table-host><div class="panel-heading"><div><p class="eyebrow">LISTADO</p><h3>Registros</h3></div><span class="list-count" data-list-count>0 registros</span></div><p class="error" data-list-message role="status" hidden></p>
                <?php if (!empty($opciones['cards'])): ?><div class="person-cards" data-card-list></div><div class="paginador-controls" data-card-pagination></div><?php else: ?><div class="table-scroll"><table id="tabla-<?= escapar($seccion) ?>"><thead><tr><?php foreach ($columnas as $etiqueta): ?><th><?= escapar($etiqueta) ?></th><?php endforeach; ?><?php if ($acciones): ?><th data-no-sort data-no-export>Acciones</th><?php endif; ?></tr></thead><tbody data-crud-body></tbody></table></div><?php endif; ?>
            </section>
            <?php if (!empty($opciones['complemento']) && (empty($opciones['complementoPermiso']) || $permisos->autorizar($seccion, (string)$opciones['complementoPermiso']))): ?><section class="view-complement"><?= $opciones['complemento'] ?></section><?php endif; ?>
            <?php if ($crear || $editar): ?><dialog class="app-dialog" id="crud-editor" aria-labelledby="crud-editor-title"><div class="dialog-shell"><div class="dialog-head"><div><p class="eyebrow">REGISTRO</p><h3 id="crud-editor-title" data-editor-title><?= escapar((string)($opciones['formularioTitulo'] ?? 'Nuevo registro')) ?></h3></div><button type="button" class="dialog-close" data-dialog-close aria-label="Cerrar">×</button></div><form data-crud-form enctype="multipart/form-data"><div class="dialog-content"><p class="muted"><?= escapar($ayuda) ?></p><?= $campos ?><p data-form-message role="status"></p></div><div class="dialog-actions"><button type="button" class="secondary outline" data-dialog-close>Cancelar</button><button class="primary inline" type="submit">Guardar</button></div></form></div></dialog><?php endif; ?>
            <?php if ($anular): ?><dialog class="app-dialog app-dialog-small" id="crud-void" aria-labelledby="crud-void-title"><div class="dialog-shell"><div class="dialog-head"><div><p class="eyebrow">ACCIÓN SENSIBLE</p><h3 id="crud-void-title">Anular registro</h3></div><button type="button" class="dialog-close" data-dialog-close aria-label="Cerrar">×</button></div><form data-void-form><div class="dialog-content"><input type="hidden" name="accion" value="anular"><input type="hidden" name="<?= escapar((string)$opciones['id']) ?>" data-void-id><p data-void-target class="muted"></p><label>Motivo de anulación<textarea name="motivo" required minlength="5"></textarea></label><p data-void-message role="status"></p></div><div class="dialog-actions"><button type="button" class="secondary outline" data-dialog-close>Cancelar</button><button type="submit" class="danger">Confirmar anulación</button></div></form></div></dialog><?php endif; ?>
        </section>
        <script src="<?= escapar($base) ?>resources/js/componentes/paginacion_tablas.js"></script><script src="<?= escapar($base) ?>resources/js/componentes/paginador_cards.js"></script>
        <?php foreach (($opciones['scripts'] ?? []) as $script): ?><script src="<?= escapar($base . $script) ?>"></script><?php endforeach; ?>
        <script src="<?= escapar($base) ?>resources/js/crud.js"></script>
        <?php
    });
}
