<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyOptimizationRequest;
use App\Models\CartItem;
use App\Services\CartOptimizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class OptimizationController extends Controller
{
    public function __construct(
        protected CartOptimizationService $optimizationService
    ) {}

    public function optimizeCart(): JsonResponse
    {
        $userId = $this->getCurrentUser()->id;

        $result = $this->optimizationService->optimizeCart($userId);

        return response()->json($result);
    }

    public function getSuggestions(CartItem $cartItem): JsonResponse
    {
        $this->authorizeCartItem($cartItem);

        $suggestions = $this->optimizationService->getOptimizationSuggestions($cartItem);

        return response()->json($suggestions);
    }

    public function applyOptimization(ApplyOptimizationRequest $request, CartItem $cartItem): RedirectResponse
    {
        $cartItem->update([
            'product_variant_id' => $request->validated()['variant_id'],
        ]);

        return redirect()->back();
    }

    protected function authorizeCartItem(CartItem $cartItem): void
    {
        $user = $this->getCurrentUser();

        if ($cartItem->user_id !== $user->id) {
            abort(403, 'You do not have permission to view this cart item.');
        }
    }
}
