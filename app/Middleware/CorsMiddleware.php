<?php
namespace App\Middleware;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public static function apply(Response $response): Response
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, BinancePay-Timestamp, BinancePay-Nonce, BinancePay-Signature');
        $response->headers->set('Access-Control-Max-Age', '86400');
        return $response;
    }
}