<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Services\CartOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptimizationController extends Controller
{
    public function __construct(
        protected CartOptimizationService $optimizationService
    ) {}

    /**
     * Get optimization suggestions for the entire cart
     */
    public function optimizeCart(): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id() ?? \App\Models\User::first()?->id;

        if (! $userId) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $result = $this->optimizationService->optimizeCart($userId);

        return response()->json($result);
    }

    /**
     * Get optimization suggestions for a specific cart item
     */
    public function getSuggestions(CartItem $cartItem): \Illuminate\Http\JsonResponse
    {
        $suggestions = $this->optimizationService->getOptimizationSuggestions($cartItem);

        return response()->json($suggestions);
    }

    /**
     * Apply optimization by replacing a cart item with a recommended variant
     */
    public function applyOptimization(Request $request, CartItem $cartItem): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
        ]);

        $cartItem->update([
            'product_variant_id' => $request->variant_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'cart_item' => $cartItem->load(['productVariant.product', 'productVariant.supplier']),
        ]);
    }
}
