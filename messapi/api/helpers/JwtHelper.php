<?php

/**
 * JWT HS256 sin dependencias externas.
 * Cubre RF-02 y RF-03.
 */
class JwtHelper
{
    // ⚠️ En producción mover a variable de entorno.
    private const SECRET = 'cambiar_esto_por_un_secreto_largo_y_aleatorio';
    private const ALGO   = 'HS256';
    private const TTL    = 7200; // 2 horas

    private static function b64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64UrlDecode(string $data): string|false
    {
        $pad = strlen($data) % 4;
        if ($pad) $data .= str_repeat('=', 4 - $pad);
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private static function sign(string $h64, string $p64): string
    {
        $hash = hash_hmac('sha256', $h64 . '.' . $p64, self::SECRET, true);
        return self::b64UrlEncode($hash);
    }

    public static function encode(array $payload): string
    {
        $header  = ['alg' => self::ALGO, 'typ' => 'JWT'];
        $now     = time();
        $payload = array_merge($payload, ['iat' => $now, 'exp' => $now + self::TTL]);

        $h64 = self::b64UrlEncode(json_encode($header));
        $p64 = self::b64UrlEncode(json_encode($payload));
        $s64 = self::sign($h64, $p64);

        return "{$h64}.{$p64}.{$s64}";
    }

    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$h64, $p64, $s64] = $parts;

        if (!hash_equals(self::sign($h64, $p64), $s64)) return null;

        $json = self::b64UrlDecode($p64);
        if ($json === false) return null;

        $payload = json_decode($json, true);
        if (!is_array($payload)) return null;

        if (!isset($payload['exp']) || time() >= (int) $payload['exp']) return null;

        return $payload;
    }
}