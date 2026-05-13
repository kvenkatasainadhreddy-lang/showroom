<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PriceLog;
use App\Models\Product;
use App\Models\VehicleVariant;
use App\Models\VehicleStock;
use Illuminate\Http\Request;

class PriceManagementController extends Controller
{
    // ── Index: vehicle prices primary, spare parts optional ───────
    public function index(Request $request)
    {

        // ── Vehicle Stock (available only) ───────────────────────
        $stockQuery = VehicleStock::with('variant.vehicleModel.brand')
            ->where('status', 'available')
            ->when($request->search, fn($q, $s) =>
                $q->where('chassis_number', 'like', "%$s%"))
            ->orderBy('chassis_number');
        $stock = $stockQuery->paginate(40, ['*'], 'spage')->withQueryString();

        // ── Grouped stock (variant_id + color) for Group Edit mode ─
        $allAvailable = VehicleStock::with('variant.vehicleModel.brand')
            ->where('status', 'available')
            ->when($request->search, fn($q, $s) =>
                $q->where('chassis_number', 'like', "%$s%"))
            ->orderBy('chassis_number')
            ->get();

        $stockGrouped = $allAvailable->groupBy(function ($s) {
            return $s->variant_id . '|' . strtolower(trim($s->color ?? ''));
        })->map(function ($group) {
            $first = $group->first();
            return (object)[
                'variant_id'      => $first->variant_id,
                'color'           => $first->color,
                'count'           => $group->count(),
                'purchase_price'  => $first->purchase_price,
                'selling_price'   => $first->selling_price,
                'variant'         => $first->variant,
                'chassis_sample'  => $group->pluck('chassis_number')->take(3)->implode(', '),
            ];
        })->values();

        // ── Spare Parts (only loaded when toggled on) ─────────────
        $products   = collect();
        $categories = collect();
        if ($request->boolean('parts')) {
            $categories = Category::orderBy('name')->get();
            $products   = Product::with('category')
                ->where('is_active', true)
                ->when($request->search,      fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%"))
                ->when($request->category_id, fn($q, $c) => $q->where('category_id', $c))
                ->orderBy('name')
                ->paginate(40, ['*'], 'ppage')
                ->withQueryString();
        }

        // ── Summary stats ─────────────────────────────────────────
        $stats = (object)[
            'available'  => VehicleStock::where('status', 'available')->count(),
            'sold'       => VehicleStock::where('status', 'sold')->count(),
            'products'   => Product::where('is_active', true)->count(),
            'logs_today' => PriceLog::whereDate('created_at', today())->count(),
        ];

        return view('admin.price-management.index',
            compact('stock', 'stockGrouped', 'products', 'categories', 'stats'));
    }

    // ── Bulk save prices ─────────────────────────────────────────
    public function update(Request $request)
    {
        $section = $request->input('section', 'stock'); // stock | products
        $reason  = $request->input('reason');
        $changed = 0;


        // ── VEHICLE STOCK ─────────────────────────────────────────
        if ($section === 'stock' && $request->has('stock')) {
            foreach ($request->stock as $row) {
                if (empty($row['stock_id'])) continue;
                $s = VehicleStock::find($row['stock_id']);
                if (!$s) continue;

                $newPurch = (float) ($row['purchase_price'] ?? $s->purchase_price);
                $newSell  = (float) ($row['selling_price']  ?? $s->selling_price);
                $oldPurch = (float) $s->purchase_price;
                $oldSell  = (float) $s->selling_price;

                if (abs($newPurch - $oldPurch) < 0.01 && abs($newSell - $oldSell) < 0.01) continue;

                $oldMargin = $oldPurch > 0 ? round((($oldSell - $oldPurch) / $oldPurch) * 100, 2) : 0;
                $newMargin = $newPurch > 0 ? round((($newSell - $newPurch) / $newPurch) * 100, 2) : 0;

                PriceLog::create([
                    'entity_type'         => 'vehicle_stock',
                    'price_field'         => abs($newPurch - $oldPurch) > 0.01 ? 'both' : 'selling_price',
                    'vehicle_stock_id'    => $s->id,
                    'price_type'          => 'both',
                    'old_purchase_price'  => $oldPurch,
                    'new_purchase_price'  => $newPurch,
                    'old_selling_price'   => $oldSell,
                    'new_selling_price'   => $newSell,
                    'old_margin_percent'  => $oldMargin,
                    'new_margin_percent'  => $newMargin,
                    'reason'              => $reason,
                    'changed_by'          => auth()->id(),
                ]);

                $s->update(['purchase_price' => $newPurch, 'selling_price' => $newSell]);
                $changed++;
            }
        }

        // ── SPARE PARTS PRODUCTS ──────────────────────────────────
        if ($section === 'products' && $request->has('products')) {
            foreach ($request->products as $row) {
                if (empty($row['product_id'])) continue;
                $product = Product::find($row['product_id']);
                if (!$product) continue;

                $newPurch = (float) ($row['purchase_price'] ?? $product->purchase_price);
                $newSell  = (float) ($row['selling_price']  ?? $product->selling_price);
                $oldPurch = (float) $product->purchase_price;
                $oldSell  = (float) $product->selling_price;

                if (abs($newPurch - $oldPurch) < 0.01 && abs($newSell - $oldSell) < 0.01) continue;

                $oldMargin = $oldPurch > 0 ? round((($oldSell - $oldPurch) / $oldPurch) * 100, 2) : 0;
                $newMargin = $newPurch > 0 ? round((($newSell - $newPurch) / $newPurch) * 100, 2) : 0;

                PriceLog::create([
                    'entity_type'        => 'product',
                    'price_field'        => 'both',
                    'product_id'         => $product->id,
                    'price_type'         => 'both',
                    'old_purchase_price' => $oldPurch,
                    'new_purchase_price' => $newPurch,
                    'old_selling_price'  => $oldSell,
                    'new_selling_price'  => $newSell,
                    'old_margin_percent' => $oldMargin,
                    'new_margin_percent' => $newMargin,
                    'reason'             => $reason,
                    'changed_by'         => auth()->id(),
                ]);

                $product->update(['purchase_price' => $newPurch, 'selling_price' => $newSell]);
                $changed++;
            }
        }

        if ($changed === 0) {
            return back()->with('info', 'No price changes detected — no records were updated.');
        }

        return back()->with('success', "$changed price record(s) updated and logged successfully.");
    }

    // ── Group price update (variant + color → all matching chassis) ─
    public function updateGroup(Request $request)
    {
        $request->validate([
            'groups'              => 'required|array',
            'groups.*.variant_id' => 'required|exists:vehicle_variants,id',
        ]);

        $reason  = $request->input('reason');
        $changed = 0;

        foreach ($request->groups as $row) {
            $variantId = $row['variant_id'];
            $color     = isset($row['color']) ? strtolower(trim($row['color'])) : null;
            $newPurch  = (float) ($row['purchase_price'] ?? 0);
            $newSell   = (float) ($row['selling_price']  ?? 0);

            // Find all available chassis matching this variant + color
            $query = VehicleStock::where('status', 'available')
                ->where('variant_id', $variantId);

            if ($color !== null && $color !== '') {
                $query->whereRaw('LOWER(TRIM(IFNULL(color,""))) = ?', [$color]);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('color')->orWhere('color', '');
                });
            }

            $chassis = $query->get();

            foreach ($chassis as $s) {
                $oldPurch = (float) $s->purchase_price;
                $oldSell  = (float) $s->selling_price;

                if (abs($newPurch - $oldPurch) < 0.01 && abs($newSell - $oldSell) < 0.01) continue;

                $oldMargin = $oldPurch > 0 ? round((($oldSell - $oldPurch) / $oldPurch) * 100, 2) : 0;
                $newMargin = $newPurch > 0 ? round((($newSell - $newPurch) / $newPurch) * 100, 2) : 0;

                PriceLog::create([
                    'entity_type'        => 'vehicle_stock',
                    'price_field'        => 'both',
                    'vehicle_stock_id'   => $s->id,
                    'price_type'         => 'both',
                    'old_purchase_price' => $oldPurch,
                    'new_purchase_price' => $newPurch,
                    'old_selling_price'  => $oldSell,
                    'new_selling_price'  => $newSell,
                    'old_margin_percent' => $oldMargin,
                    'new_margin_percent' => $newMargin,
                    'reason'             => $reason . ' [group]',
                    'changed_by'         => auth()->id(),
                ]);

                $s->update(['purchase_price' => $newPurch, 'selling_price' => $newSell]);
                $changed++;
            }
        }

        if ($changed === 0) {
            return back()->with('info', 'No price changes detected — no chassis were updated.');
        }

        return back()->with('success', "Group update complete: $changed chassis price(s) updated and logged.");
    }

    // ── Analytics ─────────────────────────────────────────────────
    public function analytics(Request $request)
    {
        $entityType = $request->get('entity_type', 'vehicle_stock');
        $entityId   = $request->get('entity_id');
        $days       = (int) ($request->get('days', 90));
        $from       = $request->get('from') ? \Carbon\Carbon::parse($request->from)->startOfDay() : now()->subDays($days);
        $to         = $request->get('to')   ? \Carbon\Carbon::parse($request->to)->endOfDay()     : now()->endOfDay();
        $search     = $request->get('search');
        $brandId    = $request->get('brand_id');
        $reasonFilter = $request->get('reason');

        // ── Base price log scope with date range ──────────────────
        $logScope = PriceLog::whereBetween('created_at', [$from, $to]);

        // ── Trend for selected entity ─────────────────────────────
        $trendData      = [];
        $selectedEntity = null;

        if ($entityId) {
            if ($entityType === 'vehicle_stock') {
                $selectedEntity = VehicleStock::with('variant.vehicleModel.brand')->find($entityId);
                $trendData = PriceLog::where('vehicle_stock_id', $entityId)
                    ->whereBetween('created_at', [$from, $to])
                    ->orderBy('created_at')
                    ->get(['created_at', 'old_selling_price', 'new_selling_price',
                           'old_purchase_price', 'new_purchase_price',
                           'old_margin_percent', 'new_margin_percent', 'reason'])
                    ->toArray();
            } elseif ($entityType === 'product') {
                $selectedEntity = Product::find($entityId);
                $trendData = PriceLog::where('product_id', $entityId)
                    ->whereBetween('created_at', [$from, $to])
                    ->orderBy('created_at')
                    ->get(['created_at', 'old_selling_price', 'new_selling_price',
                           'old_purchase_price', 'new_purchase_price',
                           'old_margin_percent', 'new_margin_percent', 'reason'])
                    ->toArray();
            }
        }

        // ── Daily change count (filtered) ─────────────────────────
        $dailyChanges = (clone $logScope)
            ->when($entityType !== 'all', fn($q) => $q->where('entity_type', $entityType))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Fill missing dates with 0 so chart looks continuous
        $filledDaily = [];
        $filledLabels = [];
        $cursor = $from->copy()->startOfDay();
        while ($cursor <= $to) {
            $key = $cursor->toDateString();
            $filledLabels[] = $cursor->format('d M');
            $filledDaily[]  = $dailyChanges[$key] ?? 0;
            $cursor->addDay();
        }

        // ── Direction stats (filtered by type) ────────────────────
        $directionStats = (clone $logScope)
            ->when($entityType !== 'all', fn($q) => $q->where('entity_type', $entityType))
            ->selectRaw('
                SUM(CASE WHEN new_selling_price > old_selling_price THEN 1 ELSE 0 END) as up,
                SUM(CASE WHEN new_selling_price < old_selling_price THEN 1 ELSE 0 END) as down,
                SUM(CASE WHEN new_selling_price = old_selling_price THEN 1 ELSE 0 END) as same
            ')->first();

        // ── Type breakdown (always global for doughnut) ───────────
        $typeBreakdown = (clone $logScope)
            ->selectRaw('entity_type, COUNT(*) as changes')
            ->groupBy('entity_type')
            ->pluck('changes', 'entity_type');

        // ── Top changed chassis (last period) ─────────────────────
        $topStocks = (clone $logScope)
            ->where('entity_type', 'vehicle_stock')
            ->selectRaw('vehicle_stock_id, COUNT(*) as changes')
            ->with('vehicleStock:id,chassis_number,variant_id')
            ->groupBy('vehicle_stock_id')
            ->orderByDesc('changes')
            ->limit(8)
            ->get();

        // ── Summary stats ─────────────────────────────────────────
        $summaryStats = (object)[
            'total_logs'     => (clone $logScope)->count(),
            'vehicle_logs'   => (clone $logScope)->where('entity_type', 'vehicle_stock')->count(),
            'parts_logs'     => (clone $logScope)->where('entity_type', 'product')->count(),
            'avg_sell_change'=> (clone $logScope)->selectRaw('AVG(ABS(new_selling_price - old_selling_price)) as avg')->value('avg') ?? 0,
        ];

        // ── Recent/filtered change log ────────────────────────────
        $logsQuery = PriceLog::with(
                'product:id,name,sku',
                'vehicleStock:id,chassis_number,variant_id',
                'vehicleStock.variant:id,name,model_id',
                'vehicleStock.variant.vehicleModel:id,name,brand_id',
                'vehicleStock.variant.vehicleModel.brand:id,name',
                'changedBy:id,name'
            )
            ->whereBetween('created_at', [$from, $to])
            ->when($entityType !== 'all', fn($q) => $q->where('entity_type', $entityType))
            ->when($entityId, function ($q) use ($entityType, $entityId) {
                if ($entityType === 'vehicle_stock') $q->where('vehicle_stock_id', $entityId);
                elseif ($entityType === 'product')   $q->where('product_id', $entityId);
            })
            ->when($search, function ($q, $s) {
                $q->whereHas('vehicleStock', fn($sq) => $sq->where('chassis_number', 'like', "%$s%"))
                  ->orWhereHas('product', fn($sq) => $sq->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%"))
                  ->orWhere('reason', 'like', "%$s%");
            })
            ->when($brandId, fn($q) =>
                $q->whereHas('vehicleStock.variant.vehicleModel', fn($sq) => $sq->where('brand_id', $brandId))
            )
            ->when($reasonFilter, fn($q, $r) => $q->where('reason', 'like', "%$r%"))
            ->latest()
            ->limit(50);

        $recentLogs = $logsQuery->get();

        // ── Selectors ─────────────────────────────────────────────
        $vehicleStocks = VehicleStock::with('variant.vehicleModel.brand')
            ->orderBy('chassis_number')->get(['id', 'chassis_number', 'variant_id']);
        $products      = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']);
        $brands        = \App\Models\Brand::orderBy('name')->get(['id', 'name']);

        return view('admin.price-management.analytics', compact(
            'trendData', 'selectedEntity', 'entityType', 'entityId',
            'filledDaily', 'filledLabels',
            'directionStats', 'typeBreakdown',
            'topStocks', 'summaryStats',
            'recentLogs', 'vehicleStocks', 'products', 'brands',
            'days', 'from', 'to', 'search', 'brandId', 'reasonFilter'
        ));
    }
}



