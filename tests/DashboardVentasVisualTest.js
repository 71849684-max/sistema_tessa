'use strict';
const assert = require('node:assert/strict');
const { puntosLinea, clasificarAlertas, acumularSerie, puntosComparables, reducirSerieVisual, agruparPorMes } = require('../resources/js/dashboard-ventas.js');
const puntos = puntosLinea([{ cobrado: 0 }, { cobrado: 100 }, { cobrado: 0 }], 'cobrado', 300, 100);
assert.equal(puntos.length, 3);
assert.ok(puntos.every(p => Number.isFinite(p.x) && Number.isFinite(p.y)));
assert.equal(clasificarAlertas([{ estado: 'PENDIENTE' }, { estado: 'VENCIDO' }])[0].estado, 'VENCIDO');
const acumulada = acumularSerie([{ fecha: '2026-09-01', proyectado: 100, cobrado: 50 }, { fecha: '2026-09-02', proyectado: 100, cobrado: 20 }]);
assert.deepEqual(acumulada.map(fila => [fila.proyectado, fila.cobrado]), [[100, 50], [200, 70]]);
const comparables = puntosComparables(acumulada, ['proyectado', 'cobrado'], 300, 100);
assert.equal(comparables.proyectado[1].y, 0, 'Las series deben compartir la misma escala vertical.');
assert.ok(comparables.cobrado[1].y > comparables.proyectado[1].y, 'Un cobro acumulado menor debe dibujarse debajo de la proyección.');
const visible = reducirSerieVisual([
    { fecha: '2026-09-01', proyectado: 0, cobrado: 0 },
    { fecha: '2026-09-02', proyectado: 0, cobrado: 0 },
    { fecha: '2026-09-22', proyectado: 1500, cobrado: 0 },
    { fecha: '2026-09-23', proyectado: 0, cobrado: 1000 }
]);
assert.deepEqual(visible.map(fila => fila.fecha), ['2026-09-01', '2026-09-22', '2026-09-23'], 'La gráfica debe omitir días intermedios sin actividad, preservando el inicio y los movimientos.');
assert.deepEqual(
    agruparPorMes([{ fecha: '2026-09-22', cobrado: 100, proyectado: 200 }, { fecha: '2026-09-23', cobrado: 50, proyectado: 0 }]),
    [{ etiqueta: 'Sep', cobrado: 150, proyectado: 200, pendiente: 50 }],
    'La cobranza mensual debe mostrar solo el saldo pendiente después de restar lo cobrado en el mismo mes.'
);
console.log('DashboardVentasVisualTest OK');
