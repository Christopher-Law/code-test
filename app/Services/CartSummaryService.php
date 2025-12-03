<?php

namespace App\Services;

use App\Models\CartItem;
use Illuminate\Support\Collection;

class CartSummaryService
{
    public function __construct()
    {
        //
    }

    protected function getTaxRate(): float
    {
        return (float) config('cart.tax_rate', 0.0835);
    }

    public function calculateSummary(Collection $selectedItems): array
    {
        $subtotal = $selectedItems->sum(fn (CartItem $item) => $item->subtotal);
        $shippingTotal = $selectedItems->sum(fn (CartItem $item) => $item->shipping_total);
        $tax = $subtotal * $this->getTaxRate();
        $orderTotal = $subtotal + $shippingTotal + $tax;

        return [
            'subtotal' => $subtotal,
            'shipping' => $shippingTotal,
            'tax' => $tax,
            'total' => $orderTotal,
            'item_count' => $selectedItems->sum('quantity'),
        ];
    }

    public function groupBySupplier(Collection $cartItems): Collection
    {
        return $cartItems->groupBy(function (CartItem $item) {
            return $item->productVariant->supplier->name;
        });
    }
}
