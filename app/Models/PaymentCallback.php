<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PaymentCallback extends Model
{
    protected $table = 'payment_callbacks';
    protected $fillable = ['order_id', 'payload', 'headers'];
    protected $casts = [
        'payload' => 'array',
        'headers' => 'array'
    ];
}