<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$migracion = file_get_contents(__DIR__ . '/../bd/actualizar_cobranza_grupal_y_anulacion.sql');

probar(
    str_contains($migracion, "estado=IF(v_saldo-v_monto<=0,'PAGADO','PARCIAL')"),
    'Un pago parcial debe dejar la cuota en estado PARCIAL usando el saldo bloqueado antes del pago.'
);

echo "PartialPaymentRegressionTest OK\n";
