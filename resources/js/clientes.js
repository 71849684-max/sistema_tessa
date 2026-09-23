'use strict';
function camposPorTipo(tipo) {
    const natural = tipo !== 'JURIDICA';
    return { natural, juridica: !natural, documento: natural ? 8 : 11 };
}
function consultaVigente(solicitud, actual) {
    return solicitud.dni === actual.dni && solicitud.tipo === actual.tipo && solicitud.version === actual.version;
}
if (typeof module !== 'undefined' && module.exports) module.exports = { camposPorTipo, consultaVigente };

if (typeof document !== 'undefined') (() => {
    const form = document.querySelector('[data-crud-form]');
    if (!form) return;
    const type = form.querySelector('[data-cliente-tipo]');
    const documentField = form.querySelector('[data-cliente-documento]');
    const action = form.querySelector('[data-reniec-action]');
    const status = form.querySelector('[data-reniec-status]');
    const button = form.querySelector('[data-consultar-reniec]');
    let version = 0;
    const contextoActual = () => ({ dni: documentField.value.trim(), tipo: type.value, version });
    const applyType = () => {
        const config = camposPorTipo(type.value);
        form.querySelectorAll('[data-natural-fields]').forEach(label => { label.hidden = !config.natural; label.querySelector('input').required = config.natural; });
        const company = form.querySelector('[data-juridica-fields]'); company.hidden = !config.juridica; company.querySelector('input').required = config.juridica;
        action.hidden = false;
        button.setAttribute('aria-label', config.natural ? 'Consultar DNI en RENIEC' : 'Consultar RUC en SUNAT');
        button.title = config.natural ? 'Consultar DNI en RENIEC' : 'Consultar RUC en SUNAT';
        button.innerHTML = '<span aria-hidden="true">⌕</span>';
        documentField.maxLength = config.documento;
        documentField.pattern = `[0-9]{${config.documento}}`;
        form.querySelector('[data-documento-etiqueta]').textContent = config.natural ? 'DNI' : 'RUC';
    };
    type.addEventListener('change', () => { version++; documentField.value = ''; applyType(); });
    documentField.addEventListener('input', () => { version++; });
    form.addEventListener('tessa:crud-edit', () => { version++; applyType(); });
    form.addEventListener('tessa:crud-new', () => { version++; applyType(); });
    button.addEventListener('click', async () => {
        const config = camposPorTipo(type.value);
        status.textContent = config.natural ? 'Consultando RENIEC…' : 'Consultando SUNAT…';
        status.setAttribute('aria-busy', 'true');
        button.disabled = true;
        const solicitud = contextoActual();
        try {
            const result = config.natural
                ? await window.TessaReniec.consultarDni(solicitud.dni)
                : await window.TessaSunat.consultarRuc(solicitud.dni);
            if (!consultaVigente(solicitud, contextoActual())) return;
            status.textContent = result.mensaje;
            status.className = result.exito ? 'success' : 'error';
            if (result.exito) {
                if (config.natural) {
                    form.elements.namedItem('nombres').value = result.datos.nombres;
                    form.elements.namedItem('apellidos').value = result.datos.apellidos;
                } else {
                    form.elements.namedItem('razon_social').value = result.datos.razon_social;
                    if (result.datos.direccion) form.elements.namedItem('direccion').value = result.datos.direccion;
                }
            }
        } catch { if (consultaVigente(solicitud, contextoActual())) { status.textContent = 'No se pudo consultar RENIEC. Ingrese los datos manualmente.'; status.className = 'error'; } }
        finally { button.disabled = false; status.removeAttribute('aria-busy'); }
    });
    applyType();
})();
