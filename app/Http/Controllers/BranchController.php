<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('branches.index', [
            'branches' => Branch::withCount(['users', 'diningTables', 'orders'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Branch::create([
            ...$validated,
            'code' => Str::upper($validated['code']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', "Filial qo'shildi.");
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('branches', 'code')->ignore($branch->id)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch->update([
            ...$validated,
            'code' => Str::upper($validated['code']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Filial yangilandi.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->loadCount(['users', 'diningTables', 'orders']);

        if ($branch->users_count > 0 || $branch->dining_tables_count > 0 || $branch->orders_count > 0) {
            return back()->with('error', "Bog'langan ma'lumot borligi uchun filial o'chirilmadi.");
        }

        $branch->delete();

        return back()->with('status', "Filial o'chirildi.");
    }
}
