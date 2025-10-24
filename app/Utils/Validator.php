<?php
namespace App\Utils;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    public static function validateOrder(array $data): void
    {
        try {
            v::key('amount', v::numericVal()->positive())
             ->key('product_name', v::stringType()->length(1, 255))
             ->key('currency', v::stringType()->in(['USDT', 'BUSD', 'BTC', 'ETH'])->notEmpty(), false)
             ->assert($data);
        } catch (NestedValidationException $e) {
            throw new \InvalidArgumentException($e->getFullMessage());
        }
    }
}