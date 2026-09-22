'use strict';
(function (root) {
    function normalizarBusqueda(value) { return String(value ?? '').trim().replace(/\s+/gu, ' ').slice(0, 100); }
    function opcionesVisibles(rows, excluded, valueOf) {
        const ids = new Set(excluded.map(String));
        return rows.filter(row => !ids.has(String(valueOf(row))));
    }
    function respuestaVigente(solicitada, actual) { return solicitada === actual; }

    class BuscadorCatalogo {
        constructor({ input, resultados, url, etiqueta, valor, alSeleccionar, excluidos = () => [] }) {
            this.input = input;
            this.resultados = resultados;
            this.url = url;
            this.etiqueta = etiqueta;
            this.valor = valor;
            this.alSeleccionar = alSeleccionar;
            this.excluidos = excluidos;
            this.secuencia = 0;
            this.espera = null;
            this.input.setAttribute('autocomplete', 'off');
            this.input.setAttribute('role', 'combobox');
            this.input.setAttribute('aria-autocomplete', 'list');
            this.input.setAttribute('aria-expanded', 'false');
            this.resultados.setAttribute('role', 'listbox');
            this.input.addEventListener('input', () => {
                clearTimeout(this.espera);
                this.limpiarResultados();
                const consulta = normalizarBusqueda(this.input.value);
                if (consulta.length < 2) return;
                this.espera = setTimeout(() => this.buscar(consulta), 240);
            });
            this.input.addEventListener('keydown', event => {
                if (event.key === 'Escape') this.limpiarResultados();
                if (event.key === 'ArrowDown') { const first = this.resultados.querySelector('button'); if (first) { event.preventDefault(); first.focus(); } }
            });
            document.addEventListener('pointerdown', event => {
                if (!this.input.contains(event.target) && !this.resultados.contains(event.target)) this.limpiarResultados();
            });
        }
        limpiarResultados() { this.secuencia++; this.resultados.replaceChildren(); this.resultados.hidden = true; this.input.setAttribute('aria-expanded', 'false'); }
        limpiar() { this.input.value = ''; this.limpiarResultados(); }
        async buscar(consulta) {
            const actual = ++this.secuencia;
            const destino = new URL(this.url, window.location.href);
            destino.searchParams.set('buscar', consulta);
            try {
                const response = await fetch(destino.toString(), { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                const json = await response.json();
                if (!response.ok || !json.exito || !Array.isArray(json.datos)) throw new Error(json.mensaje || 'No se pudo buscar.');
                if (!respuestaVigente(actual, this.secuencia)) return;
                this.mostrar(opcionesVisibles(json.datos, this.excluidos(), this.valor));
            } catch (error) {
                if (respuestaVigente(actual, this.secuencia)) this.mensaje(error.message || 'No se pudo buscar.');
            }
        }
        mensaje(texto) {
            this.resultados.replaceChildren();
            const line = document.createElement('p'); line.textContent = texto; line.className = 'buscador-empty';
            this.resultados.append(line); this.resultados.hidden = false; this.input.setAttribute('aria-expanded', 'true');
        }
        mostrar(rows) {
            this.resultados.replaceChildren();
            if (!rows.length) { this.mensaje('No hay clientes disponibles con esa búsqueda.'); return; }
            rows.forEach(row => {
                const button = document.createElement('button'); button.type = 'button'; button.className = 'buscador-option';
                button.setAttribute('role', 'option'); button.textContent = this.etiqueta(row);
                button.addEventListener('click', () => { this.alSeleccionar(row); this.limpiarResultados(); });
                this.resultados.append(button);
            });
            this.resultados.hidden = false; this.input.setAttribute('aria-expanded', 'true');
        }
    }
    if (typeof module !== 'undefined' && module.exports) module.exports = { BuscadorCatalogo, normalizarBusqueda, opcionesVisibles, respuestaVigente };
    if (root) root.BuscadorCatalogo = BuscadorCatalogo;
})(typeof window === 'undefined' ? null : window);
