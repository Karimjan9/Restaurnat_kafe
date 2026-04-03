@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Products</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">Add product</h2>

                <form action="{{ route('products.store') }}" method="POST" class="mt-5 grid gap-4">
                    @csrf

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Category</span>
                        <select name="category_id" class="select select-bordered bg-slate-950/70 text-white" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Name</span>
                            <input type="text" name="name" class="input input-bordered bg-slate-950/70 text-white" required>
                        </label>

                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">SKU</span>
                            <input type="text" name="sku" class="input input-bordered bg-slate-950/70 text-white" placeholder="BG-001">
                        </label>
                    </div>

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Description</span>
                        <textarea name="description" rows="3" class="textarea textarea-bordered bg-slate-950/70 text-white"></textarea>
                    </label>

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Price (so'm)</span>
                        <input type="number" name="price" min="0" step="0.01" class="input input-bordered bg-slate-950/70 text-white" required>
                    </label>

                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-warning" checked>
                        <span class="label-text text-slate-300">Active product</span>
                    </label>

                    <button type="submit" class="btn btn-warning">Save product</button>
                </form>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Catalog</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Available menu items</h3>

                <div class="mt-5 space-y-3">
                    @foreach ($products as $product)
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $product->category?->name }}</p>
                                    <h4 class="mt-1 text-lg font-semibold text-white">{{ $product->name }}</h4>
                                    <p class="mt-1 text-sm text-slate-400">{{ $product->description }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-amber-200">{{ number_format((float) $product->price) }} so'm</p>
                                    <p class="text-xs text-slate-500">{{ $product->sku ?: 'No SKU' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Manage</p>
            <h3 class="mt-2 text-xl font-semibold text-white">Edit existing products</h3>

            <div class="mt-5 space-y-4">
                @foreach ($products as $product)
                    <form action="{{ route('products.update', $product) }}" method="POST" class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr_0.8fr_0.8fr_auto]">
                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Category</span>
                                <select name="category_id" class="select select-bordered bg-slate-950/70 text-white" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Name</span>
                                <input type="text" name="name" value="{{ $product->name }}" class="input input-bordered bg-slate-950/70 text-white" required>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">SKU</span>
                                <input type="text" name="sku" value="{{ $product->sku }}" class="input input-bordered bg-slate-950/70 text-white">
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Price</span>
                                <input type="number" name="price" min="0" step="0.01" value="{{ $product->price }}" class="input input-bordered bg-slate-950/70 text-white" required>
                            </label>

                            <div class="flex items-end gap-2">
                                <label class="label cursor-pointer gap-2">
                                    <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-warning" @checked($product->is_active)>
                                    <span class="label-text text-slate-300">Active</span>
                                </label>
                                <button type="submit" class="btn btn-warning">Save</button>
                                <button form="delete-product-{{ $product->id }}" type="submit" class="btn btn-outline btn-error">Delete</button>
                            </div>
                        </div>

                        <label class="form-control mt-4">
                            <span class="label-text mb-2 text-slate-300">Description</span>
                            <textarea name="description" rows="2" class="textarea textarea-bordered bg-slate-950/70 text-white">{{ $product->description }}</textarea>
                        </label>
                    </form>

                    <form id="delete-product-{{ $product->id }}" action="{{ route('products.destroy', $product) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
