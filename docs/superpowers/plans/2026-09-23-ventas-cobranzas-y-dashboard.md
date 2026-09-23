# Ventas, cobranzas grupales y dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Hacer trazables los integrantes y el pagador de contratos grupales, permitir anular contratos sin borrar cobros históricos, y convertir el dashboard en una herramienta operativa con indicadores y gráfica temporal.

**Architecture:** La base de datos conserva la fuente de verdad: el pagador se almacena en cada cobranza y la anulación cambia únicamente cuotas sin cobro a `CANCELADO`; las cobranzas registradas y sus detalles se preservan como historial. Los servicios exponen esos datos a vistas de contratos/cobranzas y al dashboard. La interfaz usará JavaScript y SVG nativo para gráficos, sin incorporar una librería externa.

**Tech Stack:** PHP 8, MySQL 8 stored procedures, HTML/CSS, JavaScript nativo, SVG, pruebas PHP y `node:test`.

**Spec:** Requisitos solicitados el 23/09/2026 en esta conversación.

## Global Constraints

- El pagador de un contrato individual es siempre el titular; en uno grupal debe elegirse entre titular e integrantes activos.
- Al anular un contrato, las cobranzas `REGISTRADO`, sus detalles y vouchers no se modifican ni se anulan automáticamente.
- Las cuotas sin importe pagado se marcan `CANCELADO`; las cuotas con pagos conservan su importe y estado de cobro, pero no vuelven a ser cobrables porque el contrato queda `ANULADO`.
- `EFECTIVO` es el medio por defecto y no requiere número de operación ni voucher; los demás medios sí requieren ambos datos.
- El dashboard excluye contratos anulados de proyecciones y saldos, pero puede informar los cobros históricos dentro del período mediante una métrica separada si se requiere auditoría.
- No añadir dependencias de gráficos; el gráfico lineal se renderiza con SVG accesible.

## Review Focus

- Un integrante no perteneciente al contrato o inactivo nunca puede ser enviado como pagador.
- Un contrato anulado nunca reaparece en la búsqueda, cuota pendiente o saldo por cobrar.
- Una cobranza existente sigue visible y su voucher sigue consultable después de anular su contrato.
- Efectivo no sube ni deja un voucher residual; transferencia/Yape/Plin/Tarjeta no se registra sin número ni voucher.
- Un rango sin cobros muestra una línea de valor cero y proyección, no una tarjeta vacía sin información.

---

### Task 1: Modelo de datos y reglas de anulación

**Files:**
- Create: `bd/actualizar_cobranza_grupal_y_anulacion.sql`
- Modify: `bd/tessa_control_pagos.sql`
- Test: `tests/SmokeDatabaseTest.php`

**Interfaces:**
- Produces `cobranza.id_cliente_pagador NULL`, `sp_contrato_participantes_pago(IN p_contrato INT)` y procedimientos de cobranza/anulación actualizados.
- Consumes `contrato.id_cliente` y `contrato_integrante.id_cliente`.

- [ ] **Step 1: Write the failing database regression cases**

  Extender `SmokeDatabaseTest.php` con un contrato grupal de titular `1` e integrante `2`: registrar un pago de `2`, anular el contrato y comprobar que la cobranza, su detalle y voucher aún existen; comprobar que una cuota sin pagos queda `CANCELADO` y que el contrato ya no tiene saldo cobrable.

- [ ] **Step 2: Run the database test to verify it fails**

  Run: `TESSA_SMOKE_DB=1 php tests/SmokeDatabaseTest.php`

  Expected: fallo porque no existe `id_cliente_pagador`, el contrato no permite anulación con cobranzas o la cuota proyectada no queda cancelada.

- [ ] **Step 3: Add the migration and update the base schema**

  Añadir `id_cliente_pagador INT UNSIGNED NULL` con FK a `cliente`; permitir `CANCELADO` en `contrato_hito.estado`; recrear `sp_contrato_anular` para bloquear el contrato, cancelar solo hitos con `monto_pagado=0`, y no modificar `cobranza`, `cobranza_detalle` ni `voucher`. Crear `sp_contrato_participantes_pago` que devuelva titular e integrantes, con etiqueta y documento.

- [ ] **Step 4: Make payment registration enforce the payer**

  Cambiar `sp_cobranza_registrar` para recibir `p_pagador`; validar que sea titular para contrato individual o titular/integrante activo para contrato grupal; guardar `id_cliente_pagador`. Para efectivo, exigir operación y datos de voucher vacíos; para los otros medios exigir operación y voucher válido.

- [ ] **Step 5: Run the database regression test to verify it passes**

  Run: `TESSA_SMOKE_DB=1 php tests/SmokeDatabaseTest.php`

  Expected: PASS; los cobros históricos permanecen y las cuotas proyectadas no aparecen como cobrables.

- [ ] **Step 6: Commit**

  ```powershell
  git add bd/tessa_control_pagos.sql bd/actualizar_cobranza_grupal_y_anulacion.sql tests/SmokeDatabaseTest.php
  git commit -m "feat: preserve payments when voiding contracts"
  ```

### Task 2: Servicios y datos visibles de contratos/cobranzas

**Files:**
- Modify: `capalogica/ventas/ContratoService.php`
- Modify: `capalogica/ventas/CobranzaService.php`
- Modify: `controllers/ventas/cobranza_controller.php`
- Modify: `controllers/ventas/voucher_controller.php`
- Modify: `bd/actualizar_ventas_avanzadas.sql`
- Test: `tests/DomainServiceTest.php`
- Test: `tests/ContratoEdicionWiringTest.php`

**Interfaces:**
- Consumes `sp_contrato_participantes_pago`, `sp_cobranza_registrar(..., p_pagador, ...)` y los listados enriquecidos.
- Produces `CobranzaService::participantesPago(int $idContrato): array` y un campo `id_cliente_pagador` validado en `CobranzaService::registrar()`.

- [ ] **Step 1: Write failing service tests**

  En `DomainServiceTest.php`, simular participantes de un contrato grupal y comprobar que `registrar()` rechaza un pagador `0` o ajeno, acepta al integrante devuelto por el procedimiento y envía `EFECTIVO` con operación/voucher vacíos. En `ContratoEdicionWiringTest.php`, comprobar que el detalle/listado declara integrantes y etiquetas de participantes.

- [ ] **Step 2: Run the focused tests to verify they fail**

  Run: `php tests/DomainServiceTest.php; php tests/ContratoEdicionWiringTest.php`

  Expected: FAIL por inexistencia del método, parámetro o datos de pagador.

- [ ] **Step 3: Implement the service boundary**

  Agregar `participantesPago()` a `CobranzaService`; validar localmente que exista contrato, fecha, detalle, medio permitido y pagador cuando el backend lo solicite. Pasar el pagador al procedimiento. En listados/voucher, exponer `pagador_nombre` y el documento del pagador para que ventas y cobranzas identifiquen a cada involucrado.

- [ ] **Step 4: Keep contract detail complete**

  Asegurar que los procedimientos de detalle y listado de contrato incluyan `tipo`, titular e integrantes como arreglo JSON y etiqueta legible. Mantener los contratos anulados en historial, pero fuera de la búsqueda de cobranza y de cuotas activas.

- [ ] **Step 5: Run focused tests to verify they pass**

  Run: `php tests/DomainServiceTest.php; php tests/ContratoEdicionWiringTest.php`

  Expected: PASS.

- [ ] **Step 6: Commit**

  ```powershell
  git add capalogica/ventas/ContratoService.php capalogica/ventas/CobranzaService.php controllers/ventas/cobranza_controller.php controllers/ventas/voucher_controller.php bd/actualizar_ventas_avanzadas.sql tests/DomainServiceTest.php tests/ContratoEdicionWiringTest.php
  git commit -m "feat: expose group members and payment payer"
  ```

### Task 3: Formulario compacto de cobranza y pagador grupal

**Files:**
- Modify: `capacliente/ventas/cobranzas.php`
- Modify: `resources/js/cobranzas.js`
- Modify: `resources/css/vistas.css`
- Test: `tests/CobranzasFormularioTest.js`

**Interfaces:**
- Consumes `accion=participantes` del controlador y filas `{id_cliente, etiqueta}`.
- Produces el campo `id_cliente_pagador`, y los campos de operación/voucher correctos según `medio_pago`.

- [ ] **Step 1: Write the failing browser-unit tests**

  Crear `CobranzasFormularioTest.js` para probar una función exportada `requisitosMedio(medio)` con resultados literales: `EFECTIVO` devuelve `{requiereOperacion:false, requiereVoucher:false}`; `TRANSFERENCIA` devuelve ambos `true`. Probar que `opcionesPagador('GRUPAL', participantes)` contiene titular e integrantes, y para `INDIVIDUAL` no expone selector.

- [ ] **Step 2: Run the JS test to verify it fails**

  Run: `node tests/CobranzasFormularioTest.js`

  Expected: FAIL porque las funciones y comportamiento todavía no existen.

- [ ] **Step 3: Reorganize markup and defaults**

  En `cobranzas.php`, cambiar el select para que `EFECTIVO` sea la primera opción seleccionada. Agrupar operación y voucher en un contenedor `data-comprobante-campos`; añadir un contenedor `data-pagador-grupal` oculto con `select name="id_cliente_pagador"`. Mantener contrato, fecha y acción de cargar cuotas en la primera fila compacta.

- [ ] **Step 4: Implement the dynamic form behavior**

  En `cobranzas.js`, al seleccionar contrato consultar participantes; mostrar el selector solo si `tipo==='GRUPAL'`, precargar titular como opción marcada y limpiar el valor al cambiar de contrato. Escuchar cambios de medio: con efectivo ocultar, deshabilitar y vaciar número/voucher; con otro medio mostrar, habilitar y marcar requeridos. Evitar subir archivo al enviar efectivo.

- [ ] **Step 5: Remove wasted input space without harming responsive layout**

  En `vistas.css`, usar columnas de grilla para contrato/cargar cuotas/fecha/medio/pagador; hacer operación y voucher ocupen una columna normal en escritorio y el ancho completo solo en móvil. Preservar foco visible, etiquetas y orden lógico de teclado.

- [ ] **Step 6: Run browser-unit test and static wiring test**

  Run: `node tests/CobranzasFormularioTest.js; node --check resources/js/cobranzas.js; php tests/ContratoEdicionWiringTest.php`

  Expected: PASS.

- [ ] **Step 7: Commit**

  ```powershell
  git add capacliente/ventas/cobranzas.php resources/js/cobranzas.js resources/css/vistas.css tests/CobranzasFormularioTest.js
  git commit -m "feat: streamline cash and group payment entry"
  ```

### Task 4: Consultas analíticas del dashboard

**Files:**
- Create: `bd/actualizar_dashboard_ventas_analitico.sql`
- Modify: `bd/tessa_control_pagos.sql`
- Modify: `capalogica/ventas/DashboardVentasService.php`
- Test: `tests/DashboardVentasTest.php`

**Interfaces:**
- Produces `sp_dashboard_ventas_evolucion_diaria(desde,hasta)` con `{fecha, proyectado, cobrado}` incluyendo días sin cobro; `sp_dashboard_ventas_alertas(desde,hasta)` con vencidas, próximas 7 días y saldo; y resumen con `vencido`.
- Consumes contratos activos/finalizados, hitos no cancelados y cobranzas registradas.

- [ ] **Step 1: Write failing dashboard-service tests**

  Crear `DashboardVentasTest.php` con una conexión falsa que devuelva una serie diaria y alertas. Comprobar que `datos()` devuelve las claves `evolucion`, `alertas`, `servicios`, `pendientes` y `resumen.vencido`, y que una serie sin cobros conserva fechas/proyección.

- [ ] **Step 2: Run the test to verify it fails**

  Run: `php tests/DashboardVentasTest.php`

  Expected: FAIL porque el servicio solo consulta evolución de cobros.

- [ ] **Step 3: Add analytics procedures**

  Crear un calendario temporal/CTE recursivo para el rango, unir cuotas por vencimiento y cobranzas por fecha, omitir contratos `ANULADO` y cuotas `CANCELADO`. Añadir métricas de saldo vencido y alertas ordenadas por riesgo: vencidas, vencen en 7 días y cobranza parcial.

- [ ] **Step 4: Extend the dashboard service**

  Hacer que `DashboardVentasService::datos()` llame los nuevos procedimientos y devuelva una estructura estable, incluso si los valores son cero.

- [ ] **Step 5: Run the test to verify it passes**

  Run: `php tests/DashboardVentasTest.php`

  Expected: PASS.

- [ ] **Step 6: Commit**

  ```powershell
  git add bd/tessa_control_pagos.sql bd/actualizar_dashboard_ventas_analitico.sql capalogica/ventas/DashboardVentasService.php tests/DashboardVentasTest.php
  git commit -m "feat: add actionable sales analytics"
  ```

### Task 5: Dashboard visual y validación integral

**Files:**
- Modify: `capacliente/ventas/dashboard.php`
- Modify: `resources/js/dashboard-ventas.js`
- Modify: `resources/css/vistas.css`
- Test: `tests/DashboardVentasVisualTest.js`
- Test: `tests/SmokeDatabaseTest.php`

**Interfaces:**
- Consumes `{resumen, evolucion, alertas, servicios, pendientes}` del dashboard controller existente.
- Produces una línea SVG de proyección/cobrado, KPIs de cobrado, proyectado, efectividad, vencido y contratos activos, más panel de alertas accionables.

- [ ] **Step 1: Write the failing renderer tests**

  Crear `DashboardVentasVisualTest.js` para una función exportada `puntosLinea(serie, ancho, alto)`: con tres fechas y valores `[0, 100, 0]` debe devolver tres puntos dentro del lienzo; con serie de ceros no debe producir `NaN`. Probar que `clasificarAlertas()` pone primero las cuotas vencidas.

- [ ] **Step 2: Run the renderer test to verify it fails**

  Run: `node tests/DashboardVentasVisualTest.js`

  Expected: FAIL porque no existen los renderizadores SVG ni clasificación.

- [ ] **Step 3: Replace the empty evolution panel**

  En `dashboard.php`, crear contenedores semánticos para gráfico SVG, leyenda “Proyectado/Cobrado”, KPI de vencido y alertas. En `dashboard-ventas.js`, renderizar dos polilíneas sobre el mismo rango; incluir valores de cero, etiquetas inicial/final y texto alternativo con totales para lectores de pantalla.

- [ ] **Step 4: Make the dashboard actionable**

  Mostrar alertas con contrato, involucrado responsable, cuota, fecha, saldo y enlace directo a Cobranzas con el contrato preseleccionado. Mantener servicios como comparación contratado/cobrado y el embudo de estados para lectura rápida.

- [ ] **Step 5: Run focused and full verification**

  Run: `node tests/DashboardVentasVisualTest.js; php tests/DashboardVentasTest.php; $env:TESSA_SMOKE_DB='1'; php tests/SmokeDatabaseTest.php; Get-ChildItem tests -Filter '*Test.php' | ForEach-Object { php $_.FullName }; Get-ChildItem tests -Filter '*Test.js' | ForEach-Object { node $_.FullName }`

  Expected: todos los tests pasan; no hay cuotas canceladas en proyecciones ni contratos anulados en cobranza.

- [ ] **Step 6: Commit**

  ```powershell
  git add capacliente/ventas/dashboard.php resources/js/dashboard-ventas.js resources/css/vistas.css tests/DashboardVentasVisualTest.js tests/SmokeDatabaseTest.php
  git commit -m "feat: visualize sales performance and collection risk"
  ```

## Self-review

- Cobertura: contratos grupales, identificación del pagador, anulación con histórico, formulario de efectivo, ajuste de espacio y dashboard analítico están cubiertos por las tareas 1 a 5.
- Invariantes: los pagos registrados no se editan durante la anulación; las cuotas proyectadas no siguen cobrables; el pagador se valida en base de datos, no solo en la interfaz.
- Riesgos cubiertos: contratos individuales, integrante inválido, efectivo, rangos sin cobros y contratos anulados cuentan con pruebas explícitas.
