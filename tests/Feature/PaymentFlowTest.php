<?php
namespace Tests\Feature;

use App\Models\Order;
use App\Repositories\PaymentRepository;
use Illuminate\Database\Capsule\Manager as Capsule;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    private static $capsuleBooted = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (!self::$capsuleBooted) {
            $capsule = new Capsule;
            $capsule->addConnection([
                'driver'    => 'sqlite',
                'database'  => ':memory:',
                'prefix'    => '',
            ]);
            $capsule->bootEloquent();
            $capsule->setAsGlobal();
            self::$capsuleBooted = true;
        }

        // Run migrations in memory
        include __DIR__ . '/../../app/Migrations/2025_10_24_000000_create_orders_table.sql';
        Capsule::schema()->create('orders', function ($table) {
            // We'll recreate via raw SQL
        });
        Capsule::unprepared(file_get_contents(__DIR__ . '/../../app/Migrations/2025_10_24_000000_create_orders_table.sql'));
    }

    public function test_can_create_order_in_repository()
    {
        $repo = new PaymentRepository();
        $order = $repo->createOrder([
            'id' => 'test-uuid-123',
            'merchant_trade_no' => 'FEATURE_TEST_1',
            'amount' => 5.00,
            'currency' => 'USDT',
            'product_name' => 'Test Product',
            'status' => 'PENDING'
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('FEATURE_TEST_1', $order->merchant_trade_no);
        $this->assertEquals('5.00000000', $order->amount);
    }

    public function test_can_find_order_by_merchant_trade_no()
    {
        $repo = new PaymentRepository();
        $repo->createOrder([
            'id' => 'test-uuid-456',
            'merchant_trade_no' => 'SEARCH_ME',
            'amount' => 1.00,
            'currency' => 'USDT',
            'status' => 'PENDING'
        ]);

        $found = $repo->findOrderByMerchantTradeNo('SEARCH_ME');
        $this->assertNotNull($found);
        $this->assertEquals('test-uuid-456', $found->id);
    }
}