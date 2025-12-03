<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display the shopping cart page
     */
    public function index(): Response
    {
        // For demo purposes, use first user or create a guest user
        $user = Auth::user() ?? \App\Models\User::first();

        if (! $user) {
            abort(404, 'No user found. Please seed the database.');
        }

        $cartItems = CartItem::where('user_id', $user->id)
            ->with([
                'productVariant.product',
                'productVariant.supplier',
            ])
            ->get()
            ->groupBy(function ($item) {
                return $item->productVariant->supplier->name;
            });

        $selectedItems = CartItem::where('user_id', $user->id)
            ->where('is_selected', true)
            ->with(['productVariant.product', 'productVariant.supplier'])
            ->get();

        $subtotal = $selectedItems->sum(fn ($item) => $item->subtotal);
        $shippingTotal = $selectedItems->sum(fn ($item) => $item->shipping_total);
        $tax = $subtotal * 0.0835; // 8.35% tax rate (example)
        $orderTotal = $subtotal + $shippingTotal + $tax;

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartItems->map(function ($items, $supplierName) {
                return [
                    'supplier' => $supplierName,
                    'items' => $items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->productVariant->product_id,
                            'product_name' => $item->productVariant->product->name,
                            'product_sku' => $item->productVariant->product->sku,
                            'supplier_name' => $item->productVariant->supplier->name,
                            'variant_id' => $item->product_variant_id,
                            'price' => $item->productVariant->price,
                            'shipping_cost' => $item->productVariant->shipping_cost,
                            'quantity' => $item->quantity,
                            'is_selected' => $item->is_selected,
                            'availability_status' => $item->productVariant->availability_status,
                            'estimated_delivery_date' => $item->productVariant->estimated_delivery_date?->format('M d, Y'),
                            'estimated_delivery_days' => $item->productVariant->estimated_delivery_days,
                            'image_url' => $item->productVariant->product->image_url,
                            'subtotal' => $item->subtotal,
                            'shipping_total' => $item->shipping_total,
                            'total' => $item->total,
                        ];
                    })->values(),
                    'estimated_total' => $items->sum(fn ($item) => $item->total),
                    'estimated_shipping' => $items->sum(fn ($item) => $item->shipping_total),
                ];
            })->values(),
            'summary' => [
                'subtotal' => $subtotal,
                'shipping' => $shippingTotal,
                'tax' => $tax,
                'total' => $orderTotal,
                'item_count' => $selectedItems->sum('quantity'),
            ],
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(Request $request, CartItem $cartItem): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'success' => true,
            'cart_item' => $cartItem->load(['productVariant.product', 'productVariant.supplier']),
        ]);
    }

    /**
     * Toggle item selection
     */
    public function toggleSelection(Request $request, CartItem $cartItem): \Illuminate\Http\JsonResponse
    {
        $cartItem->update([
            'is_selected' => ! $cartItem->is_selected,
        ]);

        return response()->json([
            'success' => true,
            'is_selected' => $cartItem->is_selected,
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(CartItem $cartItem): \Illuminate\Http\JsonResponse
    {
        $cartItem->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
