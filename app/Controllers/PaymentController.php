<?php
namespace App\Controllers;
use App\Services\PaymentService;
use App\Middleware\VerifySignatureMiddleware;
use App\Services\BinancePayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\PaymentRepositoryInterface;

class PaymentController
{
    private PaymentService $paymentService;
    private PaymentRepositoryInterface $paymentRepo;
    private BinancePayService $binanceService;

    public function __construct(
        PaymentService $paymentService,
        PaymentRepositoryInterface $paymentRepo,
        BinancePayService $binanceService
    ) {
        $this->paymentService = $paymentService;
        $this->paymentRepo = $paymentRepo;
        $this->binanceService = $binanceService;
    }

    public function create(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $result = $this->paymentService->createOrder($data);
            return new Response(json_encode(['success' => true, 'data' => $result]), 201, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode(['success' => false, 'error' => $e->getMessage()]), 400, ['Content-Type' => 'application/json']);
        }
    }

    public function callback(Request $request): Response
    {
        // Verify signature
        $middleware = new VerifySignatureMiddleware();
        if ($response = $middleware->handle($request, $this->binanceService)) {
            return $response;
        }

        $payload = json_decode($request->getContent(), true);
        $headers = $request->headers->all();

        // Save callback
        $this->paymentRepo->createCallback([
            'payload' => $payload,
            'headers' => $headers
        ]);

        $merchantTradeNo = $payload['data']['merchantTradeNo'] ?? null;
        if ($merchantTradeNo) {
            $order = $this->paymentRepo->findOrderByMerchantTradeNo($merchantTradeNo);
            if ($order && ($payload['data']['status'] ?? null) === 'PAY_SUCCESS') {
                $this->paymentRepo->updateOrderStatus($order->id, 'COMPLETED', [
                    'transaction_id' => $payload['data']['transactionId'] ?? null
                ]);
            }
        }

        return new Response('OK', 200);
    }

    public function show(Request $request, array $args): Response
    {
        $order = \App\Models\Order::find($args['id']);
        if (!$order) {
            return new Response(json_encode(['error' => 'Not found']), 404, ['Content-Type' => 'application/json']);
        }
        return new Response($order->toJson(), 200, ['Content-Type' => 'application/json']);
    }
}