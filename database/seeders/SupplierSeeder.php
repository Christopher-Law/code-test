<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Brident Supply', 'slug' => 'brident-supply'],
            ['name' => 'DC Dental', 'slug' => 'dc-dental'],
            ['name' => 'DDS Dental Supplies', 'slug' => 'dds-dental-supplies'],
            ['name' => 'Dental City', 'slug' => 'dental-city'],
            ['name' => 'Frontier Dental', 'slug' => 'frontier-dental'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
