<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a user
        $user = User::first();
        if (! $user) {
            $user = User::create([
                'name' => 'Demo User',
                'email' => 'demo@zenone.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Get product variants
        $variants = ProductVariant::all();

        // Add items to cart based on the image
        // 3M RelyX from Brident Supply
        $variant1 = ProductVariant::whereHas('product', fn ($q) => $q->where('sku', '3535'))
            ->whereHas('supplier', fn ($q) => $q->where('slug', 'brident-supply'))
            ->first();

        if ($variant1) {
            CartItem::create([
                'user_id' => $user->id,
                'product_variant_id' => $variant1->id,
                'quantity' => 1,
                'is_selected' => true,
            ]);
        }

        // Meisinger Diamonds from DC Dental
        $variant2 = ProductVariant::whereHas('product', fn ($q) => $q->where('sku', '194-863G-012-FG'))
            ->whereHas('supplier', fn ($q) => $q->where('slug', 'dc-dental'))
            ->first();

        if ($variant2) {
            CartItem::create([
                'user_id' => $user->id,
                'product_variant_id' => $variant2->id,
                'quantity' => 5,
                'is_selected' => true,
            ]);
        }

        // Tetric EvoCeram from DDS Dental Supplies
        $variant3 = ProductVariant::whereHas('product', fn ($q) => $q->where('sku', 'VIV-590313US'))
            ->whereHas('supplier', fn ($q) => $q->where('slug', 'dds-dental-supplies'))
            ->first();

        if ($variant3) {
            CartItem::create([
                'user_id' => $user->id,
                'product_variant_id' => $variant3->id,
                'quantity' => 4,
                'is_selected' => true,
            ]);
        }

        // ACTIVA Presto from Dental City
        $variant4 = ProductVariant::whereHas('product', fn ($q) => $q->where('sku', 'ACTIVA-PRESTO-A3'))
            ->whereHas('supplier', fn ($q) => $q->where('slug', 'dental-city'))
            ->first();

        if ($variant4) {
            CartItem::create([
                'user_id' => $user->id,
                'product_variant_id' => $variant4->id,
                'quantity' => 3,
                'is_selected' => true,
            ]);
        }

        // Dental City Gauze
        $variant5 = ProductVariant::whereHas('product', fn ($q) => $q->where('sku', 'DC-GAUZE-2X2'))
            ->whereHas('supplier', fn ($q) => $q->where('slug', 'dental-city'))
            ->first();

        if ($variant5) {
            CartItem::create([
                'user_id' => $user->id,
                'product_variant_id' => $variant5->id,
                'quantity' => 1,
                'is_selected' => true,
            ]);
        }
    }
}
