'use strict';
const CAMPOS_EMPRESA = ['razon_social', 'ruc', 'eslogan', 'representante_nombre', 'representante_cargo', 'representante_documento', 'domicilio_legal', 'ciudad', 'pie_direccion', 'pie_telefonos', 'pie_correo', 'titulo_contrato'];
const normalizarMediosPago = medios => {
    if (typeof medios === 'string') {
        try { medios = JSON.parse(medios); } catch { medios = []; }
    }
    return Array.isArray(medios) ? medios.map(medio => ({ medio: String(medio?.medio || '').trim(), titular: String(medio?.titular || '').trim(), numero: String(medio?.numero || '').replace(/\s+/g, ''), cci: String(medio?.cci || '').replace(/\s+/g, '') })).filter(medio => Object.values(medio).some(Boolean)) : [];
};
if (typeof module !== 'undefined' && module.exports) module.exports = { CAMPOS_EMPRESA, normalizarMediosPago };
if (typeof document !== 'undefined') (() => {
    const section = document.querySelector('[data-config-empresa]');
    if (!section) return;
    const form = section.querySelector('[data-empresa-form]');
    const message = section.querySelector('[data-form-message]');
    const medios = section.querySelector('[data-medios-pago]');
    const listaMedios = medios.querySelector('[data-medios-list]');
    const editable = section.dataset.editable === '1';
    const crearCampo = (etiqueta, campo, valor, maximo) => {
        const label = document.createElement('label');
        label.textContent = etiqueta;
        const input = document.createElement('input');
        input.dataset.medioCampo = campo;
        input.value = valor;
        input.maxLength = maximo;
        input.readOnly = !editable;
        if (campo === 'numero' || campo === 'cci') input.inputMode = 'numeric';
        label.append(input);
        return label;
    };
    const agregarMedio = (datos = {}) => {
        const fila = document.createElement('div');
        fila.className = 'line-row';
        fila.dataset.medioFila = '';
        fila.append(crearCampo('Medio', 'medio', String(datos.medio || ''), 80), crearCampo('Titular', 'titular', String(datos.titular || ''), 180), crearCampo('Número', 'numero', String(datos.numero || ''), 30), crearCampo('CCI', 'cci', String(datos.cci || ''), 30));
        if (editable) {
            const quitar = document.createElement('button');
            quitar.className = 'secondary outline';
            quitar.type = 'button';
            quitar.textContent = 'Quitar';
            quitar.addEventListener('click', () => fila.remove());
            fila.append(quitar);
        }
        listaMedios.append(fila);
    };
    const leerMedios = () => normalizarMediosPago([...listaMedios.querySelectorAll('[data-medio-fila]')].map(fila => Object.fromEntries([...fila.querySelectorAll('[data-medio-campo]')].map(input => [input.dataset.medioCampo, input.value]))));
    const rellenar = data => {
        for (const campo of CAMPOS_EMPRESA) { const input = form.elements.namedItem(campo); if (input && data[campo] != null) input.value = String(data[campo]); }
        listaMedios.replaceChildren();
        normalizarMediosPago(data.medios_pago).forEach(agregarMedio);
    };
    const mostrar = (texto, exito) => { message.textContent = texto; message.className = exito ? 'success' : 'error'; };
    fetch(section.dataset.controller + '?accion=obtener', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(respuesta => respuesta.json())
        .then(json => { if (!json.exito) throw new Error(json.mensaje); rellenar(json.datos || {}); })
        .catch(error => mostrar(error.message || 'No se pudo cargar la configuración.', false));
    const agregar = medios.querySelector('[data-medio-agregar]');
    if (agregar) agregar.addEventListener('click', () => agregarMedio());
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (section.dataset.editable !== '1') return;
        const payload = Object.fromEntries(new FormData(form).entries());
        payload.medios_pago = leerMedios();
        payload.accion = 'guardar';
        try {
            const json = await tessaApi(section.dataset.controller, payload);
            mostrar(json.mensaje, json.exito);
            if (json.exito) rellenar(json.datos || {});
        } catch (error) { mostrar(error.message || 'No se pudo guardar la configuración.', false); }
    });
})();
