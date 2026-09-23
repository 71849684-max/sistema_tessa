'use strict';

const requisitosMedio = medio => medio === 'EFECTIVO' ? { requiereOperacion: false, requiereVoucher: false } : { requiereOperacion: true, requiereVoucher: true };
const opcionesPagador = (tipo, participantes) => tipo === 'GRUPAL' && Array.isArray(participantes) ? participantes : [];
const etiquetaContrato = contrato => `${contrato.con_numero} · ${contrato.cliente_nombre} · DNI ${contrato.documento} · saldo S/ ${Number(contrato.saldo || 0).toFixed(2)}`;

if (typeof module !== 'undefined' && module.exports) module.exports = { requisitosMedio, opcionesPagador, etiquetaContrato };

if (typeof document !== 'undefined') (() => {
    const form = document.querySelector('[data-crud-form]');
    if (!form) return;
    const contract = form.querySelector('[data-cobranza-contrato]');
    const search = form.querySelector('[data-cobranza-buscar]');
    const results = form.querySelector('[data-cobranza-resultados]');
    const selected = form.querySelector('[data-cobranza-selected]');
    const selectedText = selected?.querySelector('span');
    const clearContract = form.querySelector('[data-cobranza-clear]');
    const load = form.querySelector('[data-cargar-hitos]');
    const target = form.querySelector('[data-hitos-cobranza]');
    const hidden = form.querySelector('[data-detalles-json]');
    const medio = form.elements.medio_pago;
    const operation = form.elements.numero_operacion;
    const voucher = form.elements.voucher;
    const proof = form.querySelector('[data-comprobante-campos]');
    const payWrap = form.querySelector('[data-pagador-grupal]');
    const pay = form.elements.id_cliente_pagador;

    const aplicarMedio = () => {
        const requisitos = requisitosMedio(medio.value);
        proof.hidden = !requisitos.requiereOperacion;
        operation.required = requisitos.requiereOperacion;
        voucher.required = requisitos.requiereVoucher;
        operation.disabled = !requisitos.requiereOperacion;
        voucher.disabled = !requisitos.requiereVoucher;
        if (!requisitos.requiereOperacion) { operation.value = ''; voucher.value = ''; }
    };

    const cargarPagadores = async () => {
        payWrap.hidden = true;
        pay.replaceChildren();
        if (!contract.value) return;
        try {
            const respuesta = await fetch(`${window.TESSA_BASE}controllers/ventas/cobranza_controller.php?${new URLSearchParams({ accion: 'participantes', id_contrato: contract.value })}`, { credentials: 'same-origin' });
            const json = await respuesta.json();
            opcionesPagador(json.datos?.tipo, json.datos?.participantes).forEach(fila => {
                const option = document.createElement('option'); option.value = fila.id_cliente; option.textContent = fila.etiqueta; pay.append(option);
            });
            payWrap.hidden = pay.options.length === 0;
        } catch { payWrap.hidden = true; }
    };

    const limpiarContrato = () => {
        contract.value = '';
        search.value = '';
        selected.hidden = true;
        hidden.value = '';
        target.innerHTML = '<p class="hint">Busca y selecciona un contrato para consultar sus cuotas pendientes.</p>';
        contract.dispatchEvent(new Event('change'));
        search.focus();
    };

    const elegirContrato = fila => {
        contract.value = String(fila.id_contrato);
        selectedText.textContent = etiquetaContrato(fila);
        selected.hidden = false;
        search.value = '';
        contract.dispatchEvent(new Event('change'));
    };

    if (window.BuscadorCatalogo && search && results) {
        new window.BuscadorCatalogo({
            input: search,
            resultados: results,
            url: `${window.TESSA_BASE}controllers/ventas/cobranza_controller.php?accion=contratos`,
            etiqueta: etiquetaContrato,
            valor: fila => fila.id_contrato,
            alSeleccionar: elegirContrato
        });
    }
    clearContract?.addEventListener('click', limpiarContrato);

    const render = filas => {
        target.replaceChildren();
        if (!filas.length) { target.textContent = 'No hay cuotas pendientes para este contrato.'; return; }
        filas.forEach(fila => {
            const line = document.createElement('label'); line.className = 'payment-line';
            const check = document.createElement('input'); check.type = 'checkbox'; check.dataset.hito = fila.id_hito;
            const detail = document.createElement('span'); detail.textContent = `${fila.numero}. ${fila.descripcion} · saldo S/ ${Number(fila.saldo).toFixed(2)}`;
            const amount = document.createElement('input'); amount.type = 'number'; amount.min = '0.01'; amount.max = String(fila.saldo); amount.step = '0.01'; amount.placeholder = 'Monto'; amount.disabled = true;
            check.addEventListener('change', () => { amount.disabled = !check.checked; if (check.checked) amount.focus(); });
            line.append(check, detail, amount); target.append(line);
        });
    };

    load.addEventListener('click', async () => {
        if (!contract.value) { target.textContent = 'Busca y selecciona un contrato válido.'; search.focus(); return; }
        try {
            const respuesta = await fetch(`${window.TESSA_BASE}controllers/ventas/cobranza_controller.php?${new URLSearchParams({ accion: 'hitos', id_contrato: contract.value })}`, { credentials: 'same-origin' });
            const json = await respuesta.json(); render(json.datos || []);
        } catch { target.textContent = 'No se pudieron cargar las cuotas.'; }
    });
    contract.addEventListener('change', () => { target.textContent = 'Pulse “Cargar cuotas” para consultar los saldos.'; hidden.value = ''; cargarPagadores(); });
    medio.addEventListener('change', aplicarMedio);
    aplicarMedio();
    form.addEventListener('submit', event => {
        if (!contract.value) { event.preventDefault(); search.focus(); target.textContent = 'Busca y selecciona un contrato antes de guardar.'; return; }
        hidden.value = JSON.stringify([...target.querySelectorAll('input[data-hito]:checked')].map(check => ({ id_hito: Number(check.dataset.hito), monto: Number(check.parentElement.querySelector('input[type=number]').value) })));
    });
})();
