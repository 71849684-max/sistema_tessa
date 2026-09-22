'use strict';
const ACCIONES_POR_MODULO = {
    dashboard: ['ver'],
    clientes: ['ver', 'crear', 'editar'],
    servicios: ['ver', 'crear', 'editar'],
    contratos: ['ver', 'crear', 'anular'],
    cobranzas: ['ver', 'crear', 'anular'],
    egresos: ['ver', 'crear', 'anular'],
    personal: ['ver', 'crear', 'editar'],
    cargos: ['ver', 'crear', 'editar'],
    usuarios: ['ver', 'crear', 'editar', 'administrar'],
    auditoria: ['ver'],
};
function accionesDisponibles(modulo) { return ACCIONES_POR_MODULO[modulo] || ['ver']; }
function areaModulo(modulo) { return ['dashboard', 'clientes', 'servicios', 'contratos', 'cobranzas', 'egresos'].includes(modulo) ? 'Ventas' : 'Administración'; }
function ordenarPermisos(rows) {
    const order = Object.keys(ACCIONES_POR_MODULO);
    return [...rows].sort((left, right) => order.indexOf(left.modulo) - order.indexOf(right.modulo));
}
function respuestaVigente(idSolicitado, idActual, versionSolicitada, versionActual) {
    return String(idSolicitado) === String(idActual) && versionSolicitada === versionActual;
}
function valorTrasGuardado(anterior, solicitado, exito) { return exito ? solicitado : anterior; }
if (typeof module !== 'undefined' && module.exports) module.exports = { accionesDisponibles, areaModulo, ordenarPermisos, respuestaVigente, valorTrasGuardado };
if (typeof document !== 'undefined') (() => {
    const panel = document.querySelector('[data-permission-panel]');
    if (!panel) return;
    const base = window.TESSA_BASE;
    const url = panel.dataset.controller;
    const estadoUrl = base + 'controllers/rrhh/permisos_estado_controller.php';
    const catalogoUrl = base + 'controllers/rrhh/permisos_catalogo_controller.php';
    const forms = [panel.querySelector('[data-cargo-permission]'), panel.querySelector('[data-person-permission]')];
    const request = async target => {
        const response = await fetch(target, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
        const json = await response.json();
        if (!response.ok || !json.exito) throw new Error(json.mensaje || 'No se pudo cargar la información.');
        return json.datos;
    };
    const option = (value, label) => { const node = document.createElement('option'); node.value = String(value); node.textContent = label; return node; };
    const showMessage = (form, message, success) => { const output = form.querySelector('[data-permission-message]'); output.textContent = message; output.className = success ? 'success' : 'error'; };
    const humanAction = { ver: 'Ver', crear: 'Crear', editar: 'Editar', anular: 'Anular', administrar: 'Administrar' };
    const render = (form, rows, tipo, id) => {
        const container = form.querySelector('[data-permission-matrix]'); container.replaceChildren();
        const areas = new Map();
        ordenarPermisos(rows.filter(row => accionesDisponibles(row.modulo).includes(row.accion))).forEach(row => {
            const area = areaModulo(row.modulo);
            if (!areas.has(area)) areas.set(area, new Map());
            const modules = areas.get(area);
            if (!modules.has(row.modulo)) modules.set(row.modulo, []);
            modules.get(row.modulo).push(row);
        });
        areas.forEach((groups, areaName) => {
            const area = document.createElement('section'); area.className = 'permission-area';
            const areaTitle = document.createElement('h4'); areaTitle.textContent = areaName; area.append(areaTitle);
            groups.forEach((permissions, moduleName) => {
            const group = document.createElement('section'); group.className = 'permission-module';
            const heading = document.createElement('h5'); heading.textContent = moduleName === 'dashboard' ? 'Dashboard de ventas' : moduleName.charAt(0).toUpperCase() + moduleName.slice(1); group.append(heading);
            permissions.forEach(row => {
                const item = document.createElement('div'); item.className = 'permission-row';
                const label = document.createElement('div'); label.className = 'permission-label';
                const name = document.createElement('strong'); name.textContent = humanAction[row.accion] || row.accion; label.append(name);
                if (tipo === 'persona') { const source = document.createElement('small'); source.textContent = `Plantilla: ${row.plantilla == null ? 'sin definir' : Number(row.plantilla) ? 'permitido' : 'denegado'} · Efectivo: ${Number(row.efectivo) ? 'permitido' : 'denegado'}`; label.append(source); }
                item.append(label);
                const select = document.createElement('select'); select.setAttribute('aria-label', `${name.textContent} en ${moduleName}`);
                if (tipo === 'persona') select.append(option('heredar', 'Heredar cargo'));
                select.append(option('1', 'Habilitado'), option('0', 'Deshabilitado'));
                if (tipo === 'cargo' && row.plantilla == null) select.prepend(option('', 'Sin definir'));
                if (tipo === 'cargo') select.value = row.plantilla == null ? '' : String(row.plantilla);
                else select.value = row.excepcion == null ? 'heredar' : String(row.excepcion);
                select.addEventListener('change', async () => {
                    const anterior = tipo === 'cargo' ? (row.plantilla == null ? '' : String(row.plantilla)) : (row.excepcion == null ? 'heredar' : String(row.excepcion));
                    const solicitado = select.value;
                    select.disabled = true;
                    const data = { id_permiso: row.id_permiso, accion: tipo === 'cargo' ? 'permiso_cargo' : (select.value === 'heredar' ? 'permiso_quitar' : 'permiso') };
                    data[tipo === 'cargo' ? 'id_cargo' : 'id_personal'] = id;
                    if (select.value !== 'heredar') data.permitido = Number(select.value);
                    try {
                        const response = await tessaApi(url, data);
                        showMessage(form, response.mensaje, response.exito);
                        select.value = valorTrasGuardado(anterior, solicitado, response.exito);
                        if (response.exito && form.querySelector(tipo === 'cargo' ? '[data-cargo-options]' : '[data-person-options]').value === String(id)) await loadMatrix(form, tipo, id);
                        else select.disabled = false;
                    } catch { showMessage(form, 'No se pudo actualizar el permiso.', false); select.value = anterior; select.disabled = false; }
                });
                item.append(select); group.append(item);
            });
            area.append(group);
            });
            container.append(area);
        });
    };
    const loadMatrix = async (form, tipo, id) => {
        const container = form.querySelector('[data-permission-matrix]');
        const picker = form.querySelector(tipo === 'cargo' ? '[data-cargo-options]' : '[data-person-options]');
        const version = Number(form.dataset.requestVersion || 0) + 1;
        form.dataset.requestVersion = String(version);
        if (!id) { container.textContent = 'Seleccione un cargo o una persona para consultar sus permisos.'; return; }
        container.textContent = 'Cargando permisos…';
        try {
            const rows = await request(estadoUrl + '?' + new URLSearchParams({ tipo, id }));
            if (respuestaVigente(id, picker.value, version, Number(form.dataset.requestVersion))) render(form, rows, tipo, id);
        } catch (error) {
            if (respuestaVigente(id, picker.value, version, Number(form.dataset.requestVersion))) container.textContent = error.message;
        }
    };
    Promise.all([request(catalogoUrl + '?accion=catalogos'), request(catalogoUrl)]).then(([catalogos, permisos]) => {
        for (const form of forms) {
            const tipo = form.hasAttribute('data-cargo-permission') ? 'cargo' : 'persona';
            const picker = form.querySelector(tipo === 'cargo' ? '[data-cargo-options]' : '[data-person-options]');
            picker.replaceChildren(option('', 'Seleccione…'));
            (tipo === 'cargo' ? catalogos.cargos : catalogos.personas).forEach(row => picker.append(option(tipo === 'cargo' ? row.id_cargo : row.id_personal, tipo === 'cargo' ? row.nombre : `${row.nombres} ${row.apellidos}`)));
            const legacy = form.querySelector('[data-permission-options]');
            legacy.replaceChildren(...permisos.filter(row => accionesDisponibles(row.modulo).includes(row.accion)).map(row => option(row.id_permiso, `${row.modulo} · ${row.accion}`)));
            legacy.closest('label').hidden = true;
            form.querySelector('[name="permitido"]').closest('label').hidden = true;
            form.querySelector('button[type="submit"]').hidden = true;
            form.addEventListener('submit', event => event.preventDefault());
            const matrix = document.createElement('div'); matrix.dataset.permissionMatrix = ''; matrix.className = 'permission-matrix'; form.append(matrix);
            picker.addEventListener('change', () => loadMatrix(form, tipo, picker.value));
            const first = picker.options[1];
            if (first) { picker.value = first.value; loadMatrix(form, tipo, first.value); }
            else loadMatrix(form, tipo, '');
        }
    }).catch(error => forms.forEach(form => showMessage(form, error.message, false)));
})();
