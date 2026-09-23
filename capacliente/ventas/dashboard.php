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
                <h2>Control de ventas y cobranza</h2>
                <p class="muted">Resumen de ventas, montos cobrados, cuotas pendientes y cumplimiento de meta.</p>
            </div>
            <form class="dashboard-filters" data-dashboard-filters>
                <label class="dashboard-period">Período<select data-dashboard-period><option value="custom">Personalizado</option><option value="month">Este mes</option><option value="quarter">Este trimestre</option><option value="year">Este año</option></select></label>
                <label>Desde<input type="date" name="desde" value="<?= date('Y-m-01') ?>"></label>
                <label>Hasta<input type="date" name="hasta" value="<?= date('Y-m-d') ?>"></label>
                <button class="primary inline" type="submit">Actualizar</button>
            </form>
        </div>

        <div class="kpi-grid dashboard-kpis">
            <article class="kpi-card accent-green"><svg class="kpi-icon" viewBox="0 0 24 24" aria-hidden="true"><ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v8c0 1.7 3.1 3 7 3s7-1.3 7-3V6M5 10c0 1.7 3.1 3 7 3s7-1.3 7-3"/></svg><div><span>Cobrado</span><strong data-kpi="cobrado">S/ 0.00</strong><small>ingresos del período</small></div></article>
            <article class="kpi-card accent-orange"><svg class="kpi-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V10m6 9V5m6 14v-8m4 8H2"/></svg><div><span>Proyectado</span><strong data-kpi="proyectado">S/ 0.00</strong><small data-cumplimiento>meta del período</small></div></article>
            <article class="kpi-card accent-red"><svg class="kpi-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M6 6l1 14h10l1-14M9 10v6m6-6v6M8 3h8l1 3H7l1-3"/></svg><div><span>Egresos</span><strong data-kpi="egresos">S/ 0.00</strong><small>salidas del período</small></div></article>
            <article class="kpi-card accent-blue"><svg class="kpi-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 18 9 13l3 3 8-9M15 7h5v5"/></svg><div><span>Flujo neto</span><strong data-kpi="neto">S/ 0.00</strong><small>cobrado menos egresos</small></div></article>
            <article class="kpi-card accent-purple"><svg class="kpi-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h10l4 4v14H5zM15 3v5h5M8 13h8m-8 4h8"/></svg><div><span>Por cobrar</span><strong data-kpi-saldo>S/ 0.00</strong><small>saldo de cuotas activas</small></div></article>
        </div>

        <div class="dashboard-grid dashboard-layout dashboard-reference-layout">
            <article class="panel dashboard-trend">
                <div class="panel-heading">
                    <div><p class="eyebrow">FLUJO DE CAJA</p><h3>Cobrado, egresos y neto</h3></div>
                    <strong class="panel-total" data-flujo-total></strong>
                </div>
                <p class="chart-legend"><i></i> Cobrado <i class="expense"></i> Egresos <i class="net"></i> Neto</p>
                <div class="chart-frame">
                    <div class="cashflow-bars" data-flujo-caja aria-label="Resumen de flujo de caja del período"></div>
                </div>
            </article>
            <aside class="dashboard-side">
                <article class="panel dashboard-state dashboard-quota-health">
                    <div class="panel-heading"><div><h3>Estado de cuotas</h3></div><span class="quota-total" data-cuotas-total></span></div>
                    <div class="quota-stack" data-embudo-stack aria-label="Distribución de estados de cuota"></div>
                    <div class="quota-breakdown" data-embudo></div>
                    <p class="empty-state" data-embudo-empty>No hay cuotas para este período.</p>
                </article>
                <article class="panel dashboard-alerts">
                    <div class="panel-heading"><div><h3>Alertas prioritarias</h3></div><a class="text-link" href="<?= escapar($base) ?>capacliente/ventas/cobranzas.php">Ver todas</a></div>
                    <ul class="pending-list" data-alertas></ul>
                    <p class="empty-state" data-alertas-empty>No hay alertas para este período.</p>
                </article>
            </aside>
            <article class="panel dashboard-services">
                <div class="panel-heading"><div><h3>Ventas por servicio</h3></div><p class="chart-legend"><i class="contracted"></i> Ventas <i class="projected"></i> Meta</p></div>
                <div class="service-bars" data-servicios></div>
                <p class="empty-state" data-servicios-empty>No hay contratos en este período.</p>
            </article>
            <article class="panel dashboard-monthly">
                <div class="panel-heading"><div><h3>Cobranza por mes</h3></div><p class="chart-legend"><i></i> Cobrado <i class="pending"></i> Pendiente</p></div>
                <div class="monthly-bars" data-cobranza-mensual></div>
                <p class="empty-state" data-mensual-empty>No hay movimientos para este período.</p>
            </article>
        </div>
    </section>
    <script src="<?= escapar($base) ?>resources/js/dashboard-ventas.js"></script>
    <?php
});
