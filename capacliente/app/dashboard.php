<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

SessionService::iniciar();
if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . tessaBase() . 'capacliente/app/login.php');
    exit;
}

$menu = (new MenuService(PermisoService::efectivo()))->construir();
foreach ($menu as $grupo) {
    $primera = $grupo['opciones'][0] ?? null;
    if ($primera !== null) {
        header('Location: ' . tessaBase() . $primera['ruta']);
        exit;
    }
}

http_response_code(403);
echo 'No tiene módulos habilitados.';
