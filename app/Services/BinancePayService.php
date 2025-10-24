<?php
namespace App\Services;
use App\Interfaces\HttpClientInterface;
use App\Utils\SignatureVerifier;
use App\Utils\UUID;

class BinancePayService
{
    private string $merchantId;
    private string $apiKey;
    private string $secretKey;
    private HttpClientInterface $httpClient;

    public function __construct(array $config, HttpClientInterface $httpClient)
    {
        $this->merchantId = $config['merchant_id'];
        $this->apiKey = $config['api_key'];
        $this->secretKey = $config['secret_key'];
        $this->httpClient = $httpClient;
    }

    public function createOrder(array $payload): array
    {
        $timestamp = (string) round(microtime(true) * 1000);
        $nonce = UUID::generateBinanceNonce();
        $bodyJson = json_encode($payload, JSON_UNESCAPED_SLASHES);

        $signature = SignatureVerifier::generateSignature(
            $this->secretKey,
            $timestamp,
            $nonce,
            $bodyJson
        );

        $headers = [
            'BinancePay-Timestamp' => $timestamp,
            'BinancePay-Nonce' => $nonce,
            'BinancePay-Certificate-SN' => $this->apiKey,
            'BinancePay-Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $response = $this->httpClient->post('/binancepay/openapi/v2/order', $headers, $payload);
        $body = (string) $response->getBody();
        return json_decode($body, true);
    }

    public function verifyCallbackSignature(array $headers, string $rawBody): bool
    {
        $required = ['BinancePay-Timestamp', 'BinancePay-Nonce', 'BinancePay-Signature'];
        foreach ($required as $h) {
            if (!isset($headers[$h])) return false;
        }

        return SignatureVerifier::verify(
            $this->secretKey,
            $headers['BinancePay-Timestamp'],
            $headers['BinancePay-Nonce'],
            $rawBody,
            $headers['BinancePay-Signature']
        );
    }
}