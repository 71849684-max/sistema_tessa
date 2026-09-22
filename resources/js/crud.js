'use strict';

function fieldValue(row, key) {
    if (key === 'nombre' && row.razon_social) return row.razon_social;
    if (key === 'nombre' && row.nombres) return [row.nombres, row.apellidos || ''].join(' ').trim();
    const value = row[key];
    if ((key === 'estado' || key === 'usu_estado') && (value === 1 || value === '1')) return 'Activo';
    if ((key === 'estado' || key === 'usu_estado') && (value === 0 || value === '0')) return 'Inactivo';
    return value == null ? '' : String(value);
}
function metricValue(rows, metric) {
    const selected = rows.filter(row => (!metric.state || String(row.estado) === String(metric.state))
        && (!metric.hasField || Number(row[metric.hasField]) > 0)
        && (!metric.missingField || !row[metric.missingField]));
    if (metric.type === 'count') return selected.length;
    const total = selected.reduce((sum, row) => sum + (Number(row[metric.field]) || 0), 0);
    return metric.type === 'avg' ? (selected.length ? total / selected.length : 0) : total;
}
if (typeof module !== 'undefined' && module.exports) module.exports = { fieldValue, metricValue };

if (typeof document !== 'undefined') (() => {
    const page = document.querySelector('[data-crud]');
    if (!page) return;
    const url = page.dataset.controller;
    const columns = JSON.parse(page.dataset.columns || '{}');
    const config = JSON.parse(page.dataset.config || '{}');
    const form = page.querySelector('[data-crud-form]');
    const body = page.querySelector('[data-crud-body]');
    const editor = page.querySelector('#crud-editor');
    const voidDialog = page.querySelector('#crud-void');
    const message = page.querySelector('[data-form-message]');
    const listMessage = page.querySelector('[data-list-message]');
    let paginator = null;
    let cards = null;
    let filters = {};

    const rowLabel = row => String(row.con_numero || row.nombre || row.razon_social || row.nombres || row.concepto || row[config.id] || 'registro');
    const createButton = (label, action, row) => {
        const button = document.createElement('button');
        button.type = 'button'; button.className = action === 'anular' ? 'table-action danger-text' : 'table-action';
        button.textContent = label; button.dataset.rowAction = action; button.dataset.rowId = String(row[config.id]);
        return button;
    };
    const actionContainer = row => {
        const wrap = document.createElement('div'); wrap.className = 'row-actions';
        if (config.editar) wrap.append(createButton('Editar', 'editar', row));
        if (config.anular && row.estado !== 'ANULADO') wrap.append(createButton('Anular', 'anular', row));
        return wrap;
    };
    const renderCard = row => {
        const article = document.createElement('article'); article.className = 'person-card';
        const avatar = document.createElement('div'); avatar.className = 'person-avatar'; avatar.textContent = String(row.nombres || '?').trim().charAt(0).toUpperCase();
        const title = document.createElement('h4'); title.textContent = [row.nombres || '', row.apellidos || ''].join(' ').trim();
        const role = document.createElement('p'); role.textContent = row.cargo || 'Sin cargo'; role.className = 'muted';
        const meta = document.createElement('dl');
        for (const [label, value] of [['DNI', row.dni], ['Usuario', row.usu_nombre || 'Sin usuario'], ['Correo', row.correo || '—'], ['Estado', fieldValue(row, 'estado')]]) {
            const dt = document.createElement('dt'); dt.textContent = label;
            const dd = document.createElement('dd'); dd.textContent = String(value || '—');
            meta.append(dt, dd);
        }
        article.append(avatar, title, role, meta);
        if (config.editar || config.anular) article.append(actionContainer(row));
        return article;
    };
    let rowsById = new Map();
    const render = rows => {
        (config.metrics || []).forEach((metric, index) => {
            const target = page.querySelector(`[data-metric="${index}"]`);
            if (!target) return;
            const amount = metricValue(rows, metric);
            target.textContent = metric.currency ? new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(amount) : String(amount);
        });
        rowsById = new Map(rows.map(row => [String(row[config.id]), row]));
        page.querySelector('[data-list-count]').textContent = `${rows.length} ${rows.length === 1 ? 'registro' : 'registros'}`;
        if (config.cards) {
            if (!cards) cards = new PaginadorCards({ datos: rows, porPagina: 8, contenedor: page.querySelector('[data-card-list]'), contenedorPaginacion: page.querySelector('[data-card-pagination]'), renderItem: renderCard });
            else cards.setDatos(rows);
            return;
        }
        const rendered = rows.map(row => {
            const tr = document.createElement('tr');
            for (const key of Object.keys(columns)) {
                const td = document.createElement('td');
                const value = fieldValue(row, key);
                if (key === 'estado' || key === 'usu_estado') { const badge = document.createElement('span'); badge.className = 'status-badge ' + (value === 'ANULADO' || value === 'Inactivo' ? 'is-off' : 'is-on'); badge.textContent = value; td.append(badge); }
                else td.textContent = value;
                tr.append(td);
            }
            if (config.editar || config.anular) { const td = document.createElement('td'); td.append(actionContainer(row)); tr.append(td); }
            return tr;
        });
        if (!paginator) paginator = new PaginadorTablas({ tabla: page.querySelector('table'), contenedor: page.querySelector('[data-table-host]'), pageSize: 10 });
        paginator.setRows(rendered);
    };
    const load = async () => {
        listMessage.hidden = true;
        try {
            const response = await fetch(url + '?' + new URLSearchParams({ accion: 'listar', ...filters }), { credentials: 'same-origin', headers: { Accept: 'application/json' } });
            const json = await response.json();
            if (!response.ok || !json.exito || !Array.isArray(json.datos)) throw new Error(json.mensaje || 'No se pudo cargar el listado.');
            render(json.datos);
        } catch (error) {
            listMessage.textContent = error.message || 'No se pudo cargar el listado.';
            listMessage.hidden = false;
        }
    };
    const fillForm = row => {
        form.reset();
        form.elements.namedItem(config.id).value = String(row[config.id]);
        const password = form.elements.namedItem('clave');
        if (password) password.required = false;
        const aliases = { usuario: 'usu_nombre', estado: row.usu_estado == null ? 'estado' : 'usu_estado', bruto: 'monto_bruto' };
        [...form.elements].forEach(element => {
            if (!element.name || element.name === 'accion' || element.type === 'file' || element.type === 'password') return;
            const key = aliases[element.name] || element.name;
            if (row[key] != null) {
                const value = String(row[key]);
                if (element instanceof HTMLSelectElement && ![...element.options].some(option => option.value === value)) element.dataset.pendingValue = value;
                else element.value = value;
            }
        });
        message.textContent = '';
        page.querySelector('[data-editor-title]').textContent = 'Editar registro';
        form.dispatchEvent(new CustomEvent('tessa:crud-edit', { detail: { row } }));
        editor.showModal();
    };
    page.addEventListener('click', event => {
        const button = event.target.closest('[data-row-action]');
        if (!button) return;
        const row = rowsById.get(button.dataset.rowId);
        if (!row) return;
        if (button.dataset.rowAction === 'editar' && config.editar) fillForm(row);
        if (button.dataset.rowAction === 'anular' && config.anular) {
            const voidForm = voidDialog.querySelector('form'); voidForm.reset();
            voidForm.querySelector('[data-void-id]').value = String(row[config.id]);
            voidForm.querySelector('[data-void-target]').textContent = 'Registro: ' + rowLabel(row);
            voidForm.querySelector('[data-void-message]').textContent = '';
            voidDialog.showModal();
        }
    });
    page.querySelector('[data-dialog-open="crud-editor"]')?.addEventListener('click', () => {
        form.reset();
        const idField = config.id ? form.elements.namedItem(config.id) : null;
        if (idField) idField.value = '0';
        const password = form.elements.namedItem('clave');
        if (password) password.required = true;
        message.textContent = ''; page.querySelector('[data-editor-title]').textContent = 'Nuevo registro';
        form.dispatchEvent(new CustomEvent('tessa:crud-new'));
    });
    page.querySelector('[data-reload]')?.addEventListener('click', load);
    page.querySelector('[data-filter-form]')?.addEventListener('submit', event => {
        event.preventDefault(); filters = Object.fromEntries(new FormData(event.currentTarget)); load();
    });
    page.querySelector('[data-filter-clear]')?.addEventListener('click', () => { page.querySelector('[data-filter-form]').reset(); filters = {}; load(); });
    form?.addEventListener('submit', async event => {
        event.preventDefault();
        try {
            const json = await tessaApi(url, new FormData(form), true);
            message.textContent = json.mensaje; message.className = json.exito ? 'success' : 'error';
            if (json.exito) { editor.close(); form.reset(); await load(); }
        } catch { message.textContent = 'No se pudo guardar el registro.'; message.className = 'error'; }
    });
    page.querySelector('[data-void-form]')?.addEventListener('submit', async event => {
        event.preventDefault();
        const output = event.currentTarget.querySelector('[data-void-message]');
        try {
            const json = await tessaApi(url, Object.fromEntries(new FormData(event.currentTarget)));
            output.textContent = json.mensaje; output.className = json.exito ? 'success' : 'error';
            if (json.exito) { voidDialog.close(); await load(); }
        } catch { output.textContent = 'No se pudo anular el registro.'; output.className = 'error'; }
    });
    load();
})();
