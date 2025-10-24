<?php
namespace App\Utils;
use Symfony\Component\HttpFoundation\Response;

class ResponseHelper
{
    public static function json($data, int $code = 200): Response
    {
        return new Response(
            json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $code,
            ['Content-Type' => 'application/json']
        );
    }

    public static function error(string $message, int $code = 400): Response
    {
        return self::json(['success' => false, 'error' => $message], $code);
    }
}