<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'status',
        'cover_image_url',
        'delivery_type',
        'currency',
        'price',
        'compare_at_price',
        'stock',
        'summary',
        'content',
        'payload',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'payload' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'purchasable');
    }
}
