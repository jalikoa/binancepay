<?php
namespace App\Interfaces;
use App\Models\Order;

interface PaymentRepositoryInterface {
    public function createOrder(array $data): Order;
    public function findOrderByMerchantTradeNo(string $merchantTradeNo): ?Order;
    public function updateOrderStatus(string $orderId, string $status, array $meta = []): bool;
    public function createCallback(array $data): bool;
}