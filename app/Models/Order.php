<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'merchant_trade_no', 'user_id', 'amount', 'currency',
        'product_name', 'status', 'binance_prepay_id', 'binance_qr_url', 'meta'
    ];
    protected $casts = [
        'meta' => 'array'
    ];
}