<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories', ['categories' => Category::withCount('equipment')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Category::create($request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]));

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, Category $category)
    {
        $category->update($request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ]));

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->equipment()->exists()) {
            return back()->with('error', 'This category still has equipment. Move or delete that equipment first.');
        }

        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
