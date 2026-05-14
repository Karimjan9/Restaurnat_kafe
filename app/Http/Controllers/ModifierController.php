<?php

namespace App\Http\Controllers;

use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModifierController extends Controller
{
    public function index(): View
    {
        return view('modifiers.index', [
            'groups' => ModifierGroup::with('options')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $this->validateGroup($request);

        ModifierGroup::create($validated);

        return back()->with('status', "Modifier group qo'shildi.");
    }

    public function updateGroup(Request $request, ModifierGroup $group): RedirectResponse
    {
        $validated = $this->validateGroup($request, $group);

        $group->update($validated);

        return back()->with('status', 'Modifier group yangilandi.');
    }

    public function destroyGroup(ModifierGroup $group): RedirectResponse
    {
        $group->delete();

        return back()->with('status', "Modifier group o'chirildi.");
    }

    public function storeOption(Request $request, ModifierGroup $group): RedirectResponse
    {
        $validated = $this->validateOption($request, $group);

        $group->options()->create($validated);

        return back()->with('status', "Modifier option qo'shildi.");
    }

    public function updateOption(Request $request, ModifierOption $option): RedirectResponse
    {
        $validated = $this->validateOption($request, $option->group, $option);

        $option->update($validated);

        return back()->with('status', 'Modifier option yangilandi.');
    }

    public function destroyOption(ModifierOption $option): RedirectResponse
    {
        $option->delete();

        return back()->with('status', "Modifier option o'chirildi.");
    }

    protected function validateGroup(Request $request, ?ModifierGroup $group = null): array
    {
        $request->merge([
            'code' => $request->filled('code')
                ? $request->input('code')
                : Str::slug((string) $request->input('name'), '_'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('modifier_groups', 'code')->ignore($group?->id)],
            'selection_type' => ['required', Rule::in(['single', 'multiple'])],
            'is_required' => ['nullable', 'boolean'],
            'min_selected' => ['required', 'integer', 'min:0', 'max:20'],
            'max_selected' => ['nullable', 'integer', 'min:1', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_required'] = $request->boolean('is_required');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($validated['selection_type'] === 'single') {
            $validated['max_selected'] = 1;
        }

        if ($validated['is_required'] && $validated['min_selected'] < 1) {
            $validated['min_selected'] = 1;
        }

        return $validated;
    }

    protected function validateOption(Request $request, ModifierGroup $group, ?ModifierOption $option = null): array
    {
        $request->merge([
            'code' => $request->filled('code')
                ? $request->input('code')
                : Str::slug((string) $request->input('name'), '_'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('modifier_options', 'code')
                    ->where(fn ($query) => $query->where('modifier_group_id', $group->id))
                    ->ignore($option?->id),
            ],
            'price_delta' => ['nullable', 'numeric', 'min:-999999', 'max:999999999'],
            'cost_delta' => ['nullable', 'numeric', 'min:-999999', 'max:999999999'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['price_delta'] = $validated['price_delta'] ?? 0;
        $validated['cost_delta'] = $validated['cost_delta'] ?? 0;
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }
}
