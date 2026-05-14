<?php

namespace App\Http\Controllers;

use App\Events\OperationsUpdated;
use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index', [
            'products' => Product::with(['category', 'modifierGroups'])->orderBy('name')->get(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'modifierGroups' => ModifierGroup::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'station' => ['required', Rule::in(array_keys(config('pos.product_stations')))],
            'is_active' => ['nullable', 'boolean'],
            'modifier_group_ids' => ['nullable', 'array'],
            'modifier_group_ids.*' => ['integer', 'exists:modifier_groups,id'],
        ]);

        $modifierGroupIds = $validated['modifier_group_ids'] ?? [];
        unset($validated['modifier_group_ids']);

        $product = Product::create([
            ...$validated,
            'cost_price' => $validated['cost_price'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);
        $product->modifierGroups()->sync($modifierGroupIds);

        OperationsUpdated::dispatch(
            type: 'menu.updated',
            station: $product->station,
            meta: ['product_id' => $product->id, 'action' => 'created'],
        );

        return back()->with('status', "Mahsulot qo'shildi.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product->id)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'station' => ['required', Rule::in(array_keys(config('pos.product_stations')))],
            'is_active' => ['nullable', 'boolean'],
            'modifier_group_ids' => ['nullable', 'array'],
            'modifier_group_ids.*' => ['integer', 'exists:modifier_groups,id'],
        ]);

        $modifierGroupIds = $validated['modifier_group_ids'] ?? [];
        unset($validated['modifier_group_ids']);

        $product->update([
            ...$validated,
            'cost_price' => $validated['cost_price'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);
        $product->modifierGroups()->sync($modifierGroupIds);

        OperationsUpdated::dispatch(
            type: 'menu.updated',
            station: $product->station,
            meta: ['product_id' => $product->id, 'action' => 'updated'],
        );

        return back()->with('status', 'Mahsulot yangilandi.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $station = $product->station;
        $productId = $product->id;
        $product->delete();

        OperationsUpdated::dispatch(
            type: 'menu.updated',
            station: $station,
            meta: ['product_id' => $productId, 'action' => 'deleted'],
        );

        return back()->with('status', "Mahsulot o'chirildi.");
    }
}
