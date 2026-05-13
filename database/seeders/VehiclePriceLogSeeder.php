<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriceLog;
use App\Models\User;
use App\Models\VehicleStock;

class VehiclePriceLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin   = User::first();
        $stocks  = VehicleStock::all();
        $reasons = [
            'Market adjustment', 'Festival discount', 'Seasonal offer',
            'Supplier rate change', 'Competitor price match', 'Regional price revision',
        ];

        $count = 0;
        foreach ($stocks as $st) {
            $oldP = (float) $st->purchase_price;
            $oldS = (float) $st->selling_price;

            // 4–6 price change events per chassis over last 90 days
            $numChanges = rand(4, 6);
            $daysAgo = collect(range(90, 5))
                ->shuffle()
                ->take($numChanges)
                ->sort()
                ->values();

            foreach ($daysAgo as $d) {
                $newP = round($oldP * (1 + rand(-2, 6) / 100), 2);
                $newS = round($oldS * (1 + rand(-2, 8) / 100), 2);
                $oldM = $oldP > 0 ? round(($oldS - $oldP) / $oldP * 100, 2) : 0;
                $newM = $newP > 0 ? round(($newS - $newP) / $newP * 100, 2) : 0;

                PriceLog::create([
                    'entity_type'        => 'vehicle_stock',
                    'price_field'        => 'both',
                    'vehicle_stock_id'   => $st->id,
                    'price_type'         => 'both',
                    'old_purchase_price' => $oldP,
                    'new_purchase_price' => $newP,
                    'old_selling_price'  => $oldS,
                    'new_selling_price'  => $newS,
                    'old_margin_percent' => $oldM,
                    'new_margin_percent' => $newM,
                    'reason'             => $reasons[array_rand($reasons)],
                    'changed_by'         => $admin->id,
                    'created_at'         => now()->subDays($d),
                    'updated_at'         => now()->subDays($d),
                ]);

                $oldP = $newP;
                $oldS = $newS;
                $count++;
            }
        }

        $this->command->info("✅ Added {$count} vehicle price log entries across {$stocks->count()} chassis.");
    }
}
