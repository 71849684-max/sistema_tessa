<?php
declare(strict_types=1);

final class Conexion
{
    private mysqli $mysqli;

    public function __construct()
    {
        $local = __DIR__ . '/datos.local.php';
        if (is_file($local)) {
            require $local;
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->mysqli = new mysqli(
            getenv('TESSA_DB_HOST') ?: '127.0.0.1',
            getenv('TESSA_DB_USER') ?: 'root',
            getenv('TESSA_DB_PASS') ?: '',
            getenv('TESSA_DB_NAME') ?: 'tessa_control_pagos',
            (int)(getenv('TESSA_DB_PORT') ?: 3306)
        );
        $this->mysqli->set_charset('utf8mb4');
    }

    public function call(string $sql, string $tipos = '', array $parametros = []): array
    {
        $this->liberarResultados();
        $stmt = $this->mysqli->prepare($sql);
        if ($tipos !== '' && $parametros !== []) {
            $referencias = [$tipos];
            foreach ($parametros as $indice => $valor) {
                $referencias[] = &$parametros[$indice];
            }
            call_user_func_array([$stmt, 'bind_param'], $referencias);
        }
        $stmt->execute();

        $filas = [];
        do {
            $resultado = $stmt->get_result();
            if ($resultado instanceof mysqli_result) {
                while ($fila = $resultado->fetch_assoc()) {
                    $filas[] = $fila;
                }
                $resultado->free();
            }
        } while ($stmt->more_results() && $stmt->next_result());

        $stmt->close();
        $this->liberarResultados();
        return $filas;
    }

    public function iniciarTransaccion(): void
    {
        $this->mysqli->begin_transaction();
    }

    public function confirmar(): void
    {
        $this->mysqli->commit();
    }

    public function revertir(): void
    {
        $this->mysqli->rollback();
    }

    private function liberarResultados(): void
    {
        while ($this->mysqli->more_results()) {
            $this->mysqli->next_result();
            $resultado = $this->mysqli->store_result();
            if ($resultado instanceof mysqli_result) {
                $resultado->free();
            }
        }
    }
}

