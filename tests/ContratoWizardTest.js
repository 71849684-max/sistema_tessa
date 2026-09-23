'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');

test('el total es editable y el neto solo cambia al aplicar descuento', () => {
    const { calcularNeto } = require('../resources/js/contratos.js');
    assert.equal(calcularNeto('5000', '0'), 5000);
    assert.equal(calcularNeto('5000', '250'), 4750);
    assert.equal(calcularNeto('6200', '250'), 5950);
});

test('el porcentaje calcula el monto del total neto', () => {
    const { montoDesdePorcentaje, porcentajeDesdeMonto } = require('../resources/js/contratos.js');
    assert.equal(montoDesdePorcentaje('1000', '20'), 200);
    assert.equal(montoDesdePorcentaje('1000', 0), 0);
    assert.equal(montoDesdePorcentaje('0', '20'), 0);
    assert.equal(montoDesdePorcentaje('333.33', '33.33'), 111.1);
    assert.equal(porcentajeDesdeMonto('1000', '200'), 20);
    assert.equal(porcentajeDesdeMonto('1500', '500'), 33.33);
    assert.equal(porcentajeDesdeMonto('0', '200'), 0);
    assert.equal(porcentajeDesdeMonto('1000', '0'), 0);
});

test('el campo % va después de Vencimiento y antes de Monto', () => {
    const fs = require('node:fs');
    const path = require('node:path');
    const fuente = fs.readFileSync(path.join(__dirname, '..', 'resources', 'js', 'contratos.js'), 'utf8');
    const vencimiento = fuente.indexOf('data-hito-fecha');
    const porcentaje = fuente.indexOf('data-hito-porcentaje');
    const monto = fuente.indexOf('data-hito-monto');
    assert.ok(vencimiento > -1, 'Debe existir el campo de vencimiento.');
    assert.ok(porcentaje > vencimiento, 'El % debe ir después del vencimiento.');
    assert.ok(monto > porcentaje, 'El % debe ir antes del monto.');
});

test('Siguiente valida solo los campos del paso actual', () => {
    const { validarCamposPaso } = require('../resources/js/contratos.js');
    let revisados = 0;
    const primero = { checkValidity() { revisados++; return true; } };
    const pasoActual = { querySelectorAll() { return [primero]; } };
    assert.equal(validarCamposPaso(pasoActual), true);
    assert.equal(revisados, 1);
    const invalido = { checkValidity() { return false; }, reportValidity() { this.reportado = true; } };
    assert.equal(validarCamposPaso({ querySelectorAll() { return [invalido]; } }), false);
    assert.equal(invalido.reportado, true);
});
