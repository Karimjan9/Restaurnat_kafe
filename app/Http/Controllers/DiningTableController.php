<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DiningTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    public function index(): View
    {
        return view('tables.index', [
            'tables' => DiningTable::with('branch')->withCount('orders')->orderBy('branch_id')->orderBy('name')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dining_tables', 'name')->where(fn ($query) => $query->where('branch_id', $request->integer('branch_id'))),
            ],
            'seats' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DiningTable::create([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', "Stol qo'shildi.");
    }

    public function update(Request $request, DiningTable $table): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dining_tables', 'name')
                    ->where(fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
                    ->ignore($table->id),
            ],
            'seats' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $table->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Stol yangilandi.');
    }

    public function destroy(DiningTable $table): RedirectResponse
    {
        $table->delete();

        return back()->with('status', "Stol o'chirildi.");
    }
}
