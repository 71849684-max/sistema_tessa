'use strict';
(() => {
    const page = document.querySelector('[data-crud]');
    const select = page?.querySelector('[data-persona-select]');
    if (!select) return;
    const option = (value, label) => { const node = document.createElement('option'); node.value = value; node.textContent = label; return node; };
    fetch(page.dataset.controller + '?accion=personas', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(response => response.json())
        .then(json => {
            if (!json.exito || !Array.isArray(json.datos)) throw new Error(json.mensaje || 'No se pudo cargar el personal.');
            const current = select.dataset.pendingValue || select.value;
            select.replaceChildren(option('', 'Seleccione una persona'), ...json.datos.map(row => option(String(row.id_personal), `${row.nombres} ${row.apellidos}${row.id_usuario ? ' · con usuario' : ''}${Number(row.estado) === 1 ? '' : ' · inactivo'}`)));
            if (current) select.value = current;
            delete select.dataset.pendingValue;
        })
        .catch(() => { select.replaceChildren(option('', 'No se pudo cargar el personal')); });
})();
