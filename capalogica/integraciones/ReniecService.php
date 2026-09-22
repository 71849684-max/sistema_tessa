<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/RespuestaHelper.php';

final class ReniecService
{
    private array $config;
    private ?Closure $transporte;

    public function __construct(?array $config = null, ?callable $transporte = null)
    {
        $this->config = $config ?? [
            'endpoint' => getenv('TESSA_RENIEC_ENDPOINT') ?: 'https://api.decolecta.com/v1/reniec/dni',
            'token' => getenv('TESSA_RENIEC_TOKEN') ?: 'sk_13777.uGQQIYCjjAApx59EiMXDWV0Xr0ds4kly',
        ];
        $this->transporte = $transporte === null ? null : Closure::fromCallable($transporte);
    }

    public function consultar(string $dni): array
    {
        $dni = preg_replace('/\D+/u', '', $dni) ?? '';
        if (!preg_match('/^\d{8}$/', $dni)) return RespuestaHelper::error('El DNI debe tener 8 dígitos.', ['dni' => 'Ingrese un DNI válido.']);
        $token = trim((string)($this->config['token'] ?? ''));
        if ($token === '') return RespuestaHelper::error('Consulta RENIEC no configurada. Puede ingresar los datos manualmente.', ['configuracion' => 'Falta el token de Tessa.']);
        $endpoint = (string)($this->config['endpoint'] ?? '');
        $partes = parse_url($endpoint);
        if (!is_array($partes) || ($partes['scheme'] ?? '') !== 'https' || strtolower((string)($partes['host'] ?? '')) !== 'api.decolecta.com') {
            return RespuestaHelper::error('Configuración RENIEC inválida.', ['configuracion' => 'El endpoint debe usar HTTPS y el proveedor autorizado.']);
        }
        $url = $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . http_build_query(['numero' => $dni]);
        $headers = ['Authorization: Bearer ' . $token, 'Accept: application/json'];
        try {
            $respuesta = $this->transporte !== null
                ? ($this->transporte)($url, $headers, 12)
                : $this->consultarHttp($url, $headers, 12);
            $codigo = (int)($respuesta['http'] ?? 0);
            $cuerpo = json_decode((string)($respuesta['body'] ?? ''), true);
            if ($codigo === 404) return RespuestaHelper::error('No se encontraron datos para este DNI. Ingréselos manualmente.');
            if ($codigo === 429) return RespuestaHelper::error('Límite de consultas RENIEC alcanzado. Intente más tarde o ingrese los datos manualmente.');
            if ($codigo < 200 || $codigo >= 300 || !is_array($cuerpo)) return RespuestaHelper::error('RENIEC no está disponible. Ingrese los datos manualmente.');
            $datos = $cuerpo['datos'] ?? $cuerpo['data'] ?? $cuerpo;
            if (!is_array($datos)) return RespuestaHelper::error('La respuesta RENIEC no contiene datos válidos.');
            $nombres = trim((string)($datos['nombres'] ?? $datos['nombre'] ?? $datos['first_name'] ?? ''));
            $paterno = trim((string)($datos['apellido_paterno'] ?? $datos['apellidoPaterno'] ?? $datos['first_last_name'] ?? ''));
            $materno = trim((string)($datos['apellido_materno'] ?? $datos['apellidoMaterno'] ?? $datos['second_last_name'] ?? ''));
            $apellidos = trim((string)($datos['apellidos'] ?? trim($paterno . ' ' . $materno)));
            if ($nombres === '' || $apellidos === '') return RespuestaHelper::error('RENIEC no devolvió nombres y apellidos completos. Ingréselos manualmente.');
            return RespuestaHelper::ok('Datos encontrados. Confírmelos antes de guardar.', ['dni' => $dni, 'nombres' => $nombres, 'apellidos' => $apellidos]);
        } catch (Throwable $e) {
            error_log('ReniecService: ' . $e->getMessage());
            return RespuestaHelper::error('No se pudo consultar RENIEC. Ingrese los datos manualmente.');
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
