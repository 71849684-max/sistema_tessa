'use strict';
function etiquetaCliente(row) { return `${row.documento} · ${row.razon_social || [row.nombres, row.apellidos].filter(Boolean).join(' ')}`; }
function calcularNeto(total, descuento) { return Math.round(((Number(total) || 0) - (Number(descuento) || 0)) * 100) / 100; }
function montoDesdePorcentaje(neto, porcentaje) { return Math.round(((Number(neto) || 0) * (Number(porcentaje) || 0) / 100) * 100) / 100; }
function porcentajeDesdeMonto(neto, monto) { const base = Number(neto) || 0; if (base <= 0) return 0; return Math.round(((Number(monto) || 0) / base * 100) * 100) / 100; }
function validarCamposPaso(section) {
    for (const field of section.querySelectorAll('input, select, textarea')) {
        if (!field.checkValidity()) { field.reportValidity(); return false; }
    }
    return true;
}
if (typeof module !== 'undefined' && module.exports) module.exports = { etiquetaCliente, calcularNeto, montoDesdePorcentaje, porcentajeDesdeMonto, validarCamposPaso };

if (typeof document !== 'undefined') (() => {
    const form = document.querySelector('[data-crud-form]');
    if (!form) return;
    const list = form.querySelector('[data-hitos-list]'), type = form.querySelector('[data-contract-type]');
    const groupSection = form.querySelector('[data-integrantes-editor]'), groupList = form.querySelector('[data-integrantes-list]');
    const groupInput = form.querySelector('[data-integrante-buscar]'), principalInput = form.querySelector('[data-cliente-buscar]');
    const principalId = form.querySelector('[data-cliente-id]'), principalSelected = form.querySelector('[data-cliente-seleccion]');
    const services = form.querySelector('[data-servicios]'), people = form.querySelector('[data-personal]');
    const total = form.elements.namedItem('bruto'), discount = form.elements.namedItem('descuento');
    const netSummary = form.querySelector('[data-net-summary]'), netValue = form.querySelector('[data-net-value]');
    const members = new Map(), steps = [...form.querySelectorAll('[data-contract-step]')], wizardStep = form.elements.namedItem('wizard_step');
    let numero = 0, principalLabel = '', principalSearch, groupSearch;
    const row = (data = {}) => {
        numero = Math.max(numero + 1, Number(data.numero || 0));
        const item = document.createElement('div'); item.className = 'line-row hito-row';
        item.innerHTML = '<label>Cuota<input data-hito-numero type="number" min="1" required></label><label>Descripción<input data-hito-descripcion required></label><label>Vencimiento<input data-hito-fecha type="date" required></label><label>%<input data-hito-porcentaje type="number" min="0" max="100" step="0.01"></label><label>Monto<input data-hito-monto type="number" min="0.01" step="0.01" required></label><button type="button" class="icon-button" data-remove-hito aria-label="Quitar cuota">×</button>';
        item.querySelector('[data-hito-numero]').value = String(numero);
        item.querySelector('[data-hito-descripcion]').value = String(data.descripcion || `Cuota ${numero}`);
        item.querySelector('[data-hito-fecha]').value = String(data.fecha_vencimiento || '').slice(0, 10);
        item.querySelector('[data-hito-monto]').value = data.monto == null ? '' : Number(data.monto).toFixed(2);
        const porcentaje = item.querySelector('[data-hito-porcentaje]'), monto = item.querySelector('[data-hito-monto]');
        const netoInicial = calcularNeto(total.value, discount.value);
        if (data.monto != null && netoInicial > 0) porcentaje.value = String(porcentajeDesdeMonto(netoInicial, data.monto));
        porcentaje.addEventListener('input', () => {
            if (porcentaje.value === '' || Number.isNaN(Number(porcentaje.value))) return;
            const neto = calcularNeto(total.value, discount.value);
            if (neto <= 0) return;
            monto.value = montoDesdePorcentaje(neto, porcentaje.value).toFixed(2);
        });
        monto.addEventListener('input', () => {
            const neto = calcularNeto(total.value, discount.value);
            if (neto <= 0 || monto.value === '' || Number.isNaN(Number(monto.value))) return;
            porcentaje.value = String(porcentajeDesdeMonto(neto, monto.value));
        });
        list.append(item);
    };
    const renderMembers = () => {
        groupList.replaceChildren();
        members.forEach((client, id) => {
            const item = document.createElement('div'); item.className = 'member-chip';
            const name = document.createElement('span'); name.textContent = etiquetaCliente(client);
            const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = '×'; remove.setAttribute('aria-label', 'Quitar ' + etiquetaCliente(client));
            remove.addEventListener('click', () => { members.delete(id); renderMembers(); });
            item.append(name, remove); groupList.append(item);
        });
    };
    const showStep = step => {
        const current = Math.max(1, Math.min(2, step));
        steps.forEach(section => { section.hidden = Number(section.dataset.contractStep) !== current; });
        form.querySelectorAll('[data-wizard-step]').forEach(item => { item.setAttribute('aria-current', Number(item.dataset.wizardStep) === current ? 'step' : 'false'); });
        wizardStep.value = String(current);
        form.querySelector('[data-wizard-next]').hidden = current === 2;
        form.querySelector('[data-wizard-prev]').hidden = current === 1;
        form.querySelector('[data-wizard-save]').hidden = current === 1;
    };
    const mostrarNeto = () => {
        const descuento = Number(discount.value) || 0;
        netSummary.hidden = descuento <= 0;
        netValue.textContent = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(calcularNeto(total.value, discount.value));
        const neto = calcularNeto(total.value, discount.value);
        list.querySelectorAll('.hito-row').forEach(item => {
            const porcentaje = item.querySelector('[data-hito-porcentaje]');
            if (!porcentaje || porcentaje.value === '') return;
            item.querySelector('[data-hito-monto]').value = neto <= 0 ? '' : montoDesdePorcentaje(neto, porcentaje.value).toFixed(2);
        });
        discount.setCustomValidity('');
    };
    total.addEventListener('input', mostrarNeto);
    discount.addEventListener('input', mostrarNeto);
    const searchUrl = window.TESSA_BASE + 'controllers/ventas/contrato_controller.php?accion=clientes_buscar';
    principalSearch = new BuscadorCatalogo({ input: principalInput, resultados: form.querySelector('[data-cliente-resultados]'), url: searchUrl, etiqueta: etiquetaCliente, valor: client => client.id_cliente,
        alSeleccionar: client => { principalId.value = String(client.id_cliente); principalLabel = etiquetaCliente(client); principalInput.value = principalLabel; principalInput.setCustomValidity(''); principalSelected.querySelector('span').textContent = principalLabel; principalSelected.hidden = false; members.delete(String(client.id_cliente)); renderMembers(); } });
    principalInput.addEventListener('input', () => { if (principalInput.value !== principalLabel) { principalId.value = ''; principalSelected.hidden = true; principalInput.setCustomValidity(''); } });
    groupSearch = new BuscadorCatalogo({ input: groupInput, resultados: form.querySelector('[data-integrante-resultados]'), url: searchUrl, etiqueta: etiquetaCliente, valor: client => client.id_cliente,
        excluidos: () => [principalId.value, ...members.keys()], alSeleccionar: client => { members.set(String(client.id_cliente), client); renderMembers(); groupSearch.limpiar(); groupInput.setCustomValidity(''); groupInput.focus(); } });
    form.querySelector('[data-add-hito]').addEventListener('click', () => row());
    list.addEventListener('click', event => { const button = event.target.closest('[data-remove-hito]'); if (button) button.closest('.hito-row').remove(); });
    type.addEventListener('change', () => { groupSection.hidden = type.value !== 'GRUPAL'; if (type.value !== 'GRUPAL') { members.clear(); renderMembers(); } });
    form.querySelector('[data-cliente-quitar]').addEventListener('click', () => { principalId.value = ''; principalLabel = ''; principalSearch.limpiar(); principalSelected.hidden = true; });
    const addOptions = (select, rows, id, label) => { select.replaceChildren(new Option('Seleccione…', '')); rows.filter(item => Number(item.estado) === 1).forEach(item => select.append(new Option(label(item), String(item[id])))); };
    const catalogosListos = fetch(window.TESSA_BASE + 'controllers/ventas/contrato_controller.php?accion=catalogos', { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(r => r.json()).then(json => {
        if (!json.exito) throw new Error(json.mensaje);
        addOptions(services, json.datos.servicios, 'id_servicio', item => `${item.nombre} · S/ ${Number(item.precio_referencia).toFixed(2)}`);
        addOptions(people, json.datos.personal, 'id_personal', item => `${item.nombres} ${item.apellidos}`);
        services.addEventListener('change', () => { const selected = json.datos.servicios.find(item => String(item.id_servicio) === services.value); if (selected && !total.value) { total.value = String(selected.precio_referencia); mostrarNeto(); } });
    }).catch(() => [services, people].forEach(select => select.replaceChildren(new Option('No se pudo cargar.', ''))));
    const validarPaso1 = () => {
        if (!principalId.value) { principalInput.setCustomValidity('Seleccione un titular de los resultados.'); principalInput.reportValidity(); return false; }
        if (type.value === 'GRUPAL' && members.size === 0) { groupInput.setCustomValidity('Agregue al menos un integrante distinto del titular.'); groupInput.reportValidity(); return false; }
        principalInput.setCustomValidity(''); groupInput.setCustomValidity('');
        if (!validarCamposPaso(steps[0])) return false;
        if (calcularNeto(total.value, discount.value) <= 0) {
            discount.setCustomValidity('El descuento debe ser menor que el total.'); discount.reportValidity(); return false;
        }
        discount.setCustomValidity('');
        return true;
    };
    form.querySelector('[data-wizard-next]').addEventListener('click', () => { if (validarPaso1()) showStep(2); });
    form.querySelector('[data-wizard-prev]').addEventListener('click', () => showStep(1));
    form.addEventListener('submit', event => {
        if (Number(wizardStep.value) !== 2) { event.preventDefault(); event.stopImmediatePropagation(); if (validarPaso1()) showStep(2); return; }
        if (!validarPaso1()) { event.preventDefault(); event.stopImmediatePropagation(); showStep(1); return; }
        const hitos = [...list.querySelectorAll('.hito-row')];
        const conPorcentaje = hitos.filter(item => item.querySelector('[data-hito-porcentaje]').value !== '');
        const sumaPorcentaje = Math.round(hitos.reduce((sum, item) => sum + (Number(item.querySelector('[data-hito-porcentaje]').value) || 0), 0) * 100) / 100;
        let errorHitos = '';
        if (hitos.length && conPorcentaje.length === hitos.length && sumaPorcentaje !== 100) errorHitos = `La suma de porcentajes debe ser 100%. Suma actual: ${sumaPorcentaje.toFixed(2)}%.`;
        else if (!hitos.length || Math.abs(hitos.reduce((sum, item) => sum + Number(item.querySelector('[data-hito-monto]').value || 0), 0) - calcularNeto(total.value, discount.value)) > 0.001) errorHitos = 'La suma de cuotas debe coincidir con el total después del descuento.';
        if (errorHitos) {
            event.preventDefault(); event.stopImmediatePropagation();
            const message = form.querySelector('[data-form-message]'); message.textContent = errorHitos; message.className = 'error'; return;
        }
        form.querySelector('[data-hitos-json]').value = JSON.stringify([...list.querySelectorAll('.hito-row')].map(item => ({ numero: Number(item.querySelector('[data-hito-numero]').value), descripcion: item.querySelector('[data-hito-descripcion]').value.trim(), fecha_vencimiento: item.querySelector('[data-hito-fecha]').value, monto: Number(item.querySelector('[data-hito-monto]').value) })));
        form.querySelector('[data-integrantes-json]').value = JSON.stringify([...members.keys()].map(id_cliente => ({ id_cliente: Number(id_cliente) })));
    });
    const reset = () => { form.reset(); list.replaceChildren(); members.clear(); renderMembers(); numero = 0; row(); principalId.value = ''; principalLabel = ''; principalSearch.limpiar(); principalInput.setCustomValidity(''); principalSelected.hidden = true; groupSearch.limpiar(); groupInput.setCustomValidity(''); type.value = 'INDIVIDUAL'; groupSection.hidden = true; mostrarNeto(); showStep(1); };
    form.addEventListener('tessa:crud-new', reset);
    form.addEventListener('tessa:crud-edit', async event => {
        const id = event.detail.row.id_contrato; reset(); form.elements.namedItem('id_contrato').value = String(id);
        try {
            await catalogosListos;
            const response = await fetch(window.TESSA_BASE + 'controllers/ventas/contrato_controller.php?accion=detalle&id_contrato=' + encodeURIComponent(id), { credentials: 'same-origin', headers: { Accept: 'application/json' } });
            const json = await response.json();
            if (!response.ok || !json.exito) throw new Error(json.mensaje || 'No se pudo cargar el contrato.');
            const data = json.datos || {};
            const contrato = data.contrato || data;
            for (const name of ['id_servicio','id_responsable','tipo','fecha','descuento']) if (contrato[name] != null) form.elements.namedItem(name).value = String(contrato[name]).slice(0, name === 'fecha' ? 10 : undefined);
            total.value = String(contrato.monto_bruto ?? contrato.bruto ?? ''); mostrarNeto();
            if (contrato.id_cliente) { principalId.value = String(contrato.id_cliente); principalLabel = etiquetaCliente(contrato); principalInput.value = principalLabel; principalSelected.querySelector('span').textContent = principalLabel; principalSelected.hidden = false; }
            list.replaceChildren(); numero = 0; (data.hitos || []).forEach(item => row(item));
            (data.integrantes || []).forEach(item => members.set(String(item.id_cliente), item)); renderMembers(); groupSection.hidden = type.value !== 'GRUPAL'; showStep(1);
        } catch (error) { form.querySelector('[data-form-message]').textContent = error.message || 'No se pudo cargar el detalle del contrato.'; }
    });
    form.querySelector('[data-wizard-save]').hidden = true; row(); showStep(1);
})();
