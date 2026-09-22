<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

function probar(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

function respuestaFalsa(): object
{
    return new class {
        public array $llamadas = [];
        public function call(string $sql, string $tipos = '', array $parametros = []): array
        {
            $this->llamadas[] = [$sql, $tipos, $parametros];
            return [];
        }
    };
}

