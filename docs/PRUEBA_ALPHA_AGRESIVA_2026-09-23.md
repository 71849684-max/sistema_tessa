# Prueba alfa agresiva — 23 de septiembre de 2026

## Base de revisión

- Base: `tessa_control_pagos_alpha_agresiva_20260923`.
- La aplicación local está configurada temporalmente para esta base hasta que el usuario solicite retirarla.
- La base operativa `tessa_control_pagos` no fue modificada.

## Volumen generado

| Entidad | Total |
| --- | ---: |
| Clientes | 40,002 |
| Contratos | 20,003 |
| Cuotas | 60,007 |
| Cobranzas y detalles | 76,008 |
| Egresos | 10,002 |
| Integrantes grupales | 18,001 |

Los contratos generados incluyen 8,001 individuales, 6,000 grupos de dos integrantes y 6,001 grupos de tres integrantes. Cada grupo fracciona sus pagos por cuota entre sus integrantes; la primera cuota queda pagada, la segunda queda parcial y la tercera queda pendiente. También se anularon 206 contratos de prueba, preservando sus pagos y cancelando cuotas sin pago.

## Integridad validada

- 0 cuotas con pago distinto de la suma de sus detalles.
- 0 pagos realizados por una persona ajena a su contrato.
- 0 cuotas sobrepagadas.
- Prueba concurrente: 8 intentos simultáneos de S/ 200 sobre una cuota grupal de S/ 1,000; 5 se registraron y 3 fueron rechazados por saldo insuficiente. El saldo final fue exactamente S/ 0.

## Rendimiento local (mediana de tres ejecuciones)

| Consulta | Mediana |
| --- | ---: |
| Dashboard: resumen | 748.6 ms |
| Dashboard: evolución | 288.3 ms |
| Búsqueda por contrato | 115.3 ms |
| Listado de contratos sin paginar | 913.6 ms |
| Listado de cobranzas sin paginar | 1,536.4 ms |

## Hallazgo principal

El cuello de botella ya es evidente: contratos y cobranzas no se paginan en el servidor. La simulación se mantiene intencionalmente activa para revisar visualmente este comportamiento. La primera mejora a implementar deberá ser paginación del lado servidor, seguida de índices para consultas analíticas por fecha y estado de cuota.

## Monitoreo

Existe un monitoreo periódico de solo lectura sobre esta base. Revisa disponibilidad, integridad de pagos, pertenencia de pagadores, sobrepagos y tiempo del resumen del dashboard. Solo notificará si detecta un problema.

## Script de carga

La carga es reproducible mediante [alpha_agresiva_seed.sql](../tests/alpha_agresiva_seed.sql) y debe ejecutarse exclusivamente en una base de simulación.
