<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Branch;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('branch', 'creator')
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->from, fn($q, $f) => $q->whereDate('expense_date', '>=', $f))
            ->when($request->to, fn($q, $t) => $q->whereDate('expense_date', '<=', $t))
            ->latest();
        $expenses = $query->paginate(20)->withQueryString();
        $totalFiltered = $query->sum('amount');
        return view('admin.expenses.index', compact('expenses', 'totalFiltered'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.expenses.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        Expense::create(array_merge($request->all(), ['created_by' => auth()->id()]));
        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.expenses.edit', compact('expense', 'branches'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate(['title' => 'required', 'amount' => 'required|numeric|min:0', 'expense_date' => 'required|date']);
        $expense->update($request->all());
        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted.');
    }
}
