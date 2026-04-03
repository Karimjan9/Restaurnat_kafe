@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Categories</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">Add category</h2>

                <form action="{{ route('categories.store') }}" method="POST" class="mt-5 grid gap-4">
                    @csrf

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Name</span>
                        <input type="text" name="name" class="input input-bordered bg-slate-950/70 text-white" placeholder="Burgers" required>
                    </label>

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Sort order</span>
                        <input type="number" name="sort_order" min="0" value="0" class="input input-bordered bg-slate-950/70 text-white">
                    </label>

                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-warning" checked>
                        <span class="label-text text-slate-300">Active category</span>
                    </label>

                    <button type="submit" class="btn btn-warning">Save category</button>
                </form>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Overview</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Current categories</h3>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach ($categories as $category)
                        <div class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-white">{{ $category->name }}</h4>
                                    <p class="text-sm text-slate-400">{{ $category->slug }}</p>
                                </div>
                                <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-ghost' }}">{{ $category->products_count }} products</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Manage</p>
            <h3 class="mt-2 text-xl font-semibold text-white">Edit existing categories</h3>

            <div class="mt-5 space-y-4">
                @foreach ($categories as $category)
                    <form action="{{ route('categories.update', $category) }}" method="POST" class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 xl:grid-cols-[1.2fr_0.6fr_auto]">
                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Name</span>
                                <input type="text" name="name" value="{{ $category->name }}" class="input input-bordered bg-slate-950/70 text-white" required>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Sort order</span>
                                <input type="number" name="sort_order" min="0" value="{{ $category->sort_order }}" class="input input-bordered bg-slate-950/70 text-white">
                            </label>

                            <div class="flex items-end gap-2">
                                <label class="label cursor-pointer gap-2">
                                    <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-warning" @checked($category->is_active)>
                                    <span class="label-text text-slate-300">Active</span>
                                </label>
                                <button type="submit" class="btn btn-warning">Save</button>
                                <button form="delete-category-{{ $category->id }}" type="submit" class="btn btn-outline btn-error">Delete</button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-category-{{ $category->id }}" action="{{ route('categories.destroy', $category) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
