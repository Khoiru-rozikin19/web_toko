<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'amount',
        'unique_code',
        'total_amount',
        'qris_payload',
        'status',
        'approved_by',
        'approved_at',
        'telegram_message_id',
        'notes',
        'expired_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
