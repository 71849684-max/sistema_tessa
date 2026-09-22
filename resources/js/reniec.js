'use strict';
(function (root) {
    function normalizarDni(value) { return String(value ?? '').replace(/\D+/gu, '').slice(0, 8); }
    async function consultarDni(value) {
        const dni = normalizarDni(value);
        if (dni.length !== 8) return { exito: false, mensaje: 'Ingrese un DNI de 8 dígitos.', datos: null, errores: { dni: 'DNI inválido.' } };
        return tessaApi(root.TESSA_BASE + 'controllers/api/reniec_controller.php', { dni });
    }
    if (typeof module !== 'undefined' && module.exports) module.exports = { normalizarDni };
    if (root) root.TessaReniec = { normalizarDni, consultarDni };
})(typeof window === 'undefined' ? null : window);
