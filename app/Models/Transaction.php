<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'product_id',
        'product_name',
        'provider_code',
        'destination_number',
        'amount',
        'price_original',
        'profit',
        'payment_method',
        'status',
        'sn_or_token',
        'provider_trx_id',
        'provider_response',
        'error_message',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'price_original' => 'decimal:2',
        'profit' => 'decimal:2',
        'provider_response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
