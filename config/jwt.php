<?php

class JWTHandler
{
    private $secret = "your_super_secret_key";

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public function generate($payload, $rememberMe = false)
    {
        $header = [
            "alg" => "HS256",
            "typ" => "JWT"
        ];

        $issuedAt = time();

        $expiration = $rememberMe
            ? $issuedAt + (60 * 60 * 24 * 30)  // 30 days
            : $issuedAt + (60 * 60 * 24);      // 1 day

        $payload["iat"] = $issuedAt;
        $payload["exp"] = $expiration;

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            "sha256",
            $headerEncoded . "." . $payloadEncoded,
            $this->secret,
            true
        );

        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
    }

    public function verify($jwt)
    {
        $parts = explode(".", $jwt);

        if (count($parts) !== 3) {
            return null;
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        $signature = $this->base64UrlDecode($signatureEncoded);

        $expected = hash_hmac(
            "sha256",
            $headerEncoded . "." . $payloadEncoded,
            $this->secret,
            true
        );

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);

        if (isset($payload["exp"]) && $payload["exp"] < time()) {
            return null;
        }

        return $payload;
    }
}