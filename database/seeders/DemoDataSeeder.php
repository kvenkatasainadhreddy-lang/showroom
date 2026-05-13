<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\PriceLog;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\VehicleModel;
use App\Models\VehicleStock;
use App\Models\VehicleVariant;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding demo data…');

        // ──────────────────────────────────────────────────────────────
        //  1. BRANCHES
        // ──────────────────────────────────────────────────────────────
        $branches = collect([
            ['name' => 'Main Showroom',    'address' => '12 MG Road, Bangalore', 'phone' => '080-41234567', 'city' => 'Bangalore', 'is_active' => true],
            ['name' => 'North Branch',     'address' => '45 Ring Road, Bangalore', 'phone' => '080-98765432', 'city' => 'Bangalore', 'is_active' => true],
            ['name' => 'South Branch',     'address' => '8 Hosur Road, Bangalore', 'phone' => '080-55554444', 'city' => 'Bangalore', 'is_active' => true],
        ])->map(fn($d) => Branch::firstOrCreate(['name' => $d['name']], $d));

        $mainBranch  = $branches[0];
        $northBranch = $branches[1];
        $southBranch = $branches[2];

        // ──────────────────────────────────────────────────────────────
        //  2. USERS / ROLES
        // ──────────────────────────────────────────────────────────────
        $adminRole   = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        $salesRole   = Role::firstOrCreate(['name' => 'salesperson', 'guard_name' => 'web']);
        $accRole     = Role::firstOrCreate(['name' => 'accountant',  'guard_name' => 'web']);

        $admin = User::firstOrCreate(['email' => 'admin@showroom.com'], [
            'name' => 'Admin User', 'password' => Hash::make('password'),
        ]);
        $admin->syncRoles([$adminRole]);

        $salesman1 = User::firstOrCreate(['email' => 'ravi@showroom.com'], [
            'name' => 'Ravi Kumar', 'password' => Hash::make('password'),
        ]);
        $salesman1->syncRoles([$salesRole]);

        $salesman2 = User::firstOrCreate(['email' => 'priya@showroom.com'], [
            'name' => 'Priya Sharma', 'password' => Hash::make('password'),
        ]);
        $salesman2->syncRoles([$salesRole]);

        $accountant = User::firstOrCreate(['email' => 'acc@showroom.com'], [
            'name' => 'Suresh Accountant', 'password' => Hash::make('password'),
        ]);
        $accountant->syncRoles([$accRole]);

        $salesmen = collect([$admin, $salesman1, $salesman2]);

        // ──────────────────────────────────────────────────────────────
        //  3. BRANDS
        // ──────────────────────────────────────────────────────────────
        $honda   = Brand::firstOrCreate(['name' => 'Honda'],   ['country' => 'Japan']);
        $hero    = Brand::firstOrCreate(['name' => 'Hero'],    ['country' => 'India']);
        $tvs     = Brand::firstOrCreate(['name' => 'TVS'],     ['country' => 'India']);
        $bajaj   = Brand::firstOrCreate(['name' => 'Bajaj'],   ['country' => 'India']);
        $yamaha  = Brand::firstOrCreate(['name' => 'Yamaha'],  ['country' => 'Japan']);
        $suzuki  = Brand::firstOrCreate(['name' => 'Suzuki'],  ['country' => 'Japan']);

        // ──────────────────────────────────────────────────────────────
        //  4. VEHICLE MODELS
        // ──────────────────────────────────────────────────────────────
        $models = [
            // Honda
            ['brand_id' => $honda->id,  'name' => 'Activa 6G',    'year' => 2024],
            ['brand_id' => $honda->id,  'name' => 'CB Shine',      'year' => 2024],
            ['brand_id' => $honda->id,  'name' => 'Unicorn 160',   'year' => 2024],
            // Hero
            ['brand_id' => $hero->id,   'name' => 'Splendor Plus', 'year' => 2024],
            ['brand_id' => $hero->id,   'name' => 'HF Deluxe',     'year' => 2024],
            ['brand_id' => $hero->id,   'name' => 'Xtreme 160R',   'year' => 2024],
            // TVS
            ['brand_id' => $tvs->id,    'name' => 'Jupiter',       'year' => 2024],
            ['brand_id' => $tvs->id,    'name' => 'Apache RTR 160','year' => 2024],
            // Bajaj
            ['brand_id' => $bajaj->id,  'name' => 'Pulsar NS 160', 'year' => 2024],
            ['brand_id' => $bajaj->id,  'name' => 'CT 100',        'year' => 2024],
            // Yamaha
            ['brand_id' => $yamaha->id, 'name' => 'FZ-S FI',       'year' => 2024],
            ['brand_id' => $yamaha->id, 'name' => 'R15 V4',        'year' => 2024],
            // Suzuki
            ['brand_id' => $suzuki->id, 'name' => 'Access 125',    'year' => 2024],
            ['brand_id' => $suzuki->id, 'name' => 'Gixxer 150',    'year' => 2024],
        ];

        $vehicleModels = collect($models)->map(
            fn($m) => VehicleModel::firstOrCreate(['brand_id' => $m['brand_id'], 'name' => $m['name']], $m)
        );

        // ──────────────────────────────────────────────────────────────
        //  5. VEHICLE VARIANTS (2–3 per model)
        // ──────────────────────────────────────────────────────────────
        $variantDefs = [
            'Activa 6G'    => [['STD', 'Pearl Sparkling Blue'], ['Deluxe', 'Matte Axis Grey Metallic'], ['H-Smart', 'Radiant Red Metallic']],
            'CB Shine'     => [['STD', 'Athletic Blue Metallic'], ['SP', 'Pearl Igneous Black']],
            'Unicorn 160'  => [['STD', 'Pearl Nightstar Black'], ['CBS', 'Matte Axis Grey']],
            'Splendor Plus' => [['STD', 'Heavy Grey'], ['XTEC', 'Candy Blazing Red'], ['i3S', 'Panther Black']],
            'HF Deluxe'    => [['STD', 'Force Silver'], ['IBS', 'Red']],
            'Xtreme 160R'  => [['STD', 'Blazing Red'], ['BS6', 'Force Silver']],
            'Jupiter'      => [['STD', 'Celestial Blue'], ['ZX', 'Royal Wine'], ['Classic', 'Autumn Orange']],
            'Apache RTR 160' => [['2V', 'Matte Red'], ['4V', 'Black']],
            'Pulsar NS 160' => [['STD', 'Ebony Black'], ['Twin Disc', 'Pewter Grey']],
            'CT 100'       => [['STD', 'Ebony Black'], ['B', 'Electronic Green']],
            'FZ-S FI'      => [['V3', 'Metallic Black'], ['Dark Matt Blue', 'Blue']],
            'R15 V4'       => [['STD', 'Metallic Red'], ['MotoGP Edition', 'Blue']],
            'Access 125'   => [['STD', 'Pearl Mira Red'], ['CBS', 'Glass Blaze White']],
            'Gixxer 150'   => [['STD', 'Glass Sparkle Black'], ['SF', 'Metallic Triton Blue']],
        ];

        $allVariants = collect();
        foreach ($vehicleModels as $vm) {
            $defs = $variantDefs[$vm->name] ?? [['STD', 'Black']];
            foreach ($defs as [$variantName, $color]) {
                $v = VehicleVariant::firstOrCreate(
                    ['model_id' => $vm->id, 'name' => $variantName],
                    ['color' => $color, 'is_active' => true]
                );
                $allVariants->push($v);
            }
        }

        // ──────────────────────────────────────────────────────────────
        //  6. VEHICLE STOCK (CHASSIS)
        // ──────────────────────────────────────────────────────────────
        $chassisData = [
            // [variant_name, model_name, chassis_no, engine_no, color, purchase, selling, status, branch, received_date]
            ['Activa 6G', 'STD',            'ME4KC131MR8001001', 'KC13E81001001', 'Pearl Sparkling Blue', 72000, 79990, 'available', 0, '-60 days'],
            ['Activa 6G', 'STD',            'ME4KC131MR8001002', 'KC13E81001002', 'Matte Axis Grey',      72000, 79990, 'available', 0, '-55 days'],
            ['Activa 6G', 'Deluxe',         'ME4KC131MR8002001', 'KC13E82001001', 'Matte Axis Grey Metallic', 75000, 83990, 'available', 1, '-50 days'],
            ['Activa 6G', 'H-Smart',        'ME4KC131MR8003001', 'KC13E83001001', 'Radiant Red Metallic', 78000, 87990, 'sold',      0, '-90 days'],
            ['CB Shine',  'STD',            'ME4JC36NMR0001101', 'JC36E01001101', 'Athletic Blue Metallic', 67000, 74990, 'available', 1, '-40 days'],
            ['CB Shine',  'SP',             'ME4JC36NMR0002101', 'JC36E02002101', 'Pearl Igneous Black',  69000, 77990, 'available', 2, '-35 days'],
            ['CB Shine',  'SP',             'ME4JC36NMR0002102', 'JC36E02002102', 'Pearl Igneous Black',  69000, 77990, 'sold',      1, '-80 days'],
            ['Unicorn 160','STD',           'ME4KC021MR0001201', 'KC02E01001201', 'Pearl Nightstar Black', 88000, 97990, 'available', 0, '-30 days'],
            ['Splendor Plus','STD',         'MBLHA10ED9HF00001', 'HA10EF9HF00001', 'Heavy Grey',         60000, 68990, 'available', 0, '-45 days'],
            ['Splendor Plus','XTEC',        'MBLHA10ED9HF00002', 'HA10EF9HF00002', 'Candy Blazing Red',  63000, 72990, 'available', 1, '-42 days'],
            ['Splendor Plus','i3S',         'MBLHA10ED9HF00003', 'HA10EF9HF00003', 'Panther Black',      62000, 71990, 'sold',      2, '-75 days'],
            ['HF Deluxe', 'STD',            'MBLHA10AE9HF10001', 'HA10AE9HF10001', 'Force Silver',       52000, 59990, 'available', 0, '-25 days'],
            ['HF Deluxe', 'IBS',            'MBLHA10AE9HF10002', 'HA10AE9HF10002', 'Red',                54000, 62990, 'available', 0, '-20 days'],
            ['Xtreme 160R','STD',           'MBLHA11EK9RF00001', 'HA11EK9RF00001', 'Blazing Red',        88000, 99990, 'available', 1, '-30 days'],
            ['Jupiter',   'STD',            'MD634KJ69R6J00001', 'BJ6J0001001',   'Celestial Blue',      73000, 82990, 'available', 0, '-28 days'],
            ['Jupiter',   'ZX',             'MD634KJ69R6J00002', 'BJ6J0002001',   'Royal Wine',          77000, 86990, 'available', 2, '-22 days'],
            ['Jupiter',   'Classic',        'MD634KJ69R6J00003', 'BJ6J0003001',   'Autumn Orange',       76000, 85990, 'sold',      0, '-60 days'],
            ['Apache RTR 160','2V',         'MD625FG8XR6J00001', 'BG8XR0001001',  'Matte Red',           92000, 104990, 'available', 1, '-18 days'],
            ['Apache RTR 160','4V',         'MD625FG8XR6J00002', 'BG8XR0002001',  'Black',              102000, 115990, 'available', 2, '-15 days'],
            ['Pulsar NS 160','STD',         'MD2A13CZ9RCE00001', '13CZ9RCE00001', 'Ebony Black',         85000, 96990, 'available', 0, '-20 days'],
            ['Pulsar NS 160','Twin Disc',   'MD2A13CZ9RCE00002', '13CZ9RCE00002', 'Pewter Grey',         90000, 101990, 'available', 0, '-18 days'],
            ['CT 100',    'STD',            'MD2A12BZ9RCT00001', '12BZ9RCT00001', 'Ebony Black',         40000, 46990, 'available', 2, '-10 days'],
            ['FZ-S FI',   'V3',             'ME17F1304R0001001', 'F1304R0001001', 'Metallic Black',       98000, 111990, 'available', 0, '-14 days'],
            ['R15 V4',    'STD',            'ME17F5C08R0001001', 'F5C08R0001001', 'Metallic Red',        145000, 168990, 'available', 1, '-8 days'],
            ['R15 V4',    'MotoGP Edition', 'ME17F5C08R0002001', 'F5C08R0002001', 'Blue',                155000, 178990, 'available', 0, '-5 days'],
            ['Access 125','STD',            'MB8CF8B11R9001001', 'CF8B11R9001001', 'Pearl Mira Red',      68000, 76990, 'available', 1, '-12 days'],
            ['Gixxer 150','STD',            'MB8A90401R9001001', 'A90401R9001001', 'Glass Sparkle Black', 96000, 109990, 'available', 2, '-10 days'],
        ];

        $branchIds = [$mainBranch->id, $northBranch->id, $southBranch->id];
        $stockByVariant = [];

        foreach ($chassisData as $row) {
            [$modelName, $variantName, $chassis, $engine, $color, $purchase, $selling, $status, $branchIdx, $receivedDays] = $row;

            $variant = $allVariants->first(fn($v) =>
                $v->name === $variantName &&
                $v->vehicleModel?->name === $modelName
            );

            if (!$variant) {
                // Try to load with relation
                $variant = VehicleVariant::with('vehicleModel')
                    ->whereHas('vehicleModel', fn($q) => $q->where('name', $modelName))
                    ->where('name', $variantName)->first();
            }

            if (!$variant) continue;

            $stock = VehicleStock::firstOrCreate(
                ['chassis_number' => $chassis],
                [
                    'variant_id'    => $variant->id,
                    'branch_id'     => $branchIds[$branchIdx],
                    'engine_number' => $engine,
                    'color'         => $color,
                    'purchase_price'=> $purchase,
                    'selling_price' => $selling,
                    'status'        => $status,
                    'received_date' => now()->modify($receivedDays)->toDateString(),
                ]
            );

            $stockByVariant[$variant->id][] = $stock;
        }

        // ──────────────────────────────────────────────────────────────
        //  7. CUSTOMERS
        // ──────────────────────────────────────────────────────────────
        $customerData = [
            ['Arjun Nair',      'arjun@gmail.com',     '9876543210', 'Koramangala, Bangalore'],
            ['Meena Reddy',     'meena@gmail.com',     '9845123456', 'Whitefield, Bangalore'],
            ['Suresh Babu',     'suresh@yahoo.com',    '9900011111', 'Jayanagar, Bangalore'],
            ['Kavitha S',       'kavitha@gmail.com',   '9123456789', 'Indiranagar, Bangalore'],
            ['Ramesh Joshi',    'ramesh@gmail.com',    '9009900990', 'HSR Layout, Bangalore'],
            ['Deepika Pillai',  'deepika@gmail.com',   '8888777766', 'BTM Layout, Bangalore'],
            ['Venkatesh G',     'venkatesh@yahoo.com', '9191919191', 'Marathahalli, Bangalore'],
            ['Anjali Mehta',    'anjali@gmail.com',    '9772233445', 'Electronic City, Bangalore'],
        ];
        $customers = collect($customerData)->map(fn($c) =>
            Customer::firstOrCreate(['email' => $c[1]], [
                'name' => $c[0], 'phone' => $c[2], 'address' => $c[3],
            ])
        );

        // ──────────────────────────────────────────────────────────────
        //  8. SUPPLIERS
        // ──────────────────────────────────────────────────────────────
        $suppliers = collect([
            ['Honda Distributor South', 'hondadist@south.com',   '080-22334455', 'Honda Nagar, Bangalore'],
            ['Hero Cycles Ltd',         'hero@supplier.com',     '080-33445566', 'Peenya Indl Area, Bangalore'],
            ['TVS Motor Parts',         'tvs@motorparts.com',    '080-44556677', 'Bommanahalli, Bangalore'],
            ['Bajaj Auto Spares',       'bajaj@spares.com',      '080-55667788', 'Domlur, Bangalore'],
            ['Synco Industries',        'synco@spares.com',      '080-66778899', 'Yeshwanthpur, Bangalore'],
        ])->map(fn($s) => Supplier::firstOrCreate(['email' => $s[1]], [
            'name' => $s[0], 'phone' => $s[2], 'address' => $s[3],
        ]));

        // ──────────────────────────────────────────────────────────────
        //  9. CATEGORIES & PRODUCTS (Spare Parts)
        // ──────────────────────────────────────────────────────────────
        $engineCat = Category::firstOrCreate(['name' => 'Engine Parts'], ['description' => 'Engine related spare parts']);
        $electricCat = Category::firstOrCreate(['name' => 'Electrical'],  ['description' => 'Electrical components']);
        $bodyworkCat = Category::firstOrCreate(['name' => 'Body Parts'],  ['description' => 'Body panels and plastics']);
        $filterCat   = Category::firstOrCreate(['name' => 'Filters'],     ['description' => 'Oil, air and fuel filters']);
        $brakeCat    = Category::firstOrCreate(['name' => 'Brakes'],      ['description' => 'Brake pads, discs, cables']);
        $tyresCat    = Category::firstOrCreate(['name' => 'Tyres & Tubes'], ['description' => 'Tyres and inner tubes']);
        $lubricantCat= Category::firstOrCreate(['name' => 'Lubricants'],  ['description' => 'Engine and gear oils']);

        $productData = [
            // [name, sku, category_id, brand_id, purchase, selling, min_qty_for_inventory]
            ['Engine Oil 10W30 1L',    'EO-10W30-1L',  $lubricantCat->id, $honda->id,  280,  320,  20],
            ['Engine Oil 20W40 1L',    'EO-20W40-1L',  $lubricantCat->id, $hero->id,   250,  290,  20],
            ['Air Filter Activa 6G',   'AF-ACT6G',     $filterCat->id,   $honda->id,  180,  220,  15],
            ['Air Filter Splendor',    'AF-SPLEN',     $filterCat->id,   $hero->id,   160,  200,  15],
            ['Oil Filter Honda',       'OF-HONDA',     $filterCat->id,   $honda->id,  120,  150,  25],
            ['Brake Shoe Front',       'BS-FRONT',     $brakeCat->id,    $hero->id,   280,  380,  10],
            ['Brake Shoe Rear',        'BS-REAR',      $brakeCat->id,    $hero->id,   260,  360,  10],
            ['Disc Brake Pad Set',     'DBP-SET',      $brakeCat->id,    $tvs->id,    450,  620,  8],
            ['Spark Plug Iridium',     'SP-IRID',      $engineCat->id,   $honda->id,  380,  490,  20],
            ['Spark Plug Standard',    'SP-STD',       $engineCat->id,   $hero->id,   180,  240,  20],
            ['Chain Kit (Set)',        'CK-SET',       $engineCat->id,   $bajaj->id,  680,  900,  8],
            ['Clutch Plate Set',       'CP-SET',       $engineCat->id,   $honda->id,  850,  1100, 5],
            ['Battery 12V 5Ah',        'BAT-12V5',     $electricCat->id, $honda->id,  1200, 1499, 5],
            ['Headlight LED H4',       'HL-LED-H4',    $electricCat->id, $tvs->id,    650,  880,  5],
            ['Indicator Bulb Set',     'IND-BULB',     $electricCat->id, $hero->id,   120,  160,  30],
            ['Front Tyre 80/100-18',   'TY-F-80100',   $tyresCat->id,   $tvs->id,    780,  990,  6],
            ['Rear Tyre 80/100-18',    'TY-R-80100',   $tyresCat->id,   $tvs->id,    820,  1050, 6],
            ['Front Mudguard Activa',  'FMG-ACT',      $bodyworkCat->id, $honda->id,  450,  620,  4],
            ['Side Panel Set',         'SPS-SPLEN',    $bodyworkCat->id, $hero->id,   320,  450,  4],
            ['Gear Oil 90 500ml',      'GO-90-500',    $lubricantCat->id, $bajaj->id, 120,  160,  15],
        ];

        $minQtys  = collect($productData)->pluck(6); // store min_qtys separately
        $products = collect($productData)->map(fn($p) =>
            Product::firstOrCreate(['sku' => $p[1]], [
                'name'          => $p[0],
                'category_id'   => $p[2],
                'brand_id'      => $p[3],
                'purchase_price'=> $p[4],
                'selling_price' => $p[5],
            ])
        );

        // ──────────────────────────────────────────────────────────────
        //  10. INVENTORY (stock per branch)
        // ──────────────────────────────────────────────────────────────
        $inventoryQtys = [25, 30, 8, 20, 40, 12, 14, 6, 35, 28, 10, 4, 7, 6, 50, 8, 7, 3, 5, 22];
        foreach ($products as $idx => $product) {
            $baseQty  = $inventoryQtys[$idx] ?? 10;
            $minQty   = $minQtys[$idx] ?? 5;

            foreach ([$mainBranch, $northBranch, $southBranch] as $bIdx => $branch) {
                $qty = (int)round($baseQty * (1 - $bIdx * 0.3) + rand(-3, 3));
                $qty = max(0, $qty);

                Inventory::firstOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $branch->id],
                    ['quantity' => $qty, 'min_quantity' => $minQty]
                );
            }
        }

        // ──────────────────────────────────────────────────────────────
        //  11. PRICE LOGS (for analytics — 90 days history)
        // ──────────────────────────────────────────────────────────────
        $priceLogProducts = $products->take(5);
        foreach ($priceLogProducts as $product) {
            $oldPurchase = $product->purchase_price;
            $oldSelling  = $product->selling_price;

            // Generate 5–8 price change events over last 90 days
            $days = collect(range(90, 5))->random(6)->sort()->values();
            foreach ($days as $daysAgo) {
                $newPurchase = round($oldPurchase * (1 + rand(-3, 8) / 100), 2);
                $newSelling  = round($oldSelling  * (1 + rand(-2, 10) / 100), 2);
                $oldMargin   = $oldPurchase > 0 ? round(($oldSelling - $oldPurchase) / $oldPurchase * 100, 2) : 0;
                $newMargin   = $newPurchase > 0 ? round(($newSelling - $newPurchase) / $newPurchase * 100, 2) : 0;

                PriceLog::create([
                    'product_id'       => $product->id,
                    'price_type'       => 'both',
                    'old_purchase_price'=> $oldPurchase,
                    'new_purchase_price'=> $newPurchase,
                    'old_selling_price' => $oldSelling,
                    'new_selling_price' => $newSelling,
                    'old_margin_percent'=> $oldMargin,
                    'new_margin_percent'=> $newMargin,
                    'reason'           => collect(['Market adjustment', 'Supplier price change', 'Seasonal offer', 'Festival discount', 'Cost increase'])->random(),
                    'changed_by'       => $admin->id,
                    'created_at'       => now()->subDays($daysAgo),
                    'updated_at'       => now()->subDays($daysAgo),
                ]);

                $oldPurchase = $newPurchase;
                $oldSelling  = $newSelling;
            }
        }

        // ──────────────────────────────────────────────────────────────
        //  12. PURCHASES (Supplier invoices)
        // ──────────────────────────────────────────────────────────────
        for ($p = 0; $p < 6; $p++) {
            $supplier  = $suppliers->random();
            $branch    = collect([$mainBranch, $northBranch])->random();
            $daysAgo   = rand(5, 60);
            $purchase  = Purchase::create([
                'reference_no'   => 'PO-' . str_pad($p + 1, 4, '0', STR_PAD_LEFT),
                'supplier_id'    => $supplier->id,
                'branch_id'      => $branch->id,
                'purchase_date'  => now()->subDays($daysAgo)->toDateString(),
                'status'         => collect(['received', 'pending'])->random(),
                'subtotal'       => 0,
                'total'          => 0,
            ]);

            $pTotal = 0;
            $selectedProducts = $products->random(rand(3, 6));
            foreach ($selectedProducts as $prod) {
                $qty      = rand(5, 20);
                $unitCost = $prod->purchase_price;
                $itemTotal= $qty * $unitCost;
                $pTotal  += $itemTotal;

                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $prod->id,
                    'quantity'     => $qty,
                    'cost_price'   => $unitCost,
                    'total'        => $itemTotal,
                ]);
            }

            $purchase->update([
                'subtotal' => $pTotal,
                'total'    => $pTotal,
            ]);
        }

        // ──────────────────────────────────────────────────────────────
        //  13. VEHICLE SALES (sold chassis → sale records)
        // ──────────────────────────────────────────────────────────────
        $soldStocks = VehicleStock::where('status', 'sold')->get();
        $invoiceCounter = 1;

        foreach ($soldStocks as $st) {
            // Skip if already has a sale
            if ($st->sale_id ?? false) continue;

            $customer   = $customers->random();
            $salesman   = $salesmen->random();
            $daysAgo    = rand(10, 80);
            $sellingPx  = $st->selling_price;
            $discount   = rand(0, 1) ? rand(500, 3000) : 0;
            $exchange   = rand(0, 1) ? rand(5000, 20000) : 0;
            $netPayable = $sellingPx - $discount - $exchange;
            $cashAmt    = (int)round($netPayable * rand(20, 60) / 100);
            $advanceAmt = (int)round($netPayable * rand(5, 15) / 100);
            $financeAmt = $netPayable - $cashAmt - $advanceAmt;

            $invoiceNo  = 'VS-' . now()->subDays($daysAgo)->format('Ym') . '-' . strtoupper(Str::random(4));
            $amtPaid    = $cashAmt + $advanceAmt;
            $status     = $financeAmt <= 0 ? 'paid' : ($amtPaid > 0 ? 'partial' : 'unpaid');

            $sale = Sale::create([
                'invoice_no'       => $invoiceNo,
                'sale_type'        => 'vehicle',
                'vehicle_stock_id' => $st->id,
                'customer_id'      => null,
                'customer_name'    => $customer->name,
                'branch_id'        => $st->branch_id,
                'sold_by'          => $admin->id,
                'salesman_id'      => $salesman->id,
                'subtotal'         => $sellingPx,
                'discount'         => $discount,
                'exchange'         => $exchange,
                'tax'              => 0,
                'total'            => $netPayable,
                'amount_paid'      => $amtPaid,
                'cash_amount'      => $cashAmt,
                'advance_amount'   => $advanceAmt,
                'finance_name'     => $financeAmt > 0 ? collect(['HDFC Bank', 'SBI Loans', 'Bajaj Finance', 'ICICI Bank'])->random() : null,
                'finance_amount'   => $financeAmt > 0 ? $financeAmt : 0,
                'payment_status'   => $status,
                'sale_date'        => now()->subDays($daysAgo)->toDateString(),
                'notes'            => 'Demo vehicle sale',
            ]);

            // Mark stock as sold (sale_id column doesn't exist, status already set)
            $st->update(['status' => 'sold']);

            $invoiceCounter++;
        }

        // ──────────────────────────────────────────────────────────────
        //  14. SPARE PARTS SALES (last 90 days for analytics)
        // ──────────────────────────────────────────────────────────────
        for ($s = 0; $s < 40; $s++) {
            $customer  = $customers->random();
            $branch    = collect([$mainBranch, $northBranch, $southBranch])->random();
            $salesman  = $salesmen->random();
            $daysAgo   = rand(1, 90);
            $numItems  = rand(1, 4);

            $items     = $products->random($numItems)->values();
            $subtotal  = 0;
            $itemRows  = [];

            foreach ($items as $prod) {
                $qty       = rand(1, 5);
                $unitPrice = $prod->selling_price;
                $discount  = rand(0, 1) ? rand(0, (int)($unitPrice * 0.1)) : 0;
                $total     = $qty * $unitPrice - $discount;
                $cost      = $prod->purchase_price * $qty;
                $subtotal += $total;
                $itemRows[] = compact('prod', 'qty', 'unitPrice', 'discount', 'total', 'cost');
            }

            $saleDiscount = rand(0, 1) ? rand(0, (int)($subtotal * 0.05)) : 0;
            $finalTotal   = $subtotal - $saleDiscount;
            $amtPaid      = rand(0, 1) ? $finalTotal : round($finalTotal * rand(50, 90) / 100);
            $status       = $amtPaid >= $finalTotal ? 'paid' : ($amtPaid > 0 ? 'partial' : 'unpaid');

            $sale = Sale::create([
                'invoice_no'    => 'INV-' . str_pad($invoiceCounter++, 5, '0', STR_PAD_LEFT),
                'sale_type'     => 'parts',
                'customer_id'   => $customer->id,
                'customer_name' => $customer->name,
                'branch_id'     => $branch->id,
                'sold_by'       => $salesman->id,
                'salesman_id'   => $salesman->id,
                'subtotal'      => $subtotal,
                'discount'      => $saleDiscount,
                'tax'           => 0,
                'total'         => $finalTotal,
                'amount_paid'   => $amtPaid,
                'payment_status'=> $status,
                'sale_date'     => now()->subDays($daysAgo)->toDateString(),
            ]);

            foreach ($itemRows as $ir) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $ir['prod']->id,
                    'quantity'   => $ir['qty'],
                    'unit_price' => $ir['unitPrice'],
                    'cost_price' => $ir['prod']->purchase_price,
                    'discount'   => $ir['discount'],
                    'total'      => $ir['total'],
                ]);
            }

            // Record payment if paid/partial
            if ($amtPaid > 0) {
                Payment::create([
                    'sale_id'      => $sale->id,
                    'amount'       => $amtPaid,
                    'method'       => collect(['cash', 'upi', 'bank_transfer', 'card'])->random(),
                    'payment_date' => now()->subDays($daysAgo)->toDateString(),
                    'reference'    => 'PAY-' . strtoupper(Str::random(6)),
                ]);
            }
        }

        // ──────────────────────────────────────────────────────────────
        //  15. EXPENSES (overhead — last 60 days)
        // ──────────────────────────────────────────────────────────────
        $expenseTypes = [
            ['Rent - Main Showroom',   35000, 'rent',       $mainBranch->id],
            ['Rent - North Branch',    18000, 'rent',       $northBranch->id],
            ['Electricity - Main',      4500, 'utilities',  $mainBranch->id],
            ['Staff Salary',           85000, 'salary',     $mainBranch->id],
            ['Transport / Logistics',   8500, 'transport',  $mainBranch->id],
            ['Advertising (Digital)',   12000,'marketing',  $mainBranch->id],
            ['Office Supplies',          800, 'misc',       $northBranch->id],
            ['Insurance Premium',       6500, 'insurance',  $mainBranch->id],
        ];

        foreach ($expenseTypes as $exp) {
            [$title, $amount, $category, $branchId] = $exp;
            Expense::firstOrCreate(
                ['title' => $title, 'expense_date' => now()->startOfMonth()->toDateString()],
                [
                    'amount'       => $amount,
                    'category'     => $category,
                    'branch_id'    => $branchId,
                    'created_by'   => $admin->id,
                    'expense_date' => now()->startOfMonth()->toDateString(),
                ]
            );
        }

        // Also create some last-month expenses
        foreach (array_slice($expenseTypes, 0, 4) as $exp) {
            [$title, $amount, $category, $branchId] = $exp;
            Expense::firstOrCreate(
                ['title' => $title, 'expense_date' => now()->subMonth()->startOfMonth()->toDateString()],
                [
                    'amount'       => $amount,
                    'category'     => $category,
                    'branch_id'    => $branchId,
                    'created_by'   => $admin->id,
                    'expense_date' => now()->subMonth()->startOfMonth()->toDateString(),
                ]
            );
        }

        // ──────────────────────────────────────────────────────────────
        //  16. STOCK MOVEMENTS (spare parts in/out)
        // ──────────────────────────────────────────────────────────────
        $movementTypes = ['in', 'out', 'adjustment'];
        for ($m = 0; $m < 30; $m++) {
            $product  = $products->random();
            $branch   = collect([$mainBranch, $northBranch, $southBranch])->random();
            $type     = $movementTypes[array_rand($movementTypes)];
            $qty      = rand(1, 10);
            $before   = rand(5, 40);
            $after    = $type === 'in' ? $before + $qty : max(0, $before - $qty);

            StockMovement::create([
                'product_id'      => $product->id,
                'branch_id'       => $branch->id,
                'type'            => $type,
                'quantity'        => $qty,
                'before_quantity' => $before,
                'after_quantity'  => $after,
                'reference_type'  => $type === 'out' ? 'sale' : 'purchase',
                'notes'           => $type === 'adjustment' ? 'Physical count correction' : null,
                'created_by'      => $admin->id,
                'created_at'      => now()->subDays(rand(1, 60)),
                'updated_at'      => now()->subDays(rand(1, 60)),
            ]);
        }

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Branches',          Branch::count()],
                ['Users',             User::count()],
                ['Brands',            Brand::count()],
                ['Vehicle Models',    VehicleModel::count()],
                ['Vehicle Variants',  VehicleVariant::count()],
                ['Vehicle Stock',     VehicleStock::count()],
                  ['  → Available',   VehicleStock::where('status','available')->count()],
                  ['  → Sold',        VehicleStock::where('status','sold')->count()],
                ['Customers',         Customer::count()],
                ['Suppliers',         Supplier::count()],
                ['Products (Parts)',  Product::count()],
                ['Sales (Total)',     Sale::count()],
                  ['  → Vehicle',     Sale::where('sale_type','vehicle')->count()],
                  ['  → Parts',       Sale::where('sale_type','parts')->count()],
                ['Price Log Entries', PriceLog::count()],
                ['Expenses',          Expense::count()],
                ['Stock Movements',   StockMovement::count()],
            ]
        );
        $this->command->newLine();
        $this->command->line('🔑 <fg=yellow>Login credentials:</>');
        $this->command->line('  Admin:       admin@showroom.com / password');
        $this->command->line('  Salesperson: ravi@showroom.com / password');
        $this->command->line('  Accountant:  acc@showroom.com / password');
    }
}
