'use strict';
const assert = require('node:assert/strict');
const { requisitosMedio, opcionesPagador, etiquetaContrato } = require('../resources/js/cobranzas.js');
assert.deepEqual(requisitosMedio('EFECTIVO'), { requiereOperacion: false, requiereVoucher: false });
assert.deepEqual(requisitosMedio('YAPE'), { requiereOperacion: true, requiereVoucher: true });
assert.equal(opcionesPagador('INDIVIDUAL', [{ id_cliente: 1 }]).length, 0);
assert.equal(opcionesPagador('GRUPAL', [{ id_cliente: 1 }, { id_cliente: 2 }]).length, 2);
assert.equal(etiquetaContrato({ con_numero: 'TES-1', cliente_nombre: 'Ana Pérez', documento: '12345678', saldo: 500 }), 'TES-1 · Ana Pérez · DNI 12345678 · saldo S/ 500.00');
console.log('CobranzasFormularioTest OK');
