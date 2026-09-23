<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/ControllerHelper.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/ventas/CobranzaService.php';
PermisoService::requiere('cobranzas', 'ver');
$row = (new CobranzaService())->voucherDatos((int)($_GET['id_cobranza'] ?? 0), (int)($_GET['id_hito'] ?? 0));
if ($row === [] || ($row['c_estado'] ?? '') !== 'REGISTRADO') { http_response_code(404); echo 'Pago no disponible o anulado.'; exit; }
$safe = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Voucher Tessa</title><style>body{font:14px Arial;max-width:700px;margin:30px auto;color:#18263a}h1{color:#7b9d00}table{width:100%;border-collapse:collapse}td{border-bottom:1px solid #ddd;padding:9px}button{padding:10px 18px}@media print{button{display:none}}</style></head><body><h1>TESSA</h1><h2>Comprobante de pago</h2><table>';
foreach (['Número de cobranza' => $row['id_cobranza'], 'Contrato' => $row['con_numero'], 'Cliente' => $row['cliente_nombre'], 'Documento' => $row['documento'], 'Servicio' => $row['servicio_nombre'], 'Fecha' => $row['fecha'], 'Cuota' => $row['cuota_numero'].' - '.$row['cuota_descripcion'], 'Importe de este pago' => 'S/ '.number_format((float)$row['importe_pago'], 2), 'Saldo resultante' => 'S/ '.number_format((float)$row['saldo_resultante'], 2)] as $label => $value) echo '<tr><td><strong>'.$safe($label).'</strong></td><td>'.$safe((string)$value).'</td></tr>';
echo '</table><p><button onclick="window.print()">Imprimir</button></p></body></html>';
