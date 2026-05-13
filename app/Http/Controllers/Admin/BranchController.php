<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index() { return view('admin.branches.index', ['branches' => Branch::paginate(20)]); }
    public function create() { return view('admin.branches.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:branches']);
        Branch::create($request->all());
        return redirect()->route('admin.branches.index')->with('success', 'Branch created.');
    }

    public function edit(Branch $branch) { return view('admin.branches.edit', compact('branch')); }

    public function update(Request $request, Branch $branch)
    {
        $request->validate(['name' => 'required|unique:branches,name,' . $branch->id]);
        $branch->update($request->all());
        return redirect()->route('admin.branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch) { $branch->delete(); return redirect()->route('admin.branches.index')->with('success', 'Branch deleted.'); }
}
