'use strict';

const { normalizarMediosPago } = require('../resources/js/configuracion.js');

const medios = normalizarMediosPago('[{"medio":"BCP - Soles","titular":"Titular oficial","numero":"3554 0210 8910 98","cci":"00235514021089109862"}]');

if (medios.length !== 1 || medios[0].numero !== '35540210891098' || medios[0].medio !== 'BCP - Soles') {
    throw new Error('La pantalla debe convertir el JSON devuelto por la API en una fila de medio de pago.');
}

console.log('ConfiguracionMediosPagoTest OK');
