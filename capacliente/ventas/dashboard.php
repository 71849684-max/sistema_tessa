<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/_bootstrap.php';

paginaPrivada('Dashboard de ventas', 'dashboard', static function (): void {
    $base = tessaBase();
    ?>
    <section class="sales-dashboard" data-sales-dashboard data-controller="<?= escapar($base) ?>controllers/ventas/dashboard_ventas_controller.php">
        <div class="dashboard-head">
            <div>
                <p class="eyebrow">VENTAS</p>
                <h2>Control de ingresos y cobranza</h2>
                <p class="muted">Consulta montos cobrados, saldos pendientes y próximas cuotas.</p>
            </div>
            <form class="dashboard-filters" data-dashboard-filters>
                <label>Desde<input type="date" name="desde" value="<?= date('Y-m-01') ?>"></label>
                <label>Hasta<input type="date" name="hasta" value="<?= date('Y-m-d') ?>"></label>
                <button class="primary inline" type="submit">Actualizar</button>
            </form>
        </div>

        <div class="kpi-grid">
            <article class="kpi-card accent-green"><span>Cobrado</span><strong data-kpi="cobrado">S/ 0.00</strong><small>en el período</small></article>
            <article class="kpi-card accent-orange"><span>Por cobrar</span><strong data-kpi="por_cobrar">S/ 0.00</strong><small>cuotas pendientes</small></article>
            <article class="kpi-card accent-blue"><span>Efectividad</span><strong data-kpi="efectividad">0%</strong><div class="progress"><i data-kpi-progress></i></div></article>
            <article class="kpi-card accent-purple"><span>Contratos activos</span><strong data-kpi="contratos_activos">0</strong><small>en seguimiento</small></article>
        </div>

        <div class="dashboard-grid">
            <article class="panel chart-panel"><div class="panel-heading"><div><p class="eyebrow">COBRANZA</p><h3>Evolución del período</h3></div><strong data-evolucion-total>S/ 0.00</strong></div><div class="bar-chart" data-evolucion></div><p class="empty-state" data-evolucion-empty>Aún no hay cobranzas en el rango seleccionado.</p></article>
            <article class="panel"><div class="panel-heading"><div><p class="eyebrow">CUOTAS</p><h3>Embudo de cobranza</h3></div></div><div class="funnel" data-embudo></div><p class="empty-state" data-embudo-empty>No hay cuotas para este período.</p></article>
            <article class="panel chart-panel"><div class="panel-heading"><div><p class="eyebrow">SERVICIOS</p><h3>Contratado vs. cobrado</h3></div></div><div class="service-bars" data-servicios></div><p class="empty-state" data-servicios-empty>No hay contratos en este período.</p></article>
            <article class="panel"><div class="panel-heading"><div><p class="eyebrow">PRIORIDAD</p><h3>Cuotas pendientes</h3></div><a class="text-link" href="<?= escapar($base) ?>capacliente/ventas/cobranzas.php">Gestionar</a></div><div class="pending-list" data-pendientes></div><p class="empty-state" data-pendientes-empty>No hay cuotas pendientes en el rango.</p></article>
        </div>
    </section>
    <script src="<?= escapar($base) ?>resources/js/dashboard-ventas.js"></script>
    <?php
});
