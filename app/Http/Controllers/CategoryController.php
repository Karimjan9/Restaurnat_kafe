<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index', [
            'categories' => Category::withCount('products')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['name']);

        validator(['slug' => $slug], [
            'slug' => ['required', Rule::unique('categories', 'slug')],
        ])->validate();

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', "Kategoriya qo'shildi.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['name']);

        validator(['slug' => $slug], [
            'slug' => ['required', Rule::unique('categories', 'slug')->ignore($category->id)],
        ])->validate();

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Kategoriya yangilandi.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->loadCount('products');

        if ($category->products_count > 0) {
            return back()->with('error', "Kategoriyada mahsulotlar mavjud. Avval ularni ko'chiring yoki o'chiring.");
        }

        $category->delete();

        return back()->with('status', "Kategoriya o'chirildi.");
    }
}
