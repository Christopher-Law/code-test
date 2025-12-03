<?php

namespace App\Http\Requests;

use App\Models\CartItem;
use Illuminate\Foundation\Http\FormRequest;

class ApplyOptimizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cartItem = $this->route('cartItem');

        return $cartItem instanceof CartItem && $this->userOwnsCartItem($cartItem);
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
        ];
    }

    protected function userOwnsCartItem(CartItem $cartItem): bool
    {
        $userId = $this->getUserId();

        return $cartItem->user_id === $userId;
    }

    protected function getUserId(): int
    {
        return auth()->id() ?? \App\Models\User::first()?->id ?? 0;
    }
}
