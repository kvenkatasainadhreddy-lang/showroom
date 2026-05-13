<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\VehicleModel;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Branches
        $mainBranch = Branch::firstOrCreate(
            ['name' => 'Main Branch'],
            ['city' => 'Chennai', 'address' => '123 Main Road, Chennai', 'phone' => '044-12345678', 'is_active' => true]
        );

        // Categories
        $bikeCategory = Category::firstOrCreate(
            ['name' => 'Bikes'],
            ['type' => 'vehicle', 'parent_id' => null]
        );
        $spareParts = Category::firstOrCreate(
            ['name' => 'Spare Parts'],
            ['type' => 'part', 'parent_id' => null]
        );
        Category::firstOrCreate(
            ['name' => 'Engine Parts'],
            ['type' => 'part', 'parent_id' => $spareParts->id]
        );
        Category::firstOrCreate(
            ['name' => 'Body Parts'],
            ['type' => 'part', 'parent_id' => $spareParts->id]
        );

        // Brands
        $honda = Brand::firstOrCreate(['name' => 'Honda'], ['country' => 'Japan']);
        $yamaha = Brand::firstOrCreate(['name' => 'Yamaha'], ['country' => 'Japan']);
        $tvs = Brand::firstOrCreate(['name' => 'TVS'], ['country' => 'India']);

        // Models
        $activa = VehicleModel::firstOrCreate(['name' => 'Activa 6G', 'brand_id' => $honda->id], ['year' => 2024]);
        $shine = VehicleModel::firstOrCreate(['name' => 'Shine 125', 'brand_id' => $honda->id], ['year' => 2024]);
        $r15 = VehicleModel::firstOrCreate(['name' => 'R15 V4', 'brand_id' => $yamaha->id], ['year' => 2024]);
        $ntorq = VehicleModel::firstOrCreate(['name' => 'Ntorq 125', 'brand_id' => $tvs->id], ['year' => 2024]);

        // Products (Bikes)
        $products = [
            [
                'name' => 'Honda Activa 6G',
                'sku' => 'BIKE-HAC-001',
                'barcode' => '8901522501234',
                'category_id' => $bikeCategory->id,
                'type' => 'vehicle',
                'brand_id' => $honda->id,
                'model_id' => $activa->id,
                'purchase_price' => 68000,
                'selling_price' => 75000,
            ],
            [
                'name' => 'Honda Shine 125',
                'sku' => 'BIKE-HSH-001',
                'barcode' => '8901522501235',
                'category_id' => $bikeCategory->id,
                'type' => 'vehicle',
                'brand_id' => $honda->id,
                'model_id' => $shine->id,
                'purchase_price' => 70000,
                'selling_price' => 78500,
            ],
            [
                'name' => 'Yamaha R15 V4',
                'sku' => 'BIKE-YR15-001',
                'barcode' => '8901522501236',
                'category_id' => $bikeCategory->id,
                'type' => 'vehicle',
                'brand_id' => $yamaha->id,
                'model_id' => $r15->id,
                'purchase_price' => 152000,
                'selling_price' => 168000,
            ],
            [
                'name' => 'TVS Ntorq 125',
                'sku' => 'BIKE-TVN-001',
                'barcode' => '8901522501237',
                'category_id' => $bikeCategory->id,
                'type' => 'vehicle',
                'brand_id' => $tvs->id,
                'model_id' => $ntorq->id,
                'purchase_price' => 76000,
                'selling_price' => 84000,
            ],
            [
                'name' => 'Engine Oil Filter (Honda)',
                'sku' => 'PART-HOF-001',
                'barcode' => '8901522501238',
                'category_id' => $spareParts->id,
                'type' => 'part',
                'brand_id' => $honda->id,
                'model_id' => null,
                'purchase_price' => 120,
                'selling_price' => 180,
            ],
            [
                'name' => 'Brake Pads Set',
                'sku' => 'PART-BRK-001',
                'barcode' => '8901522501239',
                'category_id' => $spareParts->id,
                'type' => 'part',
                'brand_id' => null,
                'model_id' => null,
                'purchase_price' => 350,
                'selling_price' => 550,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::firstOrCreate(
                ['sku' => $productData['sku']],
                $productData
            );

            // Create inventory record
            Inventory::firstOrCreate(
                ['product_id' => $product->id, 'branch_id' => $mainBranch->id],
                ['quantity' => 10, 'min_quantity' => $productData['type'] === 'vehicle' ? 2 : 5]
            );
        }
    }
}
