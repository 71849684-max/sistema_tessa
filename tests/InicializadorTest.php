<?php
declare(strict_types=1);

require __DIR__ . '/../scripts/inicializar_admin.php';
require __DIR__ . '/bootstrap.php';
probar(validarPasswordInicial('corta') === 'La contraseña debe tener al menos 12 caracteres.', 'Contraseña corta debe rechazarse.');
probar(validarPasswordInicial('ClaveInicialSegura!2026') === null, 'Contraseña válida debe permitirse.');
echo "InicializadorTest OK\n";
