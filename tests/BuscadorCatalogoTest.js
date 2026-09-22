'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');

test('el buscador descarta respuestas de peticiones anteriores', () => {
    const { respuestaVigente } = require('../resources/js/componentes/buscador_catalogo.js');
    assert.equal(respuestaVigente(1, 1), true);
    assert.equal(respuestaVigente(1, 2), false);
});
const { normalizarBusqueda, opcionesVisibles } = require('../resources/js/componentes/buscador_catalogo.js');

test('la búsqueda global acepta DNI, nombres y apellidos', () => {
    assert.equal(normalizarBusqueda('  12345678  '), '12345678');
    assert.equal(normalizarBusqueda('  Ana   Paz '), 'Ana Paz');
});

test('la búsqueda de integrantes omite principal y ya agregados', () => {
    const rows = [{ id_cliente: 1 }, { id_cliente: 2 }, { id_cliente: 3 }];
    assert.deepEqual(opcionesVisibles(rows, [1, 3], row => row.id_cliente), [{ id_cliente: 2 }]);
});

test('las opciones muestran DNI y nombre completo o razón social', () => {
    const { etiquetaCliente } = require('../resources/js/contratos.js');
    assert.equal(etiquetaCliente({ documento: '12345678', nombres: 'Ana', apellidos: 'Paz' }), '12345678 · Ana Paz');
    assert.equal(etiquetaCliente({ documento: '20123456789', razon_social: 'Tessa SAC' }), '20123456789 · Tessa SAC');
});
