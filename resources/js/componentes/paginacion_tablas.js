(function (root) {
    'use strict';

    function csvCell(value) {
        const text = String(value ?? '');
        return /^[\s\uFEFF]*[=+\-@]/u.test(text) ? "'" + text : text;
    }

    class PaginadorTablas {
        constructor(options = {}) {
            this.table = typeof options.tabla === 'string' ? document.querySelector(options.tabla) : options.tabla;
            if (!this.table) throw new Error('PaginadorTablas requiere una tabla.');
            this.host = typeof options.contenedor === 'string' ? document.querySelector(options.contenedor) : (options.contenedor || this.table.closest('[data-table-host]'));
            if (!this.host) throw new Error('PaginadorTablas requiere un contenedor.');
            this.pageSize = Number(options.pageSize) || 10;
            this.pageSizes = options.pageSizes || [10, 25, 50, 100];
            this.exportOptions = Object.assign({ csv: true, copy: true, pdf: true }, options.exportOpciones || {});
            this.rows = [];
            this.filtered = [];
            this.page = 1;
            this.query = '';
            this.sort = { index: -1, direction: 1 };
            this.top = document.createElement('div');
            this.top.className = 'paginador-topbar';
            this.bottom = document.createElement('div');
            this.bottom.className = 'paginador-controls';
            this.buildControls();
            const scroller = this.table.closest('.table-scroll');
            if (scroller && this.host.contains(scroller)) scroller.before(this.top);
            else this.host.prepend(this.top);
            this.host.append(this.bottom);
            this.headerClick = event => {
                const button = event.target.closest('[data-sort-column]');
                if (!button || !this.table.contains(button)) return;
                const index = Number(button.dataset.sortColumn);
                this.sort.direction = this.sort.index === index ? -this.sort.direction : 1;
                this.sort.index = index;
                this.apply();
            };
            this.table.addEventListener('click', this.headerClick);
            [...(this.table.tHead?.rows[0]?.cells || [])].forEach((cell, index) => {
                if (cell.dataset.noSort !== undefined) return;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'paginador-sort-toggle';
                button.dataset.sortColumn = String(index);
                button.setAttribute('aria-label', 'Ordenar por ' + cell.textContent.trim());
                button.textContent = '↕';
                cell.append(button);
            });
            this.refreshFromDom();
        }

        buildControls() {
            const search = document.createElement('input');
            search.type = 'search';
            search.placeholder = 'Buscar en resultados…';
            search.setAttribute('aria-label', 'Buscar en la tabla');
            search.addEventListener('input', () => { this.query = search.value.trim().toLocaleLowerCase('es'); this.page = 1; this.apply(); });
            this.top.append(search);
            const size = document.createElement('select');
            size.setAttribute('aria-label', 'Filas por página');
            this.pageSizes.forEach(value => {
                const option = document.createElement('option');
                option.value = String(value);
                option.textContent = `${value} filas`;
                option.selected = value === this.pageSize;
                size.append(option);
            });
            size.addEventListener('change', () => { this.pageSize = Number(size.value); this.page = 1; this.render(); });
            this.top.append(size);
            const actions = document.createElement('div');
            actions.className = 'paginador-acciones';
            for (const [key, label, handler] of [
                ['csv', 'CSV', () => this.exportCSV()],
                ['copy', 'Copiar', () => this.copyToClipboard()],
                ['pdf', 'Imprimir / PDF', () => this.printTable()]
            ]) {
                if (!this.exportOptions[key]) continue;
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = label;
                button.addEventListener('click', handler);
                actions.append(button);
            }
            this.top.append(actions);
        }

        refreshFromDom() { this.setRows([...(this.table.tBodies[0]?.rows || [])].filter(row => row.dataset.empty !== '1')); }
        setRows(rows) { this.rows = [...rows]; this.page = 1; this.apply(); }
        columns() { return [...(this.table.tHead?.rows[0]?.cells || [])].map((cell, index) => ({ cell, index })).filter(item => item.cell.dataset.noExport === undefined); }
        apply() {
            this.filtered = this.rows.filter(row => !this.query || row.textContent.toLocaleLowerCase('es').includes(this.query));
            if (this.sort.index >= 0) {
                const { index, direction } = this.sort;
                this.filtered.sort((a, b) => {
                    const left = a.cells[index]?.textContent.trim() || '';
                    const right = b.cells[index]?.textContent.trim() || '';
                    const numeric = value => /^\s*(?:S\/\s*)?-?[\d,.]+\s*$/.test(value) ? Number(value.replace(/[^\d.-]/g, '')) : NaN;
                    const an = numeric(left), bn = numeric(right);
                    const result = Number.isFinite(an) && Number.isFinite(bn) ? an - bn : left.localeCompare(right, 'es', { numeric: true, sensitivity: 'base' });
                    return result * direction;
                });
            }
            this.render();
        }
        render() {
            const body = this.table.tBodies[0];
            if (!body) return;
            const pages = Math.max(1, Math.ceil(this.filtered.length / this.pageSize));
            this.page = Math.min(this.page, pages);
            body.replaceChildren(...this.filtered.slice((this.page - 1) * this.pageSize, this.page * this.pageSize));
            this.bottom.replaceChildren();
            const info = document.createElement('span');
            info.textContent = this.filtered.length ? `Página ${this.page} de ${pages} · ${this.filtered.length} registros` : 'Sin resultados';
            this.bottom.append(info);
            const nav = document.createElement('div');
            nav.className = 'paginador-buttons';
            const add = (label, page, disabled, current = false) => {
                const button = document.createElement('button');
                button.type = 'button'; button.textContent = label; button.disabled = disabled;
                if (current) button.setAttribute('aria-current', 'page');
                button.addEventListener('click', () => { this.page = page; this.render(); });
                nav.append(button);
            };
            add('‹ Anterior', this.page - 1, this.page === 1);
            for (let number = Math.max(1, this.page - 2); number <= Math.min(pages, this.page + 2); number++) add(String(number), number, number === this.page, number === this.page);
            add('Siguiente ›', this.page + 1, this.page === pages);
            this.bottom.append(nav);
        }
        exportData() {
            const columns = this.columns();
            return [columns.map(({ cell }) => cell.childNodes[0]?.textContent.trim() || cell.textContent.trim()), ...this.filtered.map(row => columns.map(({ index }) => row.cells[index]?.textContent.trim() || ''))];
        }
        exportCSV() {
            const csv = '\uFEFF' + this.exportData().map(row => row.map(value => '"' + csvCell(value).replace(/"/g, '""') + '"').join(',')).join('\r\n');
            const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
            const link = document.createElement('a'); link.href = url; link.download = (this.table.id || 'tessa-registros') + '.csv'; link.click();
            setTimeout(() => URL.revokeObjectURL(url), 1000);
        }
        async copyToClipboard() {
            const text = this.exportData().map(row => row.join('\t')).join('\n');
            try { await navigator.clipboard.writeText(text); } catch { this.top.dataset.message = 'El navegador no permitió copiar.'; }
        }
        printTable() {
            const popup = window.open('', '_blank');
            if (!popup) return;
            popup.document.title = 'Registros · Tessa';
            const style = popup.document.createElement('style');
            style.textContent = 'body{font:13px Arial;padding:20px;color:#17221a}table{width:100%;border-collapse:collapse}th,td{border:1px solid #cbd5ce;padding:8px;text-align:left}th{background:#eef2ec}';
            popup.document.head.append(style);
            const table = popup.document.createElement('table');
            this.exportData().forEach((row, index) => {
                const tr = popup.document.createElement('tr');
                row.forEach(value => { const cell = popup.document.createElement(index === 0 ? 'th' : 'td'); cell.textContent = value; tr.append(cell); });
                table.append(tr);
            });
            popup.document.body.append(table);
            popup.focus(); popup.print();
        }
    }

    if (typeof module !== 'undefined' && module.exports) module.exports = { PaginadorTablas, csvCell };
    if (root) root.PaginadorTablas = PaginadorTablas;
})(typeof window === 'undefined' ? null : window);
