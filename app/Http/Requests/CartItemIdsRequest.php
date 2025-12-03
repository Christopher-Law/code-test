<?php

namespace App\Http\Requests;

use App\Models\CartItem;
use Illuminate\Foundation\Http\FormRequest;

class CartItemIdsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userId = $this->getUserId();

        if ($userId === 0) {
            return false;
        }

        $itemIds = $this->input('item_ids', []);

        if (collect($itemIds)->isEmpty()) {
            return false;
        }

        $itemIdsCollection = collect($itemIds);

        return CartItem::forUser($userId)
            ->whereIn('id', $itemIds)
            ->count() === $itemIdsCollection->count();
    }

    public function rules(): array
    {
        return [
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'integer', 'exists:cart_items,id'],
        ];
    }

    protected function getUserId(): int
    {
        return auth()->id() ?? \App\Models\User::first()?->id ?? 0;
    }
}
