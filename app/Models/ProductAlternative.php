<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAlternative extends Model
{
    protected $fillable = [
        'product_id',
        'alternative_product_id',
        'relationship_type',
        'similarity_score',
    ];

    protected function casts(): array
    {
        return [
            'similarity_score' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function alternativeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'alternative_product_id');
    }
}
