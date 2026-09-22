'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');

test('CSV neutraliza entradas que una hoja de cálculo interpretaría como fórmulas', () => {
    const { csvCell } = require('../resources/js/componentes/paginacion_tablas.js');
    assert.equal(csvCell('=HYPERLINK("https://ejemplo.test")'), "'=" + 'HYPERLINK("https://ejemplo.test")');
    assert.equal(csvCell('  @SUMA(1;2)'), "'  @SUMA(1;2)");
    assert.equal(csvCell('Cliente común'), 'Cliente común');
});

test('las tarjetas paginan datos sin saltar ni duplicar registros', () => {
    const { paginaDatos } = require('../resources/js/componentes/paginador_cards.js');
    assert.deepEqual(paginaDatos([1, 2, 3, 4, 5], 2, 2), [3, 4]);
    assert.deepEqual(paginaDatos([1, 2, 3, 4, 5], 3, 2), [5]);
});

test('el listado representa estados y nombres derivados de los registros reales', () => {
    const { fieldValue } = require('../resources/js/crud.js');
    assert.equal(fieldValue({ tipo: 'JURIDICA', razon_social: 'Tessa S.A.C.' }, 'nombre'), 'Tessa S.A.C.');
    assert.equal(fieldValue({ nombres: 'Ana', apellidos: 'Paz' }, 'nombre'), 'Ana Paz');
    assert.equal(fieldValue({ usu_estado: 0 }, 'usu_estado'), 'Inactivo');
});

test('los indicadores no suman movimientos anulados', () => {
    const { metricValue } = require('../resources/js/crud.js');
    const rows = [{ monto: '100.00', estado: 'REGISTRADO' }, { monto: '40.00', estado: 'ANULADO' }];
    assert.equal(metricValue(rows, { type: 'sum', field: 'monto', state: 'REGISTRADO' }), 100);
    assert.equal(metricValue(rows, { type: 'count', state: 'ANULADO' }), 1);
});

test('las métricas de personal distinguen personas con y sin usuario', () => {
    const { metricValue } = require('../resources/js/crud.js');
    const rows = [{ id_usuario: 2 }, { id_usuario: null }, { id_usuario: 3 }];
    assert.equal(metricValue(rows, { type: 'count', hasField: 'id_usuario' }), 2);
    assert.equal(metricValue(rows, { type: 'count', missingField: 'id_usuario' }), 1);
});

test('la matriz de permisos muestra solo acciones aplicables a cada vista', () => {
    const { accionesDisponibles } = require('../resources/js/permisos.js');
    assert.deepEqual(accionesDisponibles('dashboard'), ['ver']);
    assert.deepEqual(accionesDisponibles('contratos'), ['ver', 'crear', 'anular']);
    assert.deepEqual(accionesDisponibles('usuarios'), ['ver', 'crear', 'editar', 'administrar']);
});

test('las vistas de permisos se agrupan como el menú de navegación', () => {
    const { areaModulo, ordenarPermisos } = require('../resources/js/permisos.js');
    assert.equal(areaModulo('contratos'), 'Ventas');
    assert.equal(areaModulo('cargos'), 'Administración');
    assert.deepEqual(ordenarPermisos([{ modulo: 'cargos' }, { modulo: 'clientes' }, { modulo: 'dashboard' }]).map(row => row.modulo), ['dashboard', 'clientes', 'cargos']);
});

test('una respuesta de permisos solo se aplica al sujeto y versión solicitados', () => {
    const { respuestaVigente } = require('../resources/js/permisos.js');
    assert.equal(respuestaVigente('3', '3', 2, 2), true);
    assert.equal(respuestaVigente('3', '4', 2, 2), false);
    assert.equal(respuestaVigente('3', '3', 2, 3), false);
});

test('el permiso visible se revierte cuando falla su guardado', () => {
    const { valorTrasGuardado } = require('../resources/js/permisos.js');
    assert.equal(valorTrasGuardado('0', '1', false), '0');
    assert.equal(valorTrasGuardado('0', '1', true), '1');
});
