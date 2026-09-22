'use strict';
(() => {
    const page = document.querySelector('[data-crud]');
    const select = page?.querySelector('[data-cargo-select]');
    if (!select) return;
    const option = (value, label) => { const node = document.createElement('option'); node.value = value; node.textContent = label; return node; };
    fetch(page.dataset.controller + '?accion=cargos', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(response => response.json())
        .then(json => {
            if (!json.exito || !Array.isArray(json.datos)) throw new Error(json.mensaje || 'No se pudieron cargar los cargos.');
            const current = select.dataset.pendingValue || select.value;
            select.replaceChildren(option('', 'Seleccione un cargo'), ...json.datos.map(row => option(String(row.id_cargo), row.nombre + (Number(row.estado) === 1 ? '' : ' · inactivo'))));
            if (current) select.value = current;
            delete select.dataset.pendingValue;
        })
        .catch(() => { select.replaceChildren(option('', 'No se pudieron cargar los cargos')); });
})();
