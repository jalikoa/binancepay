<?php
namespace App\Repositories;
use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Order;
use App\Models\PaymentCallback;
use App\Abstracts\AbstractRepository;

class PaymentRepository extends AbstractRepository implements PaymentRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Order());
    }

    public function createOrder(array $data): Order
    {
        return Order::create($data);
    }

    public function findOrderByMerchantTradeNo(string $merchantTradeNo): ?Order
    {
        return Order::where('merchant_trade_no', $merchantTradeNo)->first();
    }

    public function updateOrderStatus(string $orderId, string $status, array $meta = []): bool
    {
        $order = Order::find($orderId);
        if (!$order) return false;
        $order->status = $status;
        if (!empty($meta)) {
            $order->meta = array_merge($order->meta ?? [], $meta);
        }
        return $order->save();
    }

    public function createCallback(array $data): bool
    {
        return PaymentCallback::create($data) !== null;
    }
}