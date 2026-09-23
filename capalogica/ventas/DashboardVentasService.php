<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';

final class DashboardVentasService extends BaseService
{
    public function resumen(string $desde, string $hasta): array
    {
        try {
            return $this->ok('Resumen de ventas obtenido.', $this->fila($this->call('CALL sp_dashboard_ventas_resumen(?,?)', 'ss', [$desde, $hasta])));
        } catch (Throwable $e) {
            error_log('DashboardVentasService::resumen ' . $e->getMessage());
            return $this->error('No se pudo obtener el resumen de ventas.');
        }
    }

    public function datos(string $desde, string $hasta): array
    {
        $resumen = $this->resumen($desde, $hasta);
        if (!$resumen['exito']) {
            return $resumen;
        }
        try {
            $pendientes = $this->call('CALL sp_dashboard_ventas_pendientes(?,?)', 'ss', [$desde, $hasta]);
            return $this->ok('Dashboard de ventas obtenido.', [
                'resumen' => $resumen['datos'],
                'evolucion' => $this->call('CALL sp_dashboard_ventas_evolucion(?,?)', 'ss', [$desde, $hasta]),
                'embudo' => $this->call('CALL sp_dashboard_ventas_embudo(?,?)', 'ss', [$desde, $hasta]),
                'servicios' => $this->call('CALL sp_dashboard_ventas_servicios(?,?)', 'ss', [$desde, $hasta]),
                'pendientes' => $pendientes,
                'alertas' => $pendientes,
            ]);
        } catch (Throwable $e) {
            error_log('DashboardVentasService::datos ' . $e->getMessage());
            return $this->error('No se pudo obtener el dashboard de ventas.');
        }
    }
}
