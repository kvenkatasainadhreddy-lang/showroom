<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Models\VehicleModel;
use App\Models\VehicleStock;
use App\Models\VehicleVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VehicleSaleController extends Controller
{
    // ── Create form ──────────────────────────────────────────────
    public function create()
    {
        $vehicleModels = VehicleModel::with('brand')->orderBy('name')->get();
        $customers     = Customer::orderBy('name')->get();
        $salesmen      = User::orderBy('name')->get();
        $branches      = Branch::where('is_active', true)->get();

        // Auto-generate bill number: VS-YYYYMM-XXXX
        $billNo = 'VS-' . now()->format('Ym') . '-' . strtoupper(Str::random(4));

        return view('admin.sales.create', compact(
            'vehicleModels', 'customers', 'salesmen', 'branches', 'billNo'
        ));
    }

    // ── Store ─────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_no'        => 'required|string|unique:sales,invoice_no',
            'branch_id'         => 'required|exists:branches,id',
            'sale_date'         => 'required|date',
            'customer_name'     => 'required|string|max:255',
            'customer_id'       => 'nullable|exists:customers,id',
            'salesman_id'       => 'nullable|exists:users,id',
            'vehicle_stock_id'  => 'required|exists:vehicle_stock,id',
            'total'             => 'required|numeric|min:0',
            'discount_type'     => 'nullable|in:amount,percent',
            'discount_value'    => 'nullable|numeric|min:0',
            'discount_amount'   => 'nullable|numeric|min:0',
            'exchange_amount'   => 'nullable|numeric|min:0',
            'cash_amount'       => 'nullable|numeric|min:0',
            'advance_amount'    => 'nullable|numeric|min:0',
            'finance_name'      => 'nullable|string|max:255',
            'finance_amount'    => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
        ]);

        // ── Payment sum validation ────────────────────────────────
        $cash       = (float) ($data['cash_amount']    ?? 0);
        $advance    = (float) ($data['advance_amount'] ?? 0);
        $finance    = (float) ($data['finance_amount'] ?? 0);
        $exchange   = (float) ($data['exchange_amount']  ?? 0);
        $discountAmt= (float) ($data['discount_amount']  ?? 0);
        $total      = (float) $data['total'];
        $netPayable = max(0, max(0, $total - $discountAmt) - $exchange);
        $sum        = $cash + $advance + $finance;

        if (abs($sum - $netPayable) > 1) {
            return back()->withInput()->withErrors(['payment_sum' =>
                "Amount mismatch: Cash (₹{$cash}) + Advance (₹{$advance}) + Finance (₹{$finance}) = ₹{$sum}, but Net Payable is ₹{$netPayable} (after discount ₹{$discountAmt} & exchange ₹{$exchange})."
            ]);
        }

        $balance = max(0, $netPayable - $sum);

        // ── Mark chassis as sold ──────────────────────────────────
        $stock = VehicleStock::findOrFail($data['vehicle_stock_id']);
        if ($stock->status !== 'available') {
            return back()->withInput()->withErrors(['vehicle_stock_id' => 'This vehicle is no longer available.']);
        }
        $stock->update(['status' => 'sold']);

        // ── Create Sale record ────────────────────────────────────
        Sale::create([
            'invoice_no'       => $data['invoice_no'],
            'sale_type'        => 'vehicle',
            'branch_id'        => $data['branch_id'],
            'sold_by'          => auth()->id(),
            'salesman_id'      => $data['salesman_id'] ?? null,
            'customer_id'      => $data['customer_id'] ?? null,
            'customer_name'    => $data['customer_name'],
            'vehicle_stock_id' => $data['vehicle_stock_id'],
            'subtotal'         => $total,
            'discount'         => $discountAmt,
            'exchange'         => $exchange,
            'tax'              => 0,
            'total'            => $netPayable,
            'amount_paid'      => $sum,
            'cash_amount'      => $cash,
            'advance_amount'   => $advance,
            'finance_name'     => $data['finance_name'] ?? null,
            'finance_amount'   => $finance,
            'balance_amount'   => $balance,
            'payment_status'   => $balance <= 0 ? 'paid' : ($sum > 0 ? 'partial' : 'unpaid'),
            'notes'            => trim(($data['discount_type'] === 'percent' ? 'Discount: ' . $data['discount_value'] . '% (₹' . $discountAmt . ') | ' : ($discountAmt > 0 ? 'Discount: ₹' . $discountAmt . ' | ' : '')) . ($data['notes'] ?? '')),
            'sale_date'        => $data['sale_date'],
        ]);

        return redirect()->route('admin.sales.index')
            ->with('success', "Vehicle sale {$data['invoice_no']} created successfully.");
    }

    // ── AJAX: Variants by model ───────────────────────────────────
    public function variantsByModel(VehicleModel $model)
    {
        $variants = VehicleVariant::where('model_id', $model->id)
            ->where('is_active', true)
            ->withCount(['stock as available_count' => fn($q) => $q->where('status', 'available')])
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return response()->json($variants);
    }

    // ── AJAX: Chassis numbers by variant ─────────────────────────
    public function stockByVariant(VehicleVariant $variant)
    {
        $stock = VehicleStock::where('variant_id', $variant->id)
            ->where('status', 'available')
            ->orderBy('chassis_number')
            ->get(['id', 'chassis_number', 'engine_number', 'color', 'selling_price']);

        // effective_price is the chassis selling_price directly
        $stock->each(fn($s) => $s->effective_price = (float)$s->selling_price);

        return response()->json($stock);
    }
}
