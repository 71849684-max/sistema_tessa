# Prueba alfa de volumen — 23 de septiembre de 2026

## Entorno y aislamiento

- Base operativa preservada: `tessa_control_pagos` permanece con 2 contratos, 6 cuotas, 3 cobranzas y 2 egresos.
- Base de simulación: `tessa_control_pagos_alpha_20260923`.
- Medición local mediante cinco repeticiones por consulta; se informa la mediana. Los tiempos incluyen el inicio del cliente MySQL local, por lo que sirven como referencia comparativa y no como tiempo HTTP final.

## Datos generados

| Entidad | Registros |
| --- | ---: |
| Clientes distintos | 5,002 |
| Contratos activos | 5,002 |
| Integrantes de contratos grupales | 1,001 |
| Cuotas | 15,006 |
| Cobranzas | 2,503 |
| Detalles de cobranza | 2,503 |
| Egresos | 2,502 |

La carga contiene cuotas pagadas, parciales y pendientes; contratos individuales y grupales; pagos en efectivo; y egresos distribuidos entre enero y septiembre de 2026. La conciliación entre `contrato_hito.monto_pagado` y los detalles de cobranza devolvió **0 inconsistencias**.

El resumen para el 01/01/2026–30/09/2026 devolvió: cobrado S/ 1,091,000; egresos S/ 222,000; flujo neto S/ 869,000; proyectado S/ 13,981,200; saldo por cobrar S/ 29,509,000.

## Resultados de rendimiento

| Consulta | Mediana | Máximo |
| --- | ---: | ---: |
| Búsqueda por número de contrato | 53.1 ms | 59.8 ms |
| Búsqueda por DNI | 53.4 ms | 57.6 ms |
| Búsqueda por apellido | 53.7 ms | 61.5 ms |
| Dashboard: resumen | 209.4 ms | 213.3 ms |
| Dashboard: evolución | 62.7 ms | 68.1 ms |
| Dashboard: estado de cuotas | 59.0 ms | 65.8 ms |
| Dashboard: ventas por servicio | 56.3 ms | 62.3 ms |
| Dashboard: alertas | 48.6 ms | 52.7 ms |
| Listado de cobranzas | 63.6 ms | 77.7 ms |
| Listado de contratos | 227.9 ms | 236.9 ms |

## Límites observados

1. **Listados sin paginación.** `sp_contrato_listar()` devolvió las 5,002 filas en una sola respuesta (aprox. 991 KiB). El listado de cobranzas devolvió 1,091 filas (aprox. 195 KiB). Es el primer límite funcional: aun con una base pequeña local funciona, pero la interfaz y la red se degradarán al crecer.
2. **Resumen del dashboard.** Es la consulta más costosa del panel (209 ms en esta prueba). Sigue siendo usable localmente, pero requiere índices orientados al filtro de fecha/estado antes de aumentar el volumen en un orden de magnitud.
3. **Índices de cuotas.** `contrato_hito` solo tiene la clave única `(id_contrato, numero)`. Las consultas analíticas filtran por `fecha_vencimiento` y `estado`; faltan índices que apoyen esa ruta.

## Recomendaciones priorizadas

1. Paginar por servidor los procedimientos `sp_contrato_listar` y `sp_cobranza_listar`; el cliente no debe recibir miles de filas para mostrar una página de 10.
2. Añadir un índice compuesto para análisis de cuotas, validándolo con una prueba antes/después: `(fecha_vencimiento, estado, id_contrato)`.
3. Mantener el dashboard en consultas agregadas, como está, y limitar alertas a los registros necesarios.
4. Repetir la prueba con 50,000 contratos antes de producción o cuando se habiliten múltiples sedes/usuarios concurrentes.

## Reproducción

1. Crear una copia de `tessa_control_pagos` con el nombre indicado.
2. Ejecutar [alpha_seed.sql](../tests/alpha_seed.sql).
3. Ejecutar [alpha_enrich_clients.sql](../tests/alpha_enrich_clients.sql).

Los scripts solo deben ejecutarse en una base de simulación, nunca en la base operativa.
