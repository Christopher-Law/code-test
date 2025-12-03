<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductAlternative;
use App\Models\ProductVariant;

class CartOptimizationService
{
    /**
     * Default weights for optimization factors
     * These can be configured per user or globally
     */
    protected array $weights = [
        'price' => 0.40,           // 40% weight on price
        'shipping' => 0.20,        // 20% weight on shipping cost
        'delivery_speed' => 0.25,  // 25% weight on delivery speed
        'availability' => 0.15,    // 15% weight on availability
    ];

    /**
     * Optimize a user's cart by finding better alternatives
     *
     * @return array{optimizations: array, total_savings: float, total_savings_with_shipping: float}
     */
    public function optimizeCart(int $userId): array
    {
        $cartItems = CartItem::where('user_id', $userId)
            ->where('is_selected', true)
            ->with(['productVariant.product', 'productVariant.supplier'])
            ->get();

        $optimizations = [];
        $totalSavings = 0.0;
        $totalSavingsWithShipping = 0.0;

        foreach ($cartItems as $cartItem) {
            $optimization = $this->optimizeCartItem($cartItem);

            if ($optimization !== null) {
                $optimizations[] = $optimization;
                $totalSavings += $optimization['price_savings'];
                $totalSavingsWithShipping += $optimization['total_savings'];
            }
        }

        return [
            'optimizations' => $optimizations,
            'total_savings' => $totalSavings,
            'total_savings_with_shipping' => $totalSavingsWithShipping,
        ];
    }

    /**
     * Optimize a single cart item
     */
    protected function optimizeCartItem(CartItem $cartItem): ?array
    {
        $currentVariant = $cartItem->productVariant;
        $product = $currentVariant->product;

        // Get all variants of the same product (different suppliers)
        $sameProductVariants = ProductVariant::where('product_id', $product->id)
            ->where('id', '!=', $currentVariant->id)
            ->where('is_active', true)
            ->with(['supplier', 'product'])
            ->get();

        // Get alternative products (similar items)
        $alternativeProducts = ProductAlternative::where('product_id', $product->id)
            ->with(['alternativeProduct.variants.supplier'])
            ->get()
            ->pluck('alternativeProduct')
            ->filter();

        $alternatives = collect();

        // Evaluate same product variants
        foreach ($sameProductVariants as $variant) {
            $score = $this->calculateOptimizationScore($currentVariant, $variant, $cartItem->quantity, 'same_product');
            $alternatives->push([
                'variant' => $variant,
                'type' => 'same_product',
                'score' => $score,
                'relationship_type' => null,
            ]);
        }

        // Evaluate alternative products
        foreach ($alternativeProducts as $alternativeProduct) {
            $relationship = ProductAlternative::where('product_id', $product->id)
                ->where('alternative_product_id', $alternativeProduct->id)
                ->first();

            foreach ($alternativeProduct->variants as $variant) {
                if (! $variant->is_active) {
                    continue;
                }

                $score = $this->calculateOptimizationScore(
                    $currentVariant,
                    $variant,
                    $cartItem->quantity,
                    $relationship->relationship_type ?? 'similar_item'
                );

                $alternatives->push([
                    'variant' => $variant,
                    'type' => 'alternative',
                    'score' => $score,
                    'relationship_type' => $relationship->relationship_type ?? 'similar_item',
                    'similarity_score' => $relationship->similarity_score ?? 0.5,
                ]);
            }
        }

        // Get the best alternative
        $bestAlternative = $alternatives->sortByDesc('score')->first();

        if ($bestAlternative === null || $bestAlternative['score'] <= 0) {
            return null;
        }

        $bestVariant = $bestAlternative['variant'];
        $quantity = $cartItem->quantity;

        $currentTotal = ($currentVariant->price + $currentVariant->shipping_cost) * $quantity;
        $newTotal = ($bestVariant->price + $bestVariant->shipping_cost) * $quantity;
        $priceSavings = ($currentVariant->price - $bestVariant->price) * $quantity;
        $totalSavings = $currentTotal - $newTotal;

        return [
            'cart_item_id' => $cartItem->id,
            'current_variant' => [
                'id' => $currentVariant->id,
                'product_name' => $product->name,
                'supplier_name' => $currentVariant->supplier->name,
                'price' => $currentVariant->price,
                'shipping_cost' => $currentVariant->shipping_cost,
                'total' => $currentVariant->price + $currentVariant->shipping_cost,
                'estimated_delivery_date' => $currentVariant->estimated_delivery_date?->format('Y-m-d'),
                'availability_status' => $currentVariant->availability_status,
            ],
            'recommended_variant' => [
                'id' => $bestVariant->id,
                'product_name' => $bestVariant->product->name,
                'supplier_name' => $bestVariant->supplier->name,
                'price' => $bestVariant->price,
                'shipping_cost' => $bestVariant->shipping_cost,
                'total' => $bestVariant->price + $bestVariant->shipping_cost,
                'estimated_delivery_date' => $bestVariant->estimated_delivery_date?->format('Y-m-d'),
                'availability_status' => $bestVariant->availability_status,
            ],
            'type' => $bestAlternative['type'],
            'relationship_type' => $bestAlternative['relationship_type'],
            'optimization_score' => $bestAlternative['score'],
            'price_savings' => $priceSavings,
            'total_savings' => $totalSavings,
            'quantity' => $quantity,
        ];
    }

    /**
     * Calculate optimization score for a variant alternative
     * Higher score = better alternative
     */
    protected function calculateOptimizationScore(
        ProductVariant $currentVariant,
        ProductVariant $alternativeVariant,
        int $quantity,
        string $type
    ): float {
        $score = 0.0;

        // Price score (lower is better, so we invert)
        $currentPrice = $currentVariant->price * $quantity;
        $alternativePrice = $alternativeVariant->price * $quantity;
        $priceDifference = $currentPrice - $alternativePrice;
        $priceScore = $priceDifference > 0 ? ($priceDifference / $currentPrice) : 0;
        $score += $priceScore * $this->weights['price'];

        // Shipping score
        $currentShipping = $currentVariant->shipping_cost * $quantity;
        $alternativeShipping = $alternativeVariant->shipping_cost * $quantity;
        $shippingDifference = $currentShipping - $alternativeShipping;
        $shippingScore = $shippingDifference > 0 ? ($shippingDifference / max($currentShipping, 1)) : 0;
        $score += $shippingScore * $this->weights['shipping'];

        // Delivery speed score (faster delivery = higher score)
        $currentDeliveryDays = $currentVariant->estimated_delivery_days ?? 999;
        $alternativeDeliveryDays = $alternativeVariant->estimated_delivery_days ?? 999;

        if ($currentDeliveryDays > $alternativeDeliveryDays) {
            $deliveryScore = min(1.0, ($currentDeliveryDays - $alternativeDeliveryDays) / max($currentDeliveryDays, 1));
        } else {
            $deliveryScore = 0;
        }
        $score += $deliveryScore * $this->weights['delivery_speed'];

        // Availability score
        $availabilityScores = [
            'in_stock' => 1.0,
            'backordered' => 0.5,
            'out_of_stock' => 0.0,
        ];
        $currentAvailability = $availabilityScores[$currentVariant->availability_status] ?? 0;
        $alternativeAvailability = $availabilityScores[$alternativeVariant->availability_status] ?? 0;

        if ($alternativeAvailability > $currentAvailability) {
            $availabilityScore = $alternativeAvailability - $currentAvailability;
        } else {
            $availabilityScore = 0;
        }
        $score += $availabilityScore * $this->weights['availability'];

        // Bonus for same brand
        if ($type === 'same_product' ||
            ($currentVariant->product->brand &&
             $alternativeVariant->product->brand &&
             $currentVariant->product->brand === $alternativeVariant->product->brand)) {
            $score += 0.1; // 10% bonus
        }

        // Penalty for similar items (not exact match)
        if ($type === 'similar_item') {
            $score *= 0.8; // 20% penalty
        }

        return max(0, $score);
    }

    /**
     * Get detailed optimization suggestions for a cart item
     * Returns same brand and similar item alternatives separately
     *
     * @return array{same_brand: array, similar_items: array, all: array}
     */
    public function getOptimizationSuggestions(CartItem $cartItem): array
    {
        $currentVariant = $cartItem->productVariant;
        $product = $currentVariant->product;

        $sameBrand = [];
        $similarItems = [];
        $all = [];

        // Get same product variants
        $sameProductVariants = ProductVariant::where('product_id', $product->id)
            ->where('id', '!=', $currentVariant->id)
            ->where('is_active', true)
            ->with(['supplier', 'product'])
            ->get();

        foreach ($sameProductVariants as $variant) {
            $suggestion = $this->buildSuggestion($currentVariant, $variant, $cartItem->quantity, 'same_product', null);
            if ($suggestion) {
                $all[] = $suggestion;
                $sameBrand[] = $suggestion;
            }
        }

        // Get alternative products
        $alternatives = ProductAlternative::where('product_id', $product->id)
            ->with(['alternativeProduct.variants.supplier'])
            ->get();

        foreach ($alternatives as $alternative) {
            foreach ($alternative->alternativeProduct->variants as $variant) {
                if (! $variant->is_active) {
                    continue;
                }

                $suggestion = $this->buildSuggestion(
                    $currentVariant,
                    $variant,
                    $cartItem->quantity,
                    'alternative',
                    $alternative->relationship_type
                );

                if ($suggestion) {
                    $all[] = $suggestion;

                    if ($alternative->relationship_type === 'same_brand') {
                        $sameBrand[] = $suggestion;
                    } else {
                        $similarItems[] = $suggestion;
                    }
                }
            }
        }

        // Sort by score descending
        usort($sameBrand, fn ($a, $b) => $b['score'] <=> $a['score']);
        usort($similarItems, fn ($a, $b) => $b['score'] <=> $a['score']);
        usort($all, fn ($a, $b) => $b['score'] <=> $a['score']);

        return [
            'same_brand' => $sameBrand,
            'similar_items' => $similarItems,
            'all' => $all,
        ];
    }

    /**
     * Build a suggestion array for a variant
     */
    protected function buildSuggestion(
        ProductVariant $currentVariant,
        ProductVariant $alternativeVariant,
        int $quantity,
        string $type,
        ?string $relationshipType
    ): ?array {
        $score = $this->calculateOptimizationScore($currentVariant, $alternativeVariant, $quantity, $type);

        if ($score <= 0) {
            return null;
        }

        $currentTotal = ($currentVariant->price + $currentVariant->shipping_cost) * $quantity;
        $newTotal = ($alternativeVariant->price + $alternativeVariant->shipping_cost) * $quantity;
        $savings = $currentTotal - $newTotal;

        return [
            'variant_id' => $alternativeVariant->id,
            'product_id' => $alternativeVariant->product_id,
            'product_name' => $alternativeVariant->product->name,
            'supplier_id' => $alternativeVariant->supplier_id,
            'supplier_name' => $alternativeVariant->supplier->name,
            'price' => $alternativeVariant->price,
            'shipping_cost' => $alternativeVariant->shipping_cost,
            'total' => $alternativeVariant->price + $alternativeVariant->shipping_cost,
            'estimated_delivery_date' => $alternativeVariant->estimated_delivery_date?->format('Y-m-d'),
            'estimated_delivery_days' => $alternativeVariant->estimated_delivery_days,
            'availability_status' => $alternativeVariant->availability_status,
            'image_url' => $alternativeVariant->product->image_url,
            'score' => $score,
            'savings' => $savings,
            'price_savings' => ($currentVariant->price - $alternativeVariant->price) * $quantity,
            'type' => $type,
            'relationship_type' => $relationshipType,
        ];
    }
}
