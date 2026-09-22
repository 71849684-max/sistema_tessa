<?php
declare(strict_types=1);

require_once __DIR__ . '/../../capalogica/seguridad/AuthService.php';
header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
echo (new AuthService())->captcha();

