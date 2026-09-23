<?php
declare(strict_types=1);

require_once __DIR__ . '/../../capalogica/core/SessionService.php';
require_once __DIR__ . '/../../capalogica/seguridad/PermisoService.php';
require_once __DIR__ . '/../../capalogica/core/MenuService.php';

function tessaBase(): string
{
    $valor = getenv('TESSA_BASE_URL') ?: '/sistema_tessa/';
    return rtrim($valor, '/') . '/';
}

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function tessaIcono(string $codigo): string
{
    $trazos = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
        'clientes' => '<circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6m1 3a5 5 0 0 1 3 5"/>',
        'servicios' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 9h16M9 14h6"/>',
        'contratos' => '<path d="M7 3h10l3 3v15H4V3h3zm8 0v4h5M8 12h8M8 16h8"/>',
        'cobranzas' => '<rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M5 9h2m10 6h2"/>',
        'egresos' => '<path d="M4 4l16 16M5 12h10m-7-4h8m-4 8h7"/>',
        'personal' => '<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'cargos' => '<rect x="4" y="6" width="16" height="15" rx="2"/><path d="M9 6V4h6v2M4 12h16"/>',
        'usuarios' => '<circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 10-4m5 0v8m-4-4h8"/>',
        'auditoria' => '<path d="M12 3l8 4v5c0 5-3 8-8 10-5-2-8-5-8-10V7l8-4zm-3 9 2 2 4-4"/>',
        'configuracion' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.9 4.9l2.2 2.2M16.9 16.9l2.2 2.2M2 12h3M19 12h3M4.9 19.1l2.2-2.2M16.9 7.1l2.2-2.2"/>',
    ];
    $trazo = $trazos[$codigo] ?? '<circle cx="12" cy="12" r="8"/>';
    return '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$trazo.'</svg>';
}

function paginaPrivada(string $titulo, string $seccion, callable $contenido): void
{
    SessionService::iniciar();
    if (empty($_SESSION['usuario_id'])) {
        header('Location: ' . tessaBase() . 'capacliente/app/login.php');
        exit;
    }
    if (!PermisoService::puede($seccion, 'ver')) {
        http_response_code(403);
        exit('No tiene permiso para ver esta sección.');
    }
    $base=tessaBase(); $nombre=escapar((string)($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Usuario'));
    $menu=(new MenuService(PermisoService::efectivo()))->construir();
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="'.escapar(SessionService::csrfToken()).'"><title>'.escapar($titulo).' · Tessa</title><link rel="icon" href="'.$base.'assets/recursos/imagenes/favicon-64.png"><link rel="stylesheet" href="'.$base.'resources/css/app.css"><link rel="stylesheet" href="'.$base.'resources/css/vistas.css"><script>window.TESSA_BASE='.json_encode($base,JSON_UNESCAPED_SLASHES).';</script><script src="'.$base.'resources/js/api.js"></script></head><body>';
    echo '<header class="app-header"><button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Abrir menú">☰</button><a class="header-brand" href="'.$base.'capacliente/app/dashboard.php"><img class="header-logo" src="'.$base.'assets/recursos/imagenes/tessa-logo.png" alt="Tessa Asesoría Profesional"></a><div class="header-user"><span class="header-user-name">'.$nombre.'</span><span class="header-avatar">'.escapar(mb_strtoupper(mb_substr($nombre,0,1))).'</span></div></header>';
    echo '<div class="sidebar-backdrop" data-sidebar-backdrop></div><aside class="sidebar" data-sidebar><nav class="menu-groups">';
    foreach ($menu as $grupo) {
        $abierto = array_reduce($grupo['opciones'], static fn(bool $activo, array $opcion): bool => $activo || $opcion['codigo'] === $seccion, false);
        echo '<section class="menu-group '.($abierto?'is-open':'').'" data-menu-group><button type="button" class="menu-group-trigger" data-menu-group-trigger aria-expanded="'.($abierto?'true':'false').'">'.tessaIcono($grupo['codigo']==='ventas'?'dashboard':'personal').'<span>'.escapar($grupo['etiqueta']).'</span><svg class="menu-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button><div class="menu-group-items">';
        foreach ($grupo['opciones'] as $opcion) {
            echo '<a '.($seccion===$opcion['codigo']?'class="active" aria-current="page"':'').' href="'.$base.escapar($opcion['ruta']).'">'.tessaIcono($opcion['codigo']).'<span>'.escapar($opcion['etiqueta']).'</span></a>';
        }
        echo '</div></section>';
    }
    echo '</nav><form method="post" action="'.$base.'controllers/seguridad/auth_controller.php"><input type="hidden" name="accion" value="logout"><input type="hidden" name="csrf_token" value="'.escapar(SessionService::csrfToken()).'"><button class="logout">Cerrar sesión</button></form></aside>';
    echo '<main class="app-main">';
    $contenido();
    echo '</main><script src="'.$base.'resources/js/app.js"></script></body></html>';
}
