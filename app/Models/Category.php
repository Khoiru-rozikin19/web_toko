<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'brand',
        'icon',
        'type',
        'status',
        'sort_order',
    ];

    public function products()
    {
        return $this->hasMany(Product::class)->orderBy('price_selling', 'asc');
    }
}
