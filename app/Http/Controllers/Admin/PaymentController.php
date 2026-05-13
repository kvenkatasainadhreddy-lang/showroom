<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with('sale.customer', 'receivedBy')
            ->latest()->paginate(20)->withQueryString();
        return view('admin.payments.index', compact('payments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,card,bank_transfer,cheque,upi',
            'payment_date' => 'required|date',
        ]);

        $sale = Sale::findOrFail($request->sale_id);

        Payment::create(array_merge($request->all(), ['received_by' => auth()->id()]));

        // Update sale's amount_paid and status
        $totalPaid = $sale->payments()->sum('amount') + $request->amount;
        $status = $totalPaid >= $sale->total ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid');
        $sale->update(['amount_paid' => $totalPaid, 'payment_status' => $status]);

        return redirect()->back()->with('success', 'Payment of ₹' . number_format($request->amount, 2) . ' recorded.');
    }
}
