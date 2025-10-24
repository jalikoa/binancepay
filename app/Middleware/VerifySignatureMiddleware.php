<?php
namespace App\Middleware;
use App\Services\BinancePayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySignatureMiddleware
{
    public function handle(Request $request, BinancePayService $binanceService): ?Response
    {
        $rawBody = $request->getContent();
        $headers = $request->headers->all();

        // Normalize header names to match Binance format
        $binanceHeaders = [];
        foreach ($headers as $key => $value) {
            $binanceHeaders['BinancePay-' . ucfirst($key)] = $value[0] ?? '';
        }

        if (!$binanceService->verifyCallbackSignature($binanceHeaders, $rawBody)) {
            return new Response('Invalid signature', 403);
        }

        return null; // Continue
    }
}