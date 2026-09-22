'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');

test('una respuesta RENIEC antigua no rellena otro DNI ni otro formulario', () => {
    const { consultaVigente } = require('../resources/js/clientes.js');
    const solicitud = { dni: '12345678', tipo: 'NATURAL', version: 2 };
    assert.equal(consultaVigente(solicitud, { dni: '12345678', tipo: 'NATURAL', version: 2 }), true);
    assert.equal(consultaVigente(solicitud, { dni: '87654321', tipo: 'NATURAL', version: 2 }), false);
    assert.equal(consultaVigente(solicitud, { dni: '12345678', tipo: 'JURIDICA', version: 2 }), false);
    assert.equal(consultaVigente(solicitud, { dni: '12345678', tipo: 'NATURAL', version: 3 }), false);
});

test('persona natural muestra nombres y apellidos; empresa muestra razón social', () => {
    const { camposPorTipo } = require('../resources/js/clientes.js');
    assert.deepEqual(camposPorTipo('NATURAL'), { natural: true, juridica: false, documento: 8 });
    assert.deepEqual(camposPorTipo('JURIDICA'), { natural: false, juridica: true, documento: 11 });
});

test('el cliente RENIEC solo normaliza DNI de ocho dígitos', () => {
    const { normalizarDni } = require('../resources/js/reniec.js');
    assert.equal(normalizarDni('12 345 678'), '12345678');
    assert.equal(normalizarDni('123'), '123');
});
