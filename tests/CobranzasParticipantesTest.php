<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../capalogica/ventas/CobranzaService.php';

$db = new class {
    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        return [[
            'tipo' => 'GRUPAL',
            'participantes' => '[{"id_cliente":2,"etiqueta":"Integrante: Ana"},{"id_cliente":3,"etiqueta":"Titular: Luis"}]',
        ]];
    }
};

$respuesta = (new CobranzaService($db))->participantesPago(3);

probar($respuesta['exito'] === true, 'La consulta de pagadores grupales debe responder correctamente.');
probar(is_array($respuesta['datos']['participantes'] ?? null), 'Los participantes JSON deben entregarse como lista.');
probar(($respuesta['datos']['participantes'][0]['id_cliente'] ?? 0) === 2, 'La lista debe conservar el identificador del participante.');
echo "CobranzasParticipantesTest OK\n";
