<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
$base=tessaBase();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ingreso · Tessa</title>
    <link rel="icon" href="<?= $base ?>assets/recursos/imagenes/favicon-64.png">
    <link rel="stylesheet" href="<?= $base ?>resources/css/login.css">
</head>
<body class="login-body">
    <video class="login-video" autoplay muted loop playsinline poster="<?= $base ?>assets/recursos/imagenes/tessa-logo-original.webp">
        <source src="<?= $base ?>assets/recursos/videos/llamagozu.mp4" type="video/mp4">
    </video>
    <div class="login-overlay">
    </div>
    <main class="login-shell">
        <section class="login-card"><img class="login-logo" src="<?= $base ?>assets/recursos/imagenes/tessa-logo.png" alt="Tessa Asesoría Profesional">
            <p class="login-kicker">CONTROL INTERNO</p>
            <h1>Ingresa a tu espacio.</h1>
            <form id="login-form" action="<?= $base ?>controllers/seguridad/auth_controller.php" method="post" novalidate>
                <label>Usuario<input name="usuario" autocomplete="username" required></label>
                <label>Contraseña<input name="clave" type="password" autocomplete="current-password" required></label>
                <div class="captcha-row">
                    <img id="captcha" src="<?= $base ?>controllers/seguridad/captcha_controller.php" alt="CAPTCHA">
                    <button id="refresh-captcha" type="button" aria-label="Renovar CAPTCHA">↻</button>
                </div>
                <label>Escribe el código<input name="captcha" autocomplete="off" required></label>
                <button class="primary" type="submit">Ingresar</button><p id="login-message" role="status"></p>
            </form>
        </section>
    </main>
    <script>window.TESSA_BASE=<?= json_encode($base) ?>;</script>
    <script src="<?= $base ?>resources/js/login.js"></script>
</body>
</html>

