# Producción

Importe bd/tessa_control_pagos.sql y copie capaconexion/datos.local.example.php como datos.local.php, sin publicar ese archivo.
En instalaciones que ya existían antes del dashboard, importe además `bd/actualizar_dashboard_ventas.sql`; la migración agrega el permiso y las consultas sin eliminar usuarios ni movimientos.
Para habilitar la matriz de permisos de las nuevas vistas en una instalación existente, importe `bd/actualizar_permisos_vistas.sql`. Esta migración solo agrega procedimientos de consulta y de retiro de excepciones individuales; no modifica los registros existentes.
Para la búsqueda de clientes por DNI, nombres y apellidos en una instalación existente, importe una vez `bd/actualizar_clientes_busqueda.sql`. Agrega la columna `apellidos` y actualiza los procedimientos sin eliminar registros. El esquema completo ya incluye estos cambios para instalaciones nuevas.
Para validar integrantes activos y evitar que un contrato grupal guarde integrantes inexistentes, importe `bd/actualizar_integrantes_contrato.sql` en instalaciones existentes. Solo reemplaza el procedimiento correspondiente y conserva los datos.
La consulta DNI usa un endpoint propio de Tessa (`controllers/api/reniec_controller.php`) y requiere `TESSA_RENIEC_TOKEN` con un acuerdo privado del proveedor. No copie credenciales de Ascientifics. Sin token o si el proveedor falla, el formulario permite capturar nombres y apellidos manualmente. Configure el token fuera de Git, limite quién puede acceder al endpoint y mantenga HTTPS.
Cree el primer administrador solo por consola y entregue la contraseña por la entrada estándar, para no exponerla en el historial de procesos: `password | php scripts/inicializar_admin.php --password-stdin usuario nombres apellidos dni8`. No hay credenciales predeterminadas.
El proceso web solo debe escribir en storage/vouchers (0750); sus archivos deben ser 0640. Apache debe denegar el acceso directo a storage.
Active HTTPS, redireccione HTTP y use cookies Secure, HttpOnly y SameSite=Lax. Desactive display_errors y almacene logs fuera de la ruta pública.
