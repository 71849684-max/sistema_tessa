'use strict';
(function (root) {
    function normalizarRuc(value) { return String(value ?? '').replace(/\D+/gu, '').slice(0, 11); }
    async function consultarRuc(value) {
        const ruc = normalizarRuc(value);
        if (ruc.length !== 11) return { exito: false, mensaje: 'Ingrese un RUC de 11 dígitos.', datos: null, errores: { ruc: 'RUC inválido.' } };
        return tessaApi(root.TESSA_BASE + 'controllers/api/sunat_controller.php', { ruc });
    }
    if (typeof module !== 'undefined' && module.exports) module.exports = { normalizarRuc };
    if (root) root.TessaSunat = { normalizarRuc, consultarRuc };
})(typeof window === 'undefined' ? null : window);
