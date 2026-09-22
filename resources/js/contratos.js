'use strict';
function etiquetaCliente(row) {
    return `${row.documento} · ${row.razon_social || [row.nombres, row.apellidos].filter(Boolean).join(' ')}`;
}
if (typeof module !== 'undefined' && module.exports) module.exports = { etiquetaCliente };

if (typeof document !== 'undefined') (() => {
    const form = document.querySelector('[data-crud-form]');
    if (!form) return;
    const list = form.querySelector('[data-hitos-list]');
    const type = form.querySelector('[data-contract-type]');
    const groupSection = form.querySelector('[data-integrantes-editor]');
    const groupList = form.querySelector('[data-integrantes-list]');
    const groupInput = form.querySelector('[data-integrante-buscar]');
    const principalInput = form.querySelector('[data-cliente-buscar]');
    const principalId = form.querySelector('[data-cliente-id]');
    const principalSelected = form.querySelector('[data-cliente-seleccion]');
    const services = form.querySelector('[data-servicios]');
    const people = form.querySelector('[data-personal]');
    const members = new Map();
    let numero = 0;
    let principalLabel = '';

    const row = () => {
        numero += 1;
        const item = document.createElement('div'); item.className = 'line-row hito-row';
        item.innerHTML = `<label>Cuota<input data-hito-numero type="number" min="1" value="${numero}"></label><label>Descripción<input data-hito-descripcion value="Cuota ${numero}"></label><label>Vencimiento<input data-hito-fecha type="date" required></label><label>Monto<input data-hito-monto type="number" min="0.01" step="0.01" required></label><button type="button" class="icon-button" data-remove-hito aria-label="Quitar cuota">×</button>`;
        list.append(item);
    };
    form.querySelector('[data-add-hito]').addEventListener('click', row);
    list.addEventListener('click', event => { const button = event.target.closest('[data-remove-hito]'); if (button) button.closest('.hito-row').remove(); });

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
    const searchUrl = window.TESSA_BASE + 'controllers/ventas/contrato_controller.php?accion=clientes_buscar';
    const principal = new BuscadorCatalogo({
        input: principalInput, resultados: form.querySelector('[data-cliente-resultados]'), url: searchUrl,
        etiqueta: etiquetaCliente, valor: client => client.id_cliente,
        alSeleccionar: client => {
            principalId.value = String(client.id_cliente); principalLabel = etiquetaCliente(client);
            principalInput.value = principalLabel; principalInput.setCustomValidity('');
            principalSelected.querySelector('span').textContent = principalLabel; principalSelected.hidden = false;
            members.delete(String(client.id_cliente)); renderMembers();
        },
    });
    principalInput.addEventListener('input', () => {
        if (principalInput.value !== principalLabel) { principalId.value = ''; principalSelected.hidden = true; principalInput.setCustomValidity(''); }
    });
    form.querySelector('[data-cliente-quitar]').addEventListener('click', () => {
        principalId.value = ''; principalLabel = ''; principal.limpiar(); principalSelected.hidden = true; principalInput.focus();
    });

    const group = new BuscadorCatalogo({
        input: groupInput, resultados: form.querySelector('[data-integrante-resultados]'), url: searchUrl,
        etiqueta: etiquetaCliente, valor: client => client.id_cliente,
        excluidos: () => [principalId.value, ...members.keys()],
        alSeleccionar: client => {
            members.set(String(client.id_cliente), client); renderMembers(); group.limpiar(); groupInput.setCustomValidity(''); groupInput.focus();
        },
    });
    type.addEventListener('change', () => {
        const isGroup = type.value === 'GRUPAL'; groupSection.hidden = !isGroup;
        if (!isGroup) { members.clear(); renderMembers(); group.limpiar(); groupInput.setCustomValidity(''); }
    });

    const addOptions = (select, rows, id, label) => {
        select.replaceChildren();
        const empty = document.createElement('option'); empty.value = ''; empty.textContent = 'Seleccione…'; select.append(empty);
        rows.filter(item => Number(item.estado) === 1).forEach(item => {
            const option = document.createElement('option'); option.value = String(item[id]); option.textContent = label(item); select.append(option);
        });
    };
    fetch(window.TESSA_BASE + 'controllers/ventas/contrato_controller.php?accion=catalogos', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(response => response.json())
        .then(json => {
            if (!json.exito) throw new Error(json.mensaje);
            addOptions(services, json.datos.servicios, 'id_servicio', item => `${item.nombre} · S/ ${Number(item.precio_referencia).toFixed(2)}`);
            addOptions(people, json.datos.personal, 'id_personal', item => `${item.nombres} ${item.apellidos}`);
            services.addEventListener('change', () => {
                const service = json.datos.servicios.find(item => String(item.id_servicio) === services.value);
                const gross = form.elements.namedItem('bruto'); if (service && !gross.value) gross.value = String(service.precio_referencia);
            });
        }).catch(() => [services, people].forEach(select => {
            const option = document.createElement('option'); option.value = ''; option.textContent = 'No se pudo cargar. Actualice la página.'; select.replaceChildren(option);
        }));

    form.addEventListener('submit', event => {
        if (!principalId.value) {
            event.preventDefault(); event.stopImmediatePropagation();
            principalInput.setCustomValidity('Seleccione un cliente de los resultados.'); principalInput.reportValidity(); return;
        }
        if (type.value === 'GRUPAL' && members.size === 0) {
            event.preventDefault(); event.stopImmediatePropagation();
            groupInput.setCustomValidity('Agregue al menos un integrante.'); groupInput.reportValidity(); return;
        }
        form.querySelector('[data-hitos-json]').value = JSON.stringify([...list.querySelectorAll('.hito-row')].map(item => ({
            numero: Number(item.querySelector('[data-hito-numero]').value), descripcion: item.querySelector('[data-hito-descripcion]').value.trim(),
            fecha_vencimiento: item.querySelector('[data-hito-fecha]').value, monto: Number(item.querySelector('[data-hito-monto]').value),
        })));
        form.querySelector('[data-integrantes-json]').value = JSON.stringify([...members.keys()].map(id_cliente => ({ id_cliente: Number(id_cliente) })));
    });
    document.querySelector('[data-dialog-open="crud-editor"]')?.addEventListener('click', () => {
        list.replaceChildren(); numero = 0; row();
        principalLabel = ''; principalId.value = ''; principal.limpiar(); principalSelected.hidden = true; principalInput.setCustomValidity('');
        members.clear(); renderMembers(); group.limpiar(); groupInput.setCustomValidity('');
        type.value = 'INDIVIDUAL'; groupSection.hidden = true;
    });
    row();
})();
