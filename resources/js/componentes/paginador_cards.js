(function (root) {
    'use strict';
    function paginaDatos(datos, pagina, porPagina) {
        const size = Math.max(1, Number(porPagina) || 1);
        const number = Math.max(1, Number(pagina) || 1);
        return datos.slice((number - 1) * size, number * size);
    }
    class PaginadorCards {
        static crear(config) { return new PaginadorCards(config); }
        constructor(config) {
            this.container = typeof config.contenedor === 'string' ? document.querySelector(config.contenedor) : config.contenedor;
            this.controls = typeof config.contenedorPaginacion === 'string' ? document.querySelector(config.contenedorPaginacion) : config.contenedorPaginacion;
            if (!this.container || !this.controls || typeof config.renderItem !== 'function') throw new Error('Configuración inválida de tarjetas.');
            this.renderItem = config.renderItem;
            this.pageSize = Number(config.porPagina) || 8;
            this.page = 1;
            this.setDatos(config.datos || []);
        }
        setDatos(datos) { this.data = Array.isArray(datos) ? datos : []; this.page = 1; this.render(); }
        render() {
            const pages = Math.max(1, Math.ceil(this.data.length / this.pageSize));
            this.page = Math.min(this.page, pages);
            this.container.replaceChildren();
            const items = paginaDatos(this.data, this.page, this.pageSize);
            if (!items.length) { const empty = document.createElement('p'); empty.className = 'empty-inline'; empty.textContent = 'No existen registros.'; this.container.append(empty); }
            items.forEach((item, index) => {
                const node = this.renderItem(item, (this.page - 1) * this.pageSize + index);
                if (!(node instanceof Element)) throw new Error('renderItem debe devolver un elemento DOM.');
                this.container.append(node);
            });
            this.controls.replaceChildren();
            const add = (label, destination, disabled) => {
                const button = document.createElement('button'); button.type = 'button'; button.textContent = label; button.disabled = disabled;
                button.addEventListener('click', () => { this.page = destination; this.render(); });
                this.controls.append(button);
            };
            add('‹ Anterior', this.page - 1, this.page === 1);
            const info = document.createElement('span'); info.textContent = `Página ${this.page} de ${pages}`; this.controls.append(info);
            add('Siguiente ›', this.page + 1, this.page === pages);
        }
    }
    if (typeof module !== 'undefined' && module.exports) module.exports = { PaginadorCards, paginaDatos };
    if (root) root.PaginadorCards = PaginadorCards;
})(typeof window === 'undefined' ? null : window);
