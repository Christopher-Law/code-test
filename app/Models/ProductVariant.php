<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'supplier_id',
        'supplier_sku',
        'price',
        'shipping_cost',
        'availability_status',
        'estimated_delivery_days',
        'estimated_delivery_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'estimated_delivery_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->price + $this->shipping_cost;
    }
}
