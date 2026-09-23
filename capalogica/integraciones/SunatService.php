<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/RespuestaHelper.php';

final class SunatService
{
    private array $config;
    private ?Closure $transporte;

    public function __construct(?array $config = null, ?callable $transporte = null)
    {
        $this->config = $config ?? [
            'endpoint' => getenv('TESSA_SUNAT_ENDPOINT') ?: 'https://api.decolecta.com/v1/sunat/ruc',
            'token' => getenv('TESSA_SUNAT_TOKEN') ?: 'sk_13777.uGQQIYCjjAApx59EiMXDWV0Xr0ds4kly',
        ];
        $this->transporte = $transporte === null ? null : Closure::fromCallable($transporte);
    }

    public function consultar(string $ruc): array
    {
        $ruc = preg_replace('/\D+/u', '', $ruc) ?? '';
        if (!preg_match('/^\d{11}$/', $ruc)) {
            return RespuestaHelper::error('El RUC debe tener 11 dígitos.', ['ruc' => 'Ingrese un RUC válido.']);
        }
        $endpoint = trim((string)($this->config['endpoint'] ?? ''));
        $token = trim((string)($this->config['token'] ?? ''));
        if ($endpoint === '' || $token === '') {
            return RespuestaHelper::error('Consulta SUNAT no configurada. Puede ingresar los datos manualmente.');
        }
        $partes = parse_url($endpoint);
        if (!is_array($partes) || ($partes['scheme'] ?? '') !== 'https') {
            return RespuestaHelper::error('Configuración SUNAT inválida.');
        }
        $url = rtrim($endpoint, '/') . '/' . rawurlencode($ruc);
        try {
            $respuesta = $this->transporte !== null
                ? ($this->transporte)($url, ['Authorization: Bearer ' . $token, 'Accept: application/json'], 12)
                : $this->consultarHttp($url, ['Authorization: Bearer ' . $token, 'Accept: application/json'], 12);
            $cuerpo = json_decode((string)($respuesta['body'] ?? ''), true);
            $codigo = (int)($respuesta['http'] ?? 0);
            if ($codigo < 200 || $codigo >= 300 || !is_array($cuerpo)) {
                return RespuestaHelper::error('SUNAT no está disponible. Ingrese los datos manualmente.');
            }
            $datos = $cuerpo['data'] ?? $cuerpo['datos'] ?? $cuerpo;
            if (!is_array($datos)) return RespuestaHelper::error('La respuesta SUNAT no contiene datos válidos.');
            $razon = trim((string)($datos['razon_social'] ?? $datos['razonSocial'] ?? $datos['nombre_o_razon_social'] ?? $datos['nombre'] ?? ''));
            if ($razon === '') return RespuestaHelper::error('SUNAT no devolvió razón social. Ingrésela manualmente.');
            return RespuestaHelper::ok('Datos encontrados. Confírmelos antes de guardar.', [
                'ruc' => $ruc,
                'razon_social' => $razon,
                'direccion' => trim((string)($datos['direccion'] ?? $datos['domicilio_fiscal'] ?? '')),
            ]);
        } catch (Throwable $e) {
            error_log('SunatService: ' . $e->getMessage());
            return RespuestaHelper::error('No se pudo consultar SUNAT. Ingrese los datos manualmente.');
        }
    }

    private function consultarHttp(string $url, array $headers, int $timeout): array
    {
        if (!function_exists('curl_init')) throw new RuntimeException('cURL no está disponible.');
        $curl = curl_init($url);
        if ($curl === false) throw new RuntimeException('No se pudo iniciar la conexión HTTPS.');
        try {
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers,
                CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => $timeout,
                CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_MAXREDIRS => 0,
            ]);
            $body = curl_exec($curl);
            if ($body === false) throw new RuntimeException('Consulta HTTPS fallida.');
            return ['http' => (int)curl_getinfo($curl, CURLINFO_HTTP_CODE), 'body' => (string)$body];
        } finally { curl_close($curl); }
    }
}
