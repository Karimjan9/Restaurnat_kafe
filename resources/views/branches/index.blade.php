@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Branches</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">Add branch</h2>

                <form action="{{ route('branches.store') }}" method="POST" class="mt-5 grid gap-4">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Code</span>
                            <input type="text" name="code" class="input input-bordered bg-slate-950/70 text-white" placeholder="MAIN" required>
                        </label>

                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Name</span>
                            <input type="text" name="name" class="input input-bordered bg-slate-950/70 text-white" placeholder="Main Branch" required>
                        </label>
                    </div>

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Phone</span>
                        <input type="text" name="phone" class="input input-bordered bg-slate-950/70 text-white">
                    </label>

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Address</span>
                        <textarea name="address" rows="3" class="textarea textarea-bordered bg-slate-950/70 text-white"></textarea>
                    </label>

                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-warning" checked>
                        <span class="label-text text-slate-300">Active branch</span>
                    </label>

                    <button type="submit" class="btn btn-warning">Save branch</button>
                </form>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Overview</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Current branch network</h3>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach ($branches as $branch)
                        <div class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.25em] text-slate-500">{{ $branch->code }}</p>
                                    <h4 class="mt-2 text-lg font-semibold text-white">{{ $branch->name }}</h4>
                                </div>
                                <span class="badge {{ $branch->is_active ? 'badge-success' : 'badge-ghost' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <p class="mt-3 text-sm text-slate-300">{{ $branch->address ?: 'Address not set' }}</p>
                            <div class="mt-4 flex gap-2 text-xs text-slate-400">
                                <span>{{ $branch->users_count }} staff</span>
                                <span>{{ $branch->dining_tables_count }} tables</span>
                                <span>{{ $branch->orders_count }} orders</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Manage</p>
            <h3 class="mt-2 text-xl font-semibold text-white">Edit existing branches</h3>

            <div class="mt-5 space-y-4">
                @foreach ($branches as $branch)
                    <form action="{{ route('branches.update', $branch) }}" method="POST" class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 xl:grid-cols-[0.6fr_1fr_0.8fr_1.1fr_auto]">
                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Code</span>
                                <input type="text" name="code" value="{{ $branch->code }}" class="input input-bordered bg-slate-950/70 text-white" required>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Name</span>
                                <input type="text" name="name" value="{{ $branch->name }}" class="input input-bordered bg-slate-950/70 text-white" required>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Phone</span>
                                <input type="text" name="phone" value="{{ $branch->phone }}" class="input input-bordered bg-slate-950/70 text-white">
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Address</span>
                                <input type="text" name="address" value="{{ $branch->address }}" class="input input-bordered bg-slate-950/70 text-white">
                            </label>

                            <div class="flex items-end gap-2">
                                <label class="label cursor-pointer gap-2">
                                    <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-warning" @checked($branch->is_active)>
                                    <span class="label-text text-slate-300">Active</span>
                                </label>
                                <button type="submit" class="btn btn-warning">Save</button>
                                <button form="delete-branch-{{ $branch->id }}" type="submit" class="btn btn-outline btn-error">Delete</button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-branch-{{ $branch->id }}" action="{{ route('branches.destroy', $branch) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
