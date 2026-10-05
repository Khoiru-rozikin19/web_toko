<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'provider_code',
        'name',
        'description',
        'price_original',
        'price_selling',
        'type',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price_original' => 'decimal:2',
        'price_selling' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
