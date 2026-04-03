@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Tables</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">Add dining table</h2>

                <form action="{{ route('tables.store') }}" method="POST" class="mt-5 grid gap-4">
                    @csrf

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Branch</span>
                        <select name="branch_id" class="select select-bordered bg-slate-950/70 text-white" required>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Table name</span>
                            <input type="text" name="name" class="input input-bordered bg-slate-950/70 text-white" placeholder="Table 1" required>
                        </label>

                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Seats</span>
                            <input type="number" name="seats" min="1" max="20" value="4" class="input input-bordered bg-slate-950/70 text-white" required>
                        </label>
                    </div>

                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-warning" checked>
                        <span class="label-text text-slate-300">Active table</span>
                    </label>

                    <button type="submit" class="btn btn-warning">Save table</button>
                </form>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Inventory</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Dining table list</h3>

                <div class="mt-5 overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr class="text-slate-400">
                                <th>Branch</th>
                                <th>Table</th>
                                <th>Seats</th>
                                <th>Orders</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tables as $table)
                                <tr>
                                    <td>{{ $table->branch?->name }}</td>
                                    <td>{{ $table->name }}</td>
                                    <td>{{ $table->seats }}</td>
                                    <td>{{ $table->orders_count }}</td>
                                    <td>
                                        <span class="badge {{ $table->is_active ? 'badge-success' : 'badge-ghost' }}">{{ $table->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Manage</p>
            <h3 class="mt-2 text-xl font-semibold text-white">Edit existing tables</h3>

            <div class="mt-5 space-y-4">
                @foreach ($tables as $table)
                    <form action="{{ route('tables.update', $table) }}" method="POST" class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 xl:grid-cols-[1fr_1fr_0.7fr_auto]">
                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Branch</span>
                                <select name="branch_id" class="select select-bordered bg-slate-950/70 text-white" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected($table->branch_id === $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Table name</span>
                                <input type="text" name="name" value="{{ $table->name }}" class="input input-bordered bg-slate-950/70 text-white" required>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Seats</span>
                                <input type="number" name="seats" min="1" max="20" value="{{ $table->seats }}" class="input input-bordered bg-slate-950/70 text-white" required>
                            </label>

                            <div class="flex items-end gap-2">
                                <label class="label cursor-pointer gap-2">
                                    <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-warning" @checked($table->is_active)>
                                    <span class="label-text text-slate-300">Active</span>
                                </label>
                                <button type="submit" class="btn btn-warning">Save</button>
                                <button form="delete-table-{{ $table->id }}" type="submit" class="btn btn-outline btn-error">Delete</button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-table-{{ $table->id }}" action="{{ route('tables.destroy', $table) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
