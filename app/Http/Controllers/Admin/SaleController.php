<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Product;
use App\Services\SaleService;
use App\Exports\SalesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    public function __construct(private SaleService $saleService) {}

    public function index(Request $request)
    {
        $query = Sale::with('customer', 'branch', 'soldBy')
            ->when($request->search, fn($q, $s) => $q->where('invoice_no', 'like', "%$s%"))
            ->when($request->payment_status, fn($q, $s) => $q->where('payment_status', $s))
            ->when($request->from, fn($q, $f) => $q->whereDate('sale_date', '>=', $f))
            ->when($request->to, fn($q, $t) => $q->whereDate('sale_date', '<=', $t))
            ->latest();
        $sales = $query->paginate(20)->withQueryString();
        return view('admin.sales.index', compact('sales'));
    }

    public function create()
    {
        // Vehicle billing is the primary sale form
        return redirect()->route('admin.vehicle-sales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $sale = $this->saleService->createSale(
            $request->only('customer_id', 'discount', 'tax', 'amount_paid', 'notes', 'sale_date'),
            $request->input('items'),
            $request->input('branch_id'),
            auth()->id()
        );

        return redirect()->route('admin.sales.show', $sale)->with('success', 'Sale created: ' . $sale->invoice_no);
    }

    public function show(Sale $sale)
    {
        $sale->load(
            'customer', 'branch', 'soldBy',
            'items.product',
            'payments',
            'vehicleStock.variant.vehicleModel.brand'
        );
        return view('admin.sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        // Only allow editing of unpaid/partial sales (notes, discount, payment status)
        $sale->load('customer', 'branch', 'items.product');
        $customers = Customer::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.sales.edit', compact('sale', 'customers', 'branches'));
    }

    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $amountPaid = $request->input('amount_paid', $sale->amount_paid);
        $status = 'unpaid';
        if ($amountPaid >= $sale->total) $status = 'paid';
        elseif ($amountPaid > 0) $status = 'partial';

        $sale->update([
            'notes' => $request->notes,
            'amount_paid' => $amountPaid,
            'payment_status' => $status,
        ]);
        return redirect()->route('admin.sales.show', $sale)->with('success', 'Sale updated.');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('admin.sales.index')->with('success', 'Sale deleted.');
    }

    /**
     * Generate PDF invoice
     */
    public function invoice(Sale $sale)
    {
        $sale->load(
            'customer', 'branch', 'soldBy',
            'items.product',
            'payments',
            'vehicleStock.variant.vehicleModel.brand'
        );
        $pdf = Pdf::loadView('admin.sales.invoice-pdf', compact('sale'));
        return $pdf->download('Invoice-' . $sale->invoice_no . '.pdf');
    }

    /**
     * Export sales to Excel (two sheets: detail + totals)
     */
    public function export(Request $request)
    {
        $from     = $request->from ?: null;
        $to       = $request->to   ?: null;
        $datePart = ($from ? $from : 'start') . '_to_' . ($to ? $to : 'today');
        $filename = 'Sales_' . $datePart . '.xlsx';

        return Excel::download(new SalesExport($from, $to), $filename);
    }
}
