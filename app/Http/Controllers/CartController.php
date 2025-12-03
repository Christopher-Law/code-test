<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemIdsRequest;
use App\Http\Requests\UpdateCartItemQuantityRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Services\CartSummaryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        protected CartSummaryService $summaryService
    ) {}

    public function index(): Response
    {
        $user = $this->getCurrentUser();

        $cartItems = CartItem::forUser($user->id)
            ->withProductDetails()
            ->get();

        $selectedItems = CartItem::forUser($user->id)
            ->selected()
            ->withProductDetails()
            ->get();

        $groupedItems = $this->summaryService->groupBySupplier($cartItems);
        $summary = $this->summaryService->calculateSummary($selectedItems);

        return Inertia::render('Cart/Index', [
            'cartItems' => $groupedItems->map(function ($items, $supplierName) {
                return [
                    'supplier' => $supplierName,
                    'items' => CartItemResource::collection($items)->resolve(),
                    'estimated_total' => $items->sum(fn ($item) => $item->total),
                    'estimated_shipping' => $items->sum(fn ($item) => $item->shipping_total),
                ];
            })->values(),
            'summary' => $summary,
        ]);
    }

    public function updateQuantity(UpdateCartItemQuantityRequest $request, CartItem $cartItem): RedirectResponse
    {
        $cartItem->update([
            'quantity' => $request->validated()['quantity'],
        ]);

        return redirect()->back();
    }

    public function toggleSelection(CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($cartItem);

        $cartItem->update([
            'is_selected' => ! $cartItem->is_selected,
        ]);

        return redirect()->back();
    }

    public function remove(CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($cartItem);

        $cartItem->delete();

        return redirect()->back();
    }

    public function deselectAll(CartItemIdsRequest $request): RedirectResponse
    {
        $user = $this->getCurrentUser();

        CartItem::forUser($user->id)
            ->whereIn('id', $request->validated()['item_ids'])
            ->update(['is_selected' => false]);

        return redirect()->back();
    }

    public function removeSelected(CartItemIdsRequest $request): RedirectResponse
    {
        $user = $this->getCurrentUser();

        CartItem::forUser($user->id)
            ->whereIn('id', $request->validated()['item_ids'])
            ->delete();

        return redirect()->back();
    }

    public function saveForLater(CartItemIdsRequest $request): RedirectResponse
    {
        $user = $this->getCurrentUser();

        CartItem::forUser($user->id)
            ->whereIn('id', $request->validated()['item_ids'])
            ->update(['is_selected' => false]);

        return redirect()->back();
    }

    protected function authorizeCartItem(CartItem $cartItem): void
    {
        $user = $this->getCurrentUser();

        if ($cartItem->user_id !== $user->id) {
            abort(403, 'You do not have permission to modify this cart item.');
        }
    }
}
