<?php
declare(strict_types=1);

final class CaptchaService
{
    private array $sesion;
    private Closure $generador;

    public function __construct(array &$sesion, ?Closure $generador = null)
    {
        $this->sesion =& $sesion;
        $this->generador = $generador ?? static function (): string {
            $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $codigo = '';
            for ($i = 0; $i < 5; $i++) {
                $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
            return $codigo;
        };
    }

    public function generar(): string
    {
        $codigo = strtoupper(($this->generador)());
        $this->sesion['captcha_codigo'] = $codigo;
        $this->sesion['captcha_expira'] = time() + 300;

        $ruido = bin2hex(random_bytes(5));
        $texto = htmlspecialchars($codigo, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        return '<svg xmlns="http://www.w3.org/2000/svg" width="180" height="54" viewBox="0 0 180 54" role="img" aria-label="Código CAPTCHA">'
            . '<rect width="180" height="54" rx="8" fill="#111111"/>'
            . '<path d="M0 36 L180 15" stroke="#c8ff00" stroke-width="2" opacity=".7"/>'
            . '<text x="20" y="37" font-family="monospace" font-size="27" font-weight="700" letter-spacing="7" fill="#ffffff">'
            . $texto . '</text><desc>' . $ruido . '</desc></svg>';
    }

    public function validar(string $codigo): bool
    {
        $esperado = (string)($this->sesion['captcha_codigo'] ?? '');
        $expira = (int)($this->sesion['captcha_expira'] ?? 0);
        unset($this->sesion['captcha_codigo'], $this->sesion['captcha_expira']);

        return $esperado !== ''
            && $expira >= time()
            && hash_equals($esperado, strtoupper(trim($codigo)));
    }
}

