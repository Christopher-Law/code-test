<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productVariant->product_id,
            'product_name' => $this->productVariant->product->name,
            'product_sku' => $this->productVariant->product->sku,
            'supplier_name' => $this->productVariant->supplier->name,
            'variant_id' => $this->product_variant_id,
            'price' => $this->productVariant->price,
            'shipping_cost' => $this->productVariant->shipping_cost,
            'quantity' => $this->quantity,
            'is_selected' => $this->is_selected,
            'availability_status' => $this->productVariant->availability_status,
            'estimated_delivery_date' => $this->productVariant->estimated_delivery_date?->format('M d, Y'),
            'estimated_delivery_days' => $this->productVariant->estimated_delivery_days,
            'image_url' => $this->productVariant->product->image_url,
            'subtotal' => $this->subtotal,
            'shipping_total' => $this->shipping_total,
            'total' => $this->total,
        ];
    }
}
