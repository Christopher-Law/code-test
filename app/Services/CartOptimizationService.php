<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\ProductAlternative;
use App\Models\ProductVariant;

class CartOptimizationService
{
    protected array $weights;

    public function __construct()
    {
        $this->weights = config('cart.optimization.weights', [
            'price' => 0.40,
            'shipping' => 0.20,
            'delivery_speed' => 0.25,
            'availability' => 0.15,
        ]);
    }

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

    protected function optimizeCartItem(CartItem $cartItem): ?array
    {
        $currentVariant = $cartItem->productVariant;
        $product = $currentVariant->product;

        $alternatives = $this->collectAlternatives($currentVariant, $product, $cartItem->quantity);

        $bestAlternative = $alternatives->sortByDesc('score')->first();

        if ($bestAlternative === null || $bestAlternative['score'] <= 0) {
            return null;
        }

        return $this->buildOptimizationResult($cartItem, $currentVariant, $product, $bestAlternative);
    }

    protected function collectAlternatives(ProductVariant $currentVariant, $product, int $quantity): \Illuminate\Support\Collection
    {
        $alternatives = collect();

        $sameProductVariants = ProductVariant::where('product_id', $product->id)
            ->where('id', '!=', $currentVariant->id)
            ->where('is_active', true)
            ->with(['supplier', 'product'])
            ->get();

        foreach ($sameProductVariants as $variant) {
            $score = $this->calculateOptimizationScore($currentVariant, $variant, $quantity, 'same_product');
            $alternatives->push([
                'variant' => $variant,
                'type' => 'same_product',
                'score' => $score,
                'relationship_type' => null,
            ]);
        }

        $alternativeProducts = ProductAlternative::where('product_id', $product->id)
            ->with(['alternativeProduct.variants.supplier'])
            ->get()
            ->pluck('alternativeProduct')
            ->filter();

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
                    $quantity,
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

        return $alternatives;
    }

    protected function buildOptimizationResult(CartItem $cartItem, ProductVariant $currentVariant, $product, array $bestAlternative): array
    {
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

        $score += $this->calculatePriceScore($currentVariant, $alternativeVariant, $quantity);
        $score += $this->calculateShippingScore($currentVariant, $alternativeVariant, $quantity);
        $score += $this->calculateDeliveryScore($currentVariant, $alternativeVariant);
        $score += $this->calculateAvailabilityScore($currentVariant, $alternativeVariant);

        if ($type === 'same_product' ||
            ($currentVariant->product->brand &&
             $alternativeVariant->product->brand &&
             $currentVariant->product->brand === $alternativeVariant->product->brand)) {
            $score += 0.1;
        }

        if ($type === 'similar_item') {
            $score *= 0.8;
        }

        return max(0, $score);
    }

    protected function calculatePriceScore(ProductVariant $currentVariant, ProductVariant $alternativeVariant, int $quantity): float
    {
        $currentPrice = $currentVariant->price * $quantity;
        $alternativePrice = $alternativeVariant->price * $quantity;
        $priceDifference = $currentPrice - $alternativePrice;
        $priceScore = $priceDifference > 0 ? ($priceDifference / $currentPrice) : 0;

        return $priceScore * $this->weights['price'];
    }

    protected function calculateShippingScore(ProductVariant $currentVariant, ProductVariant $alternativeVariant, int $quantity): float
    {
        $currentShipping = $currentVariant->shipping_cost * $quantity;
        $alternativeShipping = $alternativeVariant->shipping_cost * $quantity;
        $shippingDifference = $currentShipping - $alternativeShipping;
        $shippingScore = $shippingDifference > 0 ? ($shippingDifference / max($currentShipping, 1)) : 0;

        return $shippingScore * $this->weights['shipping'];
    }

    protected function calculateDeliveryScore(ProductVariant $currentVariant, ProductVariant $alternativeVariant): float
    {
        $currentDeliveryDays = $currentVariant->estimated_delivery_days ?? 999;
        $alternativeDeliveryDays = $alternativeVariant->estimated_delivery_days ?? 999;

        if ($currentDeliveryDays > $alternativeDeliveryDays) {
            $deliveryScore = min(1.0, ($currentDeliveryDays - $alternativeDeliveryDays) / max($currentDeliveryDays, 1));
        } else {
            $deliveryScore = 0;
        }

        return $deliveryScore * $this->weights['delivery_speed'];
    }

    protected function calculateAvailabilityScore(ProductVariant $currentVariant, ProductVariant $alternativeVariant): float
    {
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

        return $availabilityScore * $this->weights['availability'];
    }

    public function getOptimizationSuggestions(CartItem $cartItem): array
    {
        $currentVariant = $cartItem->productVariant;
        $product = $currentVariant->product;

        $sameBrand = [];
        $similarItems = [];
        $all = [];

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

        usort($sameBrand, fn ($a, $b) => $b['score'] <=> $a['score']);
        usort($similarItems, fn ($a, $b) => $b['score'] <=> $a['score']);
        usort($all, fn ($a, $b) => $b['score'] <=> $a['score']);

        return [
            'same_brand' => $sameBrand,
            'similar_items' => $similarItems,
            'all' => $all,
        ];
    }

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
