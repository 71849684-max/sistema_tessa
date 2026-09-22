# Diseño técnico: Control de pagos Tessa

## Propósito y límites

Crear una aplicación PHP independiente para la operación interna de Tessa. Gestiona clientes, servicios, contratos individuales o grupales, cuotas, cobranzas, vouchers, egresos, personal, cargos, usuarios, permisos y auditoría.

No reutiliza base de datos, registros, usuarios, contraseñas, contratos, cobranzas ni egresos de Ascientifics. La importación inicial crea únicamente estructura y metadatos técnicos de autorización; no crea un administrador ni datos comerciales.

La moneda operativa será PEN y los importes se almacenarán como `DECIMAL(12,2)`. Las anulaciones son reversibles a nivel contable mediante trazabilidad: no habrá borrado físico de cobros, egresos ni contratos ya registrados.

## Arquitectura y estructura

El flujo obligatorio será:

```text
capacliente/ (vistas PHP y JavaScript) -> controllers/ -> capalogica/ (Services)
-> capaconexion/ (mysqli preparado) -> bd/tessa_control_pagos.sql (CALL sp_*) -> MariaDB
```

La aplicación no usa framework ni paquetes de aplicación. Todos los archivos PHP nuevos declaran `strict_types=1`. Las vistas no ejecutan SQL y los controladores validan entrada y autorización, delegando la regla de negocio a los Services. Las respuestas API siempre siguen:

```json
{"exito": true, "mensaje": "…", "datos": {}, "errores": {}}
```

Estructura prevista:

```text
capacliente/
  app/                 shell, dashboard, componentes y login
  ventas/              clientes, servicios, contratos, cobranzas, egresos
  rrhh/                personal, cargos y usuarios/permisos
controllers/
  core/ seguridad/ ventas/ rrhh/
capalogica/
  core/ seguridad/ ventas/ rrhh/
capaconexion/
bd/
resources/css/        hojas de estilo de la aplicación
resources/js/         JavaScript de la aplicación
assets/recursos/imagenes/  logos, favicons e imágenes de marca
assets/recursos/videos/    videos de marca
assets/recursos/fuentes/   fuentes autorizadas de marca
storage/vouchers/     fuera de las rutas públicas y único directorio escribible
scripts/              inicializador del primer administrador
config/               ejemplos no secretos
```

## Modelo de datos

Las entidades de negocio son:

- `cliente` y `servicio`: catálogos activos con desactivación lógica.
- `contrato`: número único, titular, tipo `INDIVIDUAL|GRUPAL`, servicio, monto bruto, descuento, neto, responsable, estado y motivo de anulación.
- `contrato_integrante`: clientes adicionales para contratos grupales.
- `contrato_hito`: cuota/hito único por contrato y número, fecha, importe, importe aplicado y estado `PENDIENTE|PARCIAL|PAGADO|VENCIDO|ANULADO`.
- `cobranza`, `cobranza_detalle` y `voucher`: una cobranza puede repartir el importe entre cuotas; el voucher se relaciona a la cobranza y contiene solo nombre interno, tipo, tamaño, hash y estado de validación.
- `egreso`: fecha, categoría, proveedor/beneficiario, concepto, importe, estado, motivo de anulación y usuario responsable.
- `cargo`, `personal`, `usuario`: un usuario pertenece opcionalmente a una persona; usuario y documento son únicos.
- `modulo`, `permiso`, `cargo_permiso` y `persona_permiso`: permisos por acción. `persona_permiso.permitido` permite agregar o negar sobre la plantilla del cargo. La resolución prioriza la denegación individual, después el permiso individual y finalmente el permiso de cargo.
- `login_intento` y `auditoria`: trazabilidad de acceso y de acciones sensibles, incluyendo actor, acción, entidad, IP, metadatos y antes/después cuando corresponda.

Todas las tablas operativas incluyen fechas de creación/actualización y usuario actor cuando aplica. Hay claves foráneas, índices de filtros habituales y restricciones para impedir números duplicados, pagos superiores al saldo y relaciones inconsistentes.

## Procedimientos y reglas financieras

Las operaciones de escritura se ejecutan mediante procedimientos `sp_*` y sentencias preparadas. Los procedimientos devuelven una fila estándar con `resultado`, `mensaje` e identificador cuando corresponde.

- `sp_contrato_crear`, `sp_contrato_actualizar`, `sp_contrato_anular`, `sp_contrato_listar`, `sp_contrato_detalle`.
- `sp_contrato_hito_guardar` y `sp_contrato_hito_listar` validan que los hitos activos sumen el neto contractual.
- `sp_cobranza_registrar` corre en transacción: bloquea las cuotas seleccionadas, verifica su saldo, registra cabecera/detalles, actualiza los importes aplicados y finaliza el contrato al pagarse todas sus cuotas.
- `sp_cobranza_anular` exige motivo y revierte cada detalle de la cobranza; recalcula estados de hitos y contrato; nunca borra registros.
- `sp_egreso_registrar`, `sp_egreso_listar` y `sp_egreso_anular` exigen motivo para anular y conservan el historial.
- Los procedimientos de personal, cargo, usuario y permiso impiden duplicados y registran auditoría desde la aplicación tras una operación exitosa.

Las lecturas también usan `CALL sp_*`, con filtros por fecha, estado, cliente, contrato y texto de búsqueda. Ningún importe final depende de JavaScript.

## Seguridad

La configuración se carga desde `capaconexion/datos.local.php` o variables de entorno y solo se versiona `datos.local.example.php`. `.gitignore` excluye el archivo local, logs y vouchers.

El login consulta el hash mediante `sp_login_obtener_hash`; PHP aplica `password_verify`. Un intento fallido se registra con `sp_login_registrar_intento` y tras cinco fallos dentro de quince minutos la cuenta queda bloqueada durante quince minutos. Un acceso exitoso reinicia el contador y ejecuta `session_regenerate_id(true)`.

El captcha se genera como SVG en el servidor, se guarda como token de un solo uso en sesión y se renueva por endpoint. Su valor no se expone fuera de la imagen. Las cookies de sesión serán `HttpOnly`, `SameSite=Lax` y `Secure` cuando HTTPS esté habilitado. Las vistas y endpoints verifican sesión y el permiso de acción antes de operar.

Los vouchers aceptan únicamente JPEG, PNG y PDF, con validación de MIME usando `finfo`, firma de archivo, tamaño máximo de 5 MiB, nombre aleatorio y permisos de archivo restrictivos. Se almacenan en `storage/vouchers`; la descarga pasa por controlador con permiso de lectura y cabeceras seguras. Las acciones de crear/anular contratos, cobros, egresos, usuarios y permisos se auditan.

`scripts/inicializar_admin.php` requerirá ejecución local/CLI y parámetros o entradas interactivas para nombre de usuario, contraseña y datos personales. Fallará si ya existe un administrador. Por lo tanto no habrá credenciales predeterminadas.

## Interfaz y rutas

La UI toma la fuente de exhibición suministrada en el material `2030` para títulos y `Outfit` como fuente de texto. Los tokens serán fondo tinta `#111111`, lima Tessa `#c8ff00` / `#d2ff35`, papel `#fbfcf5`, líneas suaves y texto oscuro. Los materiales se organizarán sin duplicados como sigue:

```text
assets/recursos/imagenes/  tessa-logo.png, tessa-logo-original.webp y favicon-*.png
assets/recursos/videos/    llamagozu.mp4
assets/recursos/fuentes/   Outfit y la tipografía de títulos autorizada
resources/css/             CSS propio del sistema
resources/js/              JavaScript propio del sistema
storage/vouchers/          comprobantes subidos por usuarios
```

El respaldo HEVC no se publicará: se conservará fuera de `assets` como copia técnica si fuese necesario. El logo PNG transparente será el principal para fondos claros u oscuros según contraste; el WebP se usará solo cuando su fondo lima forme parte de la composición. Los favicons se referenciarán desde el layout y el video se reservará para login/inicio, con imagen de reserva y sin reproducción obligatoria en móvil.

El shell privado tiene sidebar, cabecera de sesión, avisos accesibles y contenido principal. En móvil el sidebar se oculta y abre con botón; filtros se apilan, modales ocupan espacio seguro de pantalla y las tablas se envuelven en contenedores con desplazamiento horizontal.

Pantallas: login, inicio, clientes, servicios, contratos (con integrantes/cuotas), detalle/historial de contrato, cobranzas/vouchers, egresos, personal, cargos y matriz de permisos, usuarios y auditoría. JavaScript vanilla usa `fetch`, `FormData`, `textContent` y renderizado seguro.

## Despliegue y operación

El documento `docs/PRODUCCION.md` describirá el import SQL, archivo local de credenciales, creación del directorio no público de vouchers, propietario/permisos de escritura mínimos y configuración HTTPS para Apache. No se incluirán secretos en el repositorio. El directorio público no podrá listar ni acceder directamente a `storage`.

## Plan de verificación esencial

1. Importar `bd/tessa_control_pagos.sql` en una instancia local limpia.
2. Ejecutar `php -l` sobre todos los PHP creados.
3. Crear el primer administrador con el inicializador; comprobar captcha renovable, fallo de captcha, fallo de contraseña y login válido.
4. Crear un cliente, servicio, contrato con cuotas y cobranza parcial; comprobar la actualización del saldo.
5. Anular una cobranza con motivo y verificar reversión/historial.
6. Registrar y anular un egreso con filtros.
7. Comprobar una vista de escritorio y móvil para sidebar, filtros, tabla y modal.

Se añadirán pruebas PHP focalizadas para el cálculo de permisos efectivos, captcha y validación de archivo antes de implementar el código que cubren.
