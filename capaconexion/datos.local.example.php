<?php
declare(strict_types=1);

// Copiar como datos.local.php o definir estas variables de entorno.
putenv('TESSA_DB_HOST=127.0.0.1');
putenv('TESSA_DB_PORT=3306');
putenv('TESSA_DB_NAME=tessa_control_pagos');
putenv('TESSA_DB_USER=root');
putenv('TESSA_DB_PASS=');

// Consulta DNI opcional. Use un token propio de Tessa, nunca el de Ascientifics.
// Requiere acuerdo privado con el proveedor. También puede configurarse en el entorno.
// putenv('TESSA_RENIEC_TOKEN=REEMPLAZAR_POR_TOKEN_PRIVADO');
// putenv('TESSA_RENIEC_ENDPOINT=https://api.decolecta.com/v1/reniec/dni');
// Rutas absolutas fuera del repositorio para el texto legal y medios de pago aprobados.
// La plantilla DOCX debe usar los marcadores de TESSA y no contener datos de clientes.
// putenv('TESSA_CONTRATO_PLANTILLA=C:\\ruta-segura\\contrato-tessa.docx');
// putenv('TESSA_CONTRATO_CLAUSULAS=C:\\ruta-segura\\tessa-clausulas.json');
// putenv('TESSA_CONTRATO_MEDIOS_PAGO=C:\\ruta-segura\\tessa-medios-pago.json');
