<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
