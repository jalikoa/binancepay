<?php
namespace App\Utils;

class SignatureVerifier
{
    public static function generateSignature(string $secret, string $timestamp, string $nonce, string $body): string
    {
        $payload = $timestamp . "\n" . $nonce . "\n" . $body . "\n";
        return strtoupper(hash_hmac('sha512', $payload, $secret));
    }

    public static function verify(string $secret, string $timestamp, string $nonce, string $body, string $signature): bool
    {
        $computed = self::generateSignature($secret, $timestamp, $nonce, $body);
        return hash_equals($computed, $signature);
    }
}