<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_variant_id',
        'quantity',
        'is_selected',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'is_selected' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSelected(Builder $query): Builder
    {
        return $query->where('is_selected', true);
    }

    public function scopeWithProductDetails(Builder $query): Builder
    {
        return $query->with([
            'productVariant.product',
            'productVariant.supplier',
        ]);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->productVariant->price * $this->quantity;
    }

    public function getShippingTotalAttribute(): float
    {
        return $this->productVariant->shipping_cost * $this->quantity;
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal + $this->shipping_total;
    }
}
