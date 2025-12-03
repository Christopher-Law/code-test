<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAlternative;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all()->keyBy('slug');

        // Product 1: 3M RelyX Luting Plus
        $product1 = Product::create([
            'name' => '3M RelyX Luting Plus Luting Rsn Mod Gls Inmr Automix Cement Netrl Val Pk 3/Pk',
            'sku' => '3535',
            'brand' => '3M',
            'category' => 'Cement',
            'image_url' => null,
        ]);

        ProductVariant::create([
            'product_id' => $product1->id,
            'supplier_id' => $suppliers['brident-supply']->id,
            'supplier_sku' => '3535',
            'price' => 236.79,
            'shipping_cost' => 0,
            'availability_status' => 'in_stock',
            'estimated_delivery_days' => 3,
            'estimated_delivery_date' => Carbon::now()->addDays(3),
        ]);

        // Product 2: Meisinger Diamonds
        $product2 = Product::create([
            'name' => 'Meisinger Diamonds 863G-012-FG Coarse 5/Pk',
            'sku' => '194-863G-012-FG',
            'brand' => 'Meisinger',
            'category' => 'Diamonds',
            'image_url' => null,
        ]);

        ProductVariant::create([
            'product_id' => $product2->id,
            'supplier_id' => $suppliers['dc-dental']->id,
            'supplier_sku' => '194-863G-012-FG',
            'price' => 53.70,
            'shipping_cost' => 0,
            'availability_status' => 'in_stock',
            'estimated_delivery_days' => 2,
            'estimated_delivery_date' => Carbon::now()->addDays(2),
        ]);

        // Product 3: Tetric EvoCeram
        $product3 = Product::create([
            'name' => 'Tetric EvoCeram Syringe A2 3 Gm Refill 3gm 1/PK',
            'sku' => 'VIV-590313US',
            'brand' => 'Ivoclar',
            'category' => 'Composite',
            'image_url' => null,
        ]);

        ProductVariant::create([
            'product_id' => $product3->id,
            'supplier_id' => $suppliers['dds-dental-supplies']->id,
            'supplier_sku' => 'VIV-590313US',
            'price' => 77.93,
            'shipping_cost' => 0,
            'availability_status' => 'backordered',
            'estimated_delivery_days' => 5,
            'estimated_delivery_date' => Carbon::now()->addDays(5),
        ]);

        // Product 4: ACTIVA Presto (for alternatives demo)
        $product4 = Product::create([
            'name' => 'ACTIVA Presto Universal Stackable Light Cure Composite 2/Pk, A3 Shade',
            'sku' => 'ACTIVA-PRESTO-A3',
            'brand' => 'ACTIVA',
            'category' => 'Composite',
            'image_url' => null,
        ]);

        ProductVariant::create([
            'product_id' => $product4->id,
            'supplier_id' => $suppliers['dental-city']->id,
            'supplier_sku' => 'ACTIVA-PRESTO-A3-DC',
            'price' => 87.79,
            'shipping_cost' => 0,
            'availability_status' => 'in_stock',
            'estimated_delivery_days' => 3,
            'estimated_delivery_date' => Carbon::now()->addDays(3),
        ]);

        // Same product, different supplier (cheaper)
        ProductVariant::create([
            'product_id' => $product4->id,
            'supplier_id' => $suppliers['frontier-dental']->id,
            'supplier_sku' => 'ACTIVA-PRESTO-A3-FD',
            'price' => 84.30,
            'shipping_cost' => 0,
            'availability_status' => 'in_stock',
            'estimated_delivery_days' => 5,
            'estimated_delivery_date' => Carbon::now()->addDays(5),
        ]);

        // Product 5: Dental City Gauze
        $product5 = Product::create([
            'name' => 'Dental City 2x2 4-ply Non-Woven Gauze 35gm 5000/Case',
            'sku' => 'DC-GAUZE-2X2',
            'brand' => 'Dental City',
            'category' => 'Gauze',
            'image_url' => null,
        ]);

        ProductVariant::create([
            'product_id' => $product5->id,
            'supplier_id' => $suppliers['dental-city']->id,
            'supplier_sku' => 'DC-GAUZE-2X2',
            'price' => 23.82,
            'shipping_cost' => 0,
            'availability_status' => 'in_stock',
            'estimated_delivery_days' => 3,
            'estimated_delivery_date' => Carbon::now()->addDays(3),
        ]);

        // Similar product (alternative)
        $product6 = Product::create([
            'name' => 'Non-Woven Gauzes, Non-Sterile, 4-Ply, 2" x 2", 5000/Pk',
            'sku' => 'GAUZE-2X2-ALT',
            'brand' => 'Generic',
            'category' => 'Gauze',
            'image_url' => null,
        ]);

        ProductVariant::create([
            'product_id' => $product6->id,
            'supplier_id' => $suppliers['frontier-dental']->id,
            'supplier_sku' => 'GAUZE-2X2-ALT-FD',
            'price' => 15.07,
            'shipping_cost' => 0,
            'availability_status' => 'in_stock',
            'estimated_delivery_days' => 5,
            'estimated_delivery_date' => Carbon::now()->addDays(5),
        ]);

        // Create product alternatives
        ProductAlternative::create([
            'product_id' => $product5->id,
            'alternative_product_id' => $product6->id,
            'relationship_type' => 'similar_item',
            'similarity_score' => 0.85,
        ]);

        // Add more variants for optimization demo
        // Same product from different suppliers with varying prices
        ProductVariant::create([
            'product_id' => $product1->id,
            'supplier_id' => $suppliers['frontier-dental']->id,
            'supplier_sku' => '3535-FD',
            'price' => 228.50,
            'shipping_cost' => 5.00,
            'availability_status' => 'in_stock',
            'estimated_delivery_days' => 4,
            'estimated_delivery_date' => Carbon::now()->addDays(4),
        ]);

        ProductVariant::create([
            'product_id' => $product2->id,
            'supplier_id' => $suppliers['dental-city']->id,
            'supplier_sku' => '194-863G-012-FG-DC',
            'price' => 51.20,
            'shipping_cost' => 0,
            'availability_status' => 'in_stock',
            'estimated_delivery_days' => 3,
            'estimated_delivery_date' => Carbon::now()->addDays(3),
        ]);

        ProductVariant::create([
            'product_id' => $product3->id,
            'supplier_id' => $suppliers['frontier-dental']->id,
            'supplier_sku' => 'VIV-590313US-FD',
            'price' => 74.50,
            'shipping_cost' => 0,
            'availability_status' => 'in_stock',
            'estimated_delivery_days' => 4,
            'estimated_delivery_date' => Carbon::now()->addDays(4),
        ]);
    }
}
