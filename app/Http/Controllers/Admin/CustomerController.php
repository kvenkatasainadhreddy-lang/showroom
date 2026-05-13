<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"))
            ->withCount('sales')->latest()->paginate(20)->withQueryString();
        return view('admin.customers.index', compact('customers'));
    }

    public function create() { return view('admin.customers.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'phone' => 'nullable', 'email' => 'nullable|email', 'type' => 'required|in:individual,company']);
        Customer::create($request->all());
        return redirect()->route('admin.customers.index')->with('success', 'Customer added.');
    }

    public function show(Customer $customer)
    {
        $customer->load('sales.items');
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer) { return view('admin.customers.edit', compact('customer')); }

    public function update(Request $request, Customer $customer)
    {
        $request->validate(['name' => 'required', 'email' => 'nullable|email', 'type' => 'required|in:individual,company']);
        $customer->update($request->all());
        return redirect()->route('admin.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }
}
