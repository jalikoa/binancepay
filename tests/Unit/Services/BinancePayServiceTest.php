<?php
namespace Tests\Unit\Services;

use App\Services\BinancePayService;
use App\Interfaces\HttpClientInterface;
use Mockery;
use Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class BinancePayServiceTest extends TestCase
{
    public function test_create_order_signs_request_correctly()
    {
        // Mock HTTP client
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('getContents')->andReturn('{"status":"SUCCESS","data":{"prepayId":"123","qrCodeUrl":"https://qr"}}');
        $mockResponse->shouldReceive('getBody')->andReturn($mockStream);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('post')
            ->once()
            ->withArgs(function ($url, $headers, $body) {
                return isset($headers['BinancePay-Signature']) &&
                       isset($headers['BinancePay-Timestamp']) &&
                       $body['merchantTradeNo'] === 'TEST_123';
            })
            ->andReturn($mockResponse);

        $service = new BinancePayService([
            'merchant_id' => '987654321',
            'api_key' => 'pub_key',
            'secret_key' => 'secret'
        ], $httpClient);

        $result = $service->createOrder([
            'merchantTradeNo' => 'TEST_123',
            'amount' => 10,
            'currency' => 'USDT',
            'productType' => 'OTHER',
            'productName' => 'Test'
        ]);

        $this->assertEquals('SUCCESS', $result['status']);
        $this->assertEquals('123', $result['data']['prepayId']);
    }

    public function test_verify_callback_signature_works()
    {
        $service = new BinancePayService([
            'merchant_id' => '987654321',
            'api_key' => 'pub_key',
            'secret_key' => 'my_secret'
        ], Mockery::mock(HttpClientInterface::class));

        $headers = [
            'BinancePay-Timestamp' => '1000',
            'BinancePay-Nonce' => 'nonce123',
            'BinancePay-Signature' => 'SIGNATURE'
        ];

        $rawBody = '{"data":{}}';

        // Override signature generation for test
        $expectedSig = strtoupper(hash_hmac('sha512', "1000\nnonce123\n{$rawBody}\n", 'my_secret'));
        $headers['BinancePay-Signature'] = $expectedSig;

        $valid = $service->verifyCallbackSignature($headers, $rawBody);
        $this->assertTrue($valid);
    }
}