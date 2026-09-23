'use strict';

const puntosLinea = (serie, clave, ancho, alto, maximo = null) => {
    const max = maximo ?? Math.max(1, ...serie.map(fila => Number(fila[clave] || 0)));
    const divisor = Math.max(1, serie.length - 1);
    return serie.map((fila, indice) => ({ x: indice * ancho / divisor, y: alto - Number(fila[clave] || 0) / max * alto }));
};
const acumularSerie = serie => serie.reduce((acumulada, fila) => {
    const anterior = acumulada.at(-1) || { proyectado: 0, cobrado: 0 };
    acumulada.push({ ...fila, proyectado: anterior.proyectado + Number(fila.proyectado || 0), cobrado: anterior.cobrado + Number(fila.cobrado || 0) });
    return acumulada;
}, []);
const puntosComparables = (serie, claves, ancho, alto) => {
    const maximo = Math.max(1, ...serie.flatMap(fila => claves.map(clave => Number(fila[clave] || 0))));
    return Object.fromEntries(claves.map(clave => [clave, puntosLinea(serie, clave, ancho, alto, maximo)]));
};
const reducirSerieVisual = serie => serie.filter((fila, indice) => indice === 0 || indice === serie.length - 1 || Number(fila.proyectado || 0) !== 0 || Number(fila.cobrado || 0) !== 0);
const agruparPorMes = serie => {
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const grupos = new Map();
    serie.forEach(fila => {
        const clave = String(fila.fecha || '').slice(0, 7);
        if (!/^\d{4}-\d{2}$/.test(clave)) return;
        const grupo = grupos.get(clave) || { etiqueta: meses[Number(clave.slice(5)) - 1], cobrado: 0, proyectado: 0 };
        grupo.cobrado += Number(fila.cobrado || 0);
        grupo.proyectado += Number(fila.proyectado || 0);
        grupos.set(clave, grupo);
    });
    return [...grupos.values()].map(grupo => ({
        ...grupo,
        pendiente: Math.max(0, grupo.proyectado - grupo.cobrado)
    }));
};
const clasificarAlertas = filas => [...filas].sort((a, b) => (a.estado === 'VENCIDO' ? 0 : 1) - (b.estado === 'VENCIDO' ? 0 : 1));

if (typeof module !== 'undefined' && module.exports) module.exports = { puntosLinea, acumularSerie, puntosComparables, reducirSerieVisual, agruparPorMes, clasificarAlertas };

if (typeof document !== 'undefined') (() => {
    const root = document.querySelector('[data-sales-dashboard]');
    if (!root) return;
    const ns = 'http://www.w3.org/2000/svg';
    const money = valor => new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN', maximumFractionDigits: 0 }).format(Number(valor || 0));
    const vacio = (selector, mostrar) => { const nodo = root.querySelector(selector); if (nodo) nodo.hidden = !mostrar; };
    const svgNodo = (nombre, atributos = {}) => {
        const nodo = document.createElementNS(ns, nombre);
        Object.entries(atributos).forEach(([clave, valor]) => nodo.setAttribute(clave, String(valor)));
        return nodo;
    };

    const dibujarLinea = filas => {
        const svg = root.querySelector('[data-evolucion-linea]');
        if (!svg) return;
        svg.replaceChildren();
        const serie = reducirSerieVisual(acumularSerie(filas));
        if (!serie.length) {
            const texto = svgNodo('text', { x: 340, y: 130, 'text-anchor': 'middle', class: 'chart-empty-text' });
            texto.textContent = 'Sin movimientos en el período seleccionado';
            svg.append(texto);
            return;
        }
        const marco = { ancho: 680, alto: 260, izquierda: 52, derecha: 14, arriba: 16, abajo: 34 };
        const ancho = marco.ancho - marco.izquierda - marco.derecha;
        const alto = marco.alto - marco.arriba - marco.abajo;
        const maximo = Math.max(1, ...serie.flatMap(fila => [Number(fila.proyectado || 0), Number(fila.cobrado || 0)]));
        const puntos = Object.fromEntries(['proyectado', 'cobrado'].map(clave => [clave, puntosLinea(serie, clave, ancho, alto, maximo).map(punto => ({ x: punto.x + marco.izquierda, y: punto.y + marco.arriba }))]));
        [0, .25, .5, .75, 1].forEach(fraccion => {
            const y = marco.arriba + alto - alto * fraccion;
            svg.append(svgNodo('line', { x1: marco.izquierda, x2: marco.ancho - marco.derecha, y1: y, y2: y, class: 'chart-gridline' }));
            const etiqueta = svgNodo('text', { x: marco.izquierda - 8, y: y + 4, 'text-anchor': 'end', class: 'chart-axis-label' });
            etiqueta.textContent = money(maximo * fraccion);
            svg.append(etiqueta);
        });
        serie.forEach((fila, indice) => {
            if (!(serie.length <= 5 || indice === 0 || indice === serie.length - 1 || indice % Math.ceil(serie.length / 5) === 0)) return;
            const etiqueta = svgNodo('text', { x: puntos.cobrado[indice].x, y: marco.alto - 7, 'text-anchor': 'middle', class: 'chart-axis-label' });
            etiqueta.textContent = String(fila.fecha || '').slice(5).replace('-', '/');
            svg.append(etiqueta);
        });
        [['proyectado', '#f59e0b'], ['cobrado', '#00a66a']].forEach(([clave, color]) => {
            svg.append(svgNodo('polyline', { points: puntos[clave].map(punto => `${punto.x},${punto.y}`).join(' '), fill: 'none', stroke: color, 'stroke-width': 3.5, 'stroke-linejoin': 'round', 'stroke-linecap': 'round' }));
            puntos[clave].forEach((punto, indice) => {
                if (serie.length <= 8 || indice === 0 || indice === serie.length - 1) svg.append(svgNodo('circle', { cx: punto.x, cy: punto.y, r: 4, fill: color }));
            });
        });
    };

    const dibujarBarras = (selector, filas, clave, etiqueta) => {
        const destino = root.querySelector(selector);
        if (!destino) return;
        destino.replaceChildren();
        const maximo = Math.max(1, ...filas.map(fila => Number(fila[clave] || 0)));
        filas.slice(0, 6).forEach(fila => {
            const filaBarra = document.createElement('div'); filaBarra.className = 'bar-row';
            const titulo = document.createElement('span'); titulo.textContent = etiqueta(fila);
            const riel = document.createElement('div'); riel.className = 'bar-rail';
            const relleno = document.createElement('i'); relleno.style.width = `${Math.max(4, Number(fila[clave] || 0) / maximo * 100)}%`; riel.append(relleno);
            const valor = document.createElement('strong'); valor.textContent = money(fila[clave]);
            filaBarra.append(titulo, riel, valor); destino.append(filaBarra);
        });
    };

    const dibujarEstadoCuotas = filas => {
        const pila = root.querySelector('[data-embudo-stack]');
        const detalle = root.querySelector('[data-embudo]');
        const totalNodo = root.querySelector('[data-cuotas-total]');
        const total = filas.reduce((suma, fila) => suma + Number(fila.cantidad || 0), 0);
        if (totalNodo) totalNodo.textContent = total ? `Total: ${total} cuota${total === 1 ? '' : 's'}` : '';
        if (!pila || !detalle) return;
        pila.replaceChildren(); detalle.replaceChildren();
        const colores = { PAGADO: 'paid', PARCIAL: 'partial', PENDIENTE: 'pending', VENCIDO: 'overdue' };
        filas.forEach(fila => {
            const estado = String(fila.estado || 'PENDIENTE').toUpperCase();
            const clase = colores[estado] || 'pending';
            const porcentaje = total ? Number(fila.cantidad || 0) / total * 100 : 0;
            const tramo = document.createElement('span');
            tramo.className = `quota-segment is-${clase}`;
            tramo.style.width = `${porcentaje}%`;
            tramo.title = `${estado}: ${fila.cantidad}`;
            if (porcentaje >= 12) tramo.textContent = `${Math.round(porcentaje)}%`;
            pila.append(tramo);

            const item = document.createElement('div'); item.className = 'quota-detail';
            const punto = document.createElement('i'); punto.className = `is-${clase}`;
            const etiqueta = document.createElement('span'); etiqueta.textContent = estado[0] + estado.slice(1).toLowerCase();
            const cantidad = document.createElement('strong'); cantidad.textContent = String(fila.cantidad || 0);
            item.append(punto, etiqueta, cantidad); detalle.append(item);
        });
    };

    const dibujarServicios = filas => {
        const destino = root.querySelector('[data-servicios]');
        if (!destino) return;
        destino.replaceChildren();
        const maximo = Math.max(1, ...filas.flatMap(fila => [Number(fila.contratado || 0), Number(fila.cobrado || 0)]));
        filas.slice(0, 6).forEach(fila => {
            const grupo = document.createElement('div'); grupo.className = 'service-group';
            const titulo = document.createElement('span'); titulo.textContent = fila.nombre;
            const barras = document.createElement('div'); barras.className = 'service-pair';
            ['contratado', 'cobrado'].forEach(clave => {
                const columna = document.createElement('div'); columna.className = `service-column is-${clave}`;
                const monto = document.createElement('strong'); monto.textContent = money(fila[clave]);
                const barra = document.createElement('i'); barra.style.height = `${Math.max(4, Number(fila[clave] || 0) / maximo * 100)}%`;
                columna.append(monto, barra); barras.append(columna);
            });
            grupo.append(titulo, barras); destino.append(grupo);
        });
    };

    const dibujarFlujoCaja = resumen => {
        const destino = root.querySelector('[data-flujo-caja]');
        if (!destino) return;
        destino.replaceChildren();
        const filas = [
            { etiqueta: 'Cobrado', valor: Number(resumen.cobrado || 0), clase: 'income' },
            { etiqueta: 'Egresos', valor: Number(resumen.egresos || 0), clase: 'expense' },
            { etiqueta: 'Neto', valor: Number(resumen.neto || 0), clase: 'net' }
        ];
        const maximo = Math.max(1, ...filas.map(fila => Math.abs(fila.valor)));
        filas.forEach(fila => {
            const grupo = document.createElement('div'); grupo.className = `cashflow-item is-${fila.clase}`;
            const valor = document.createElement('strong'); valor.textContent = money(fila.valor);
            const rail = document.createElement('div'); rail.className = 'cashflow-rail';
            const barra = document.createElement('i'); barra.style.height = `${Math.max(4, Math.abs(fila.valor) / maximo * 100)}%`; rail.append(barra);
            const etiqueta = document.createElement('span'); etiqueta.textContent = fila.etiqueta;
            grupo.append(valor, rail, etiqueta); destino.append(grupo);
        });
    };

    const dibujarCobranzaMensual = filas => {
        const destino = root.querySelector('[data-cobranza-mensual]');
        if (!destino) return;
        destino.replaceChildren();
        const meses = agruparPorMes(filas);
        const maximo = Math.max(1, ...meses.flatMap(fila => [fila.cobrado, fila.pendiente]));
        meses.forEach(fila => {
            const grupo = document.createElement('div'); grupo.className = 'monthly-group';
            const barras = document.createElement('div'); barras.className = 'monthly-pair';
            ['cobrado', 'pendiente'].forEach(clave => {
                const columna = document.createElement('div'); columna.className = `monthly-column is-${clave}`;
                const monto = document.createElement('strong'); monto.textContent = money(fila[clave]);
                const valor = Number(fila[clave] || 0);
                const barra = document.createElement('i');
                barra.style.height = valor > 0 ? `${Math.max(4, valor / maximo * 100)}%` : '0';
                if (valor === 0) barra.style.minHeight = '0';
                columna.append(monto, barra); barras.append(columna);
            });
            const etiqueta = document.createElement('span'); etiqueta.textContent = fila.etiqueta;
            grupo.append(barras, etiqueta); destino.append(grupo);
        });
        vacio('[data-mensual-empty]', meses.length === 0 || meses.every(fila => fila.cobrado === 0 && fila.proyectado === 0));
    };

    const cargar = async datos => {
        try {
            const respuesta = await fetch(`${root.dataset.controller}?${new URLSearchParams(datos)}`, { credentials: 'same-origin' });
            const json = await respuesta.json();
            if (!json.exito) throw new Error('Respuesta no válida');
            const datosDashboard = json.datos;
            const resumen = datosDashboard.resumen || {};
            ['cobrado', 'proyectado', 'egresos', 'neto'].forEach(clave => {
                const nodo = root.querySelector(`[data-kpi="${clave}"]`);
                if (nodo) nodo.textContent = money(resumen[clave]);
            });
            const saldo = root.querySelector('[data-kpi-saldo]'); if (saldo) saldo.textContent = money(resumen.por_cobrar || 0);
            const cumplimiento = root.querySelector('[data-cumplimiento]');
            if (cumplimiento) cumplimiento.textContent = `${Number(resumen.efectividad || 0).toFixed(0)}% de la meta del período`;
            const evolucion = datosDashboard.evolucion || [];
            dibujarFlujoCaja(resumen);
            const total = root.querySelector('[data-flujo-total]'); if (total) total.textContent = money(resumen.neto || 0);
            const embudo = datosDashboard.embudo || []; dibujarEstadoCuotas(embudo); vacio('[data-embudo-empty]', embudo.length === 0);
            const servicios = datosDashboard.servicios || []; dibujarServicios(servicios); vacio('[data-servicios-empty]', servicios.length === 0);
            dibujarCobranzaMensual(evolucion);
            const alertas = root.querySelector('[data-alertas]');
            if (alertas) {
                alertas.replaceChildren();
                clasificarAlertas(datosDashboard.alertas || []).slice(0, 4).forEach(fila => {
                    const elemento = document.createElement('li');
                    const contrato = document.createElement('strong'); contrato.textContent = fila.con_numero;
                    const detalle = document.createElement('span'); detalle.textContent = `${fila.descripcion} · ${money(fila.saldo)}`;
                    elemento.append(contrato, detalle); alertas.append(elemento);
                });
                vacio('[data-alertas-empty]', alertas.childElementCount === 0);
            }
        } catch {
            root.querySelectorAll('.empty-state').forEach(nodo => { nodo.hidden = false; nodo.textContent = 'No se pudo cargar la información del dashboard.'; });
        }
    };

    const filtros = root.querySelector('[data-dashboard-filters]');
    const periodo = root.querySelector('[data-dashboard-period]');
    periodo?.addEventListener('change', () => {
        if (periodo.value === 'custom') return;
        const hoy = new Date();
        const inicio = new Date(hoy);
        if (periodo.value === 'month') inicio.setDate(1);
        if (periodo.value === 'quarter') inicio.setMonth(hoy.getMonth() - hoy.getMonth() % 3, 1);
        if (periodo.value === 'year') inicio.setMonth(0, 1);
        const fecha = valor => valor.toISOString().slice(0, 10);
        filtros.elements.desde.value = fecha(inicio);
        filtros.elements.hasta.value = fecha(hoy);
        cargar(Object.fromEntries(new FormData(filtros)));
    });
    filtros?.addEventListener('submit', evento => { evento.preventDefault(); cargar(Object.fromEntries(new FormData(filtros))); });
    cargar(Object.fromEntries(new FormData(filtros)));
})();
