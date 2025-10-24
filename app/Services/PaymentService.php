<?php
namespace App\Services;
use App\Interfaces\PaymentRepositoryInterface;
use App\Utils\UUID;

class PaymentService
{
    private BinancePayService $binancePayService;
    private PaymentRepositoryInterface $paymentRepo;

    public function __construct(
        BinancePayService $binancePayService,
        PaymentRepositoryInterface $paymentRepo
    ) {
        $this->binancePayService = $binancePayService;
        $this->paymentRepo = $paymentRepo;
    }

    public function createOrder(array $requestData): array
    {
        $merchantTradeNo = 'ORDER_' . strtoupper(UUID::v4());
        $orderId = UUID::v4();

        // Save local order
        $order = $this->paymentRepo->createOrder([
            'id' => $orderId,
            'merchant_trade_no' => $merchantTradeNo,
            'user_id' => $requestData['user_id'] ?? null,
            'amount' => $requestData['amount'],
            'currency' => $requestData['currency'] ?? 'USDT',
            'product_name' => $requestData['product_name'] ?? 'Product',
            'status' => 'PENDING'
        ]);

        // Call Binance
        $binancePayload = [
            'merchantId' => $_ENV['BINANCE_MERCHANT_ID'],
            'merchantTradeNo' => $merchantTradeNo,
            'tradeType' => 'WEB',
            'totalFee' => $requestData['amount'],
            'currency' => $requestData['currency'] ?? 'USDT',
            'productType' => 'OTHER',
            'productName' => $requestData['product_name'] ?? 'Product',
            'returnUrl' => $requestData['returnUrl'] ?? $_ENV['WEBHOOK_URL'],
            'cancelUrl' => $requestData['cancelUrl'] ?? $_ENV['WEBHOOK_URL']
        ];

        $binanceResponse = $this->binancePayService->createOrder($binancePayload);

        if (($binanceResponse['status'] ?? null) !== 'SUCCESS') {
            throw new \Exception('Binance order failed: ' . ($binanceResponse['errorMessage'] ?? 'Unknown'));
        }

        $data = $binanceResponse['data'];
        $this->paymentRepo->updateOrderStatus($orderId, 'BINANCE_CREATED', [
            'binance_prepay_id' => $data['prepayId'],
            'qr_code' => $data['qrCode'] ?? null,
            'qr_url' => $data['qrCodeUrl'] ?? null
        ]);

        return [
            'order_id' => $orderId,
            'merchant_trade_no' => $merchantTradeNo,
            'qr_code_url' => $data['qrCodeUrl'] ?? null,
            'checkout_url' => $data['checkoutUrl'] ?? null
        ];
    }
}