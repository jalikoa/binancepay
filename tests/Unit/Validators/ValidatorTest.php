<?php
namespace Tests\Unit\Validators;

use App\Utils\Validator;
use InvalidArgumentException;
use Tests\TestCase;

class ValidatorTest extends TestCase
{
    public function test_valid_order_passes_validation()
    {
        $data = [
            'amount' => 10.5,
            'product_name' => 'Test Product',
            'currency' => 'USDT'
        ];

        $this->expectNotToPerformAssertions();
        Validator::validateOrder($data);
    }

    public function test_missing_amount_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::validateOrder(['product_name' => 'Test']);
    }

    public function test_invalid_currency_fails()
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::validateOrder([
            'amount' => 1,
            'product_name' => 'Test',
            'currency' => 'INVALID'
        ]);
    }
}