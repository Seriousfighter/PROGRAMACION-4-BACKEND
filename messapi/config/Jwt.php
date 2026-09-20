<?php

class Jwt
{
    private static $secret = 'MESSAPI_CLAVE_SECRETA_2026_PABLO';

    // El token dura 8 horas
    private static $expirationTime = 28800;

    public static function create($userId)
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $payload = [
            'sub' => (int) $userId,
            'iat' => time(),
            'exp' => time() + self::$expirationTime
        ];

        $headerEncoded = self::base64UrlEncode(
            json_encode($header)
        );

        $payloadEncoded = self::base64UrlEncode(
            json_encode($payload)
        );

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            self::$secret,
            true
        );

        $signatureEncoded = self::base64UrlEncode($signature);

        return $headerEncoded . '.' .
            $payloadEncoded . '.' .
            $signatureEncoded;
    }

    public static function verify($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        $headerEncoded = $parts[0];
        $payloadEncoded = $parts[1];
        $signatureEncoded = $parts[2];

        $expectedSignature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            self::$secret,
            true
        );

        $expectedSignatureEncoded =
            self::base64UrlEncode($expectedSignature);

        if (!hash_equals(
            $expectedSignatureEncoded,
            $signatureEncoded
        )) {
            return null;
        }

        $payload = json_decode(
            self::base64UrlDecode($payloadEncoded),
            true
        );

        if (!is_array($payload)) {
            return null;
        }

        if (!isset($payload['sub']) || !isset($payload['exp'])) {
            return null;
        }

        if ($payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(
            strtr(base64_encode($data), '+/', '-_'),
            '='
        );
    }

    private static function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(
            strtr($data, '-_', '+/')
        );
    }
}
