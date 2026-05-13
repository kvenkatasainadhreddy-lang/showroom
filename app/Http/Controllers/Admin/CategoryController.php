<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() { return view('admin.categories.index', ['categories' => Category::with('parent')->orderBy('type')->orderBy('name')->paginate(30)]); }
    public function create() { $parents = Category::whereNull('parent_id')->get(); return view('admin.categories.create', compact('parents')); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'type' => 'required|in:vehicle,part']);
        Category::create($request->all());
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category) { $parents = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get(); return view('admin.categories.edit', compact('category', 'parents')); }

    public function update(Request $request, Category $category) { $request->validate(['name' => 'required', 'type' => 'required|in:vehicle,part']); $category->update($request->all()); return redirect()->route('admin.categories.index')->with('success', 'Category updated.'); }

    public function destroy(Category $category) { $category->delete(); return redirect()->route('admin.categories.index')->with('success', 'Category deleted.'); }
}
