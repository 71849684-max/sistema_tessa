<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/_bootstrap.php';

paginaPrivada('Configuración', 'configuracion', static function (): void {
    $base = tessaBase();
    $editar = PermisoService::puede('configuracion', 'editar');
    $ro = $editar ? '' : ' readonly';
    ?>
    <section class="view-page" data-config-empresa data-controller="<?= escapar($base . 'controllers/core/empresa_controller.php') ?>" data-editable="<?= $editar ? '1' : '0' ?>">
        <header class="view-header panel"><div><p class="eyebrow">ADMINISTRACIÓN</p><h2>Configuración</h2><p class="muted">Datos de la empresa que complementan los contratos y su documento Word.</p></div></header>
        <section class="panel">
            <form data-empresa-form>
                <div class="form-grid">
                    <p class="eyebrow wide">EMPRESA</p>
                    <label class="wide">Razón social<input name="razon_social" maxlength="180" <?= $editar ? 'required' : '' ?> <?= $ro ?>></label>
                    <label>RUC<input name="ruc" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" <?= $ro ?>></label>
                    <label>Eslogan<input name="eslogan" maxlength="180" <?= $ro ?>></label>
                    <p class="eyebrow wide">REPRESENTANTE LEGAL</p>
                    <label class="wide">Nombre del representante<input name="representante_nombre" maxlength="180" <?= $ro ?>></label>
                    <label>Cargo<input name="representante_cargo" maxlength="80" <?= $ro ?>></label>
                    <label>DNI<input name="representante_documento" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" <?= $ro ?>></label>
                    <p class="eyebrow wide">DOMICILIO Y CONTRATO</p>
                    <label class="wide">Domicilio legal<input name="domicilio_legal" maxlength="220" <?= $ro ?>></label>
                    <label>Ciudad<input name="ciudad" maxlength="100" <?= $ro ?>></label>
                    <label class="wide">Título del contrato<input name="titulo_contrato" maxlength="255" <?= $ro ?>></label>
                    <p class="eyebrow wide">CONTACTO (PIE DEL CONTRATO)</p>
                    <label class="wide">Dirección<input name="pie_direccion" maxlength="220" <?= $ro ?>></label>
                    <label>Teléfonos<input name="pie_telefonos" maxlength="120" <?= $ro ?>></label>
                    <label>Correo<input name="pie_correo" type="email" maxlength="160" <?= $ro ?>></label>
                    <div class="wide" data-medios-pago>
                        <p class="eyebrow">MEDIOS DE PAGO OFICIALES</p>
                        <p class="muted">Cada medio aparecerá en una fila independiente dentro del contrato.</p>
                        <div data-medios-list></div>
                        <?php if ($editar): ?><button class="secondary outline" type="button" data-medio-agregar>+ Agregar medio de pago</button><?php endif; ?>
                    </div>
                </div>
                <p data-form-message role="status"></p>
                <?php if ($editar): ?><div class="dialog-actions"><button class="primary inline" type="submit">Guardar</button></div><?php endif; ?>
            </form>
        </section>
    </section>
    <script src="<?= escapar($base) ?>resources/js/configuracion.js"></script>
    <?php
});
