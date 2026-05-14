@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="soft-panel rounded-[2rem] border border-white/10 p-6 lg:p-8">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Menu modifiers</p>
                    <h2 class="mt-2 text-3xl font-semibold text-white">Options, sizes, extras and cooking choices</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-300">
                        Burger extra cheese, spice level, drink size va boshqa tanlovlar mahsulotga ulanadi va order item bilan saqlanadi.
                    </p>
                </div>

                <a href="{{ route('products.index') }}" class="btn btn-outline btn-warning">Product linking</a>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Create group</p>
                <h3 class="mt-2 text-xl font-semibold text-white">New modifier group</h3>

                <form action="{{ route('modifiers.groups.store') }}" method="POST" class="mt-5 grid gap-4">
                    @csrf
                    @include('modifiers.partials.group-fields', ['group' => null])
                    <button class="btn btn-warning">Save group</button>
                </form>
            </div>

            <div class="space-y-4">
                @foreach ($groups as $group)
                    <article class="soft-panel rounded-[2rem] border border-white/10 p-6">
                        <form action="{{ route('modifiers.groups.update', $group) }}" method="POST" class="grid gap-4">
                            @csrf
                            @method('PUT')

                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">{{ $group->code }}</p>
                                    <h4 class="mt-2 text-xl font-semibold text-white">{{ $group->name }}</h4>
                                    <p class="mt-2 text-sm text-slate-400">
                                        {{ $group->selection_type }} | min {{ $group->min_selected }} | max {{ $group->max_selected ?? 'N/A' }}
                                    </p>
                                </div>
                                <span class="badge {{ $group->is_active ? 'badge-success' : 'badge-ghost' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>

                            @include('modifiers.partials.group-fields', ['group' => $group])

                            <div class="flex flex-wrap gap-2">
                                <button class="btn btn-sm btn-warning">Update group</button>
                                <button form="delete-modifier-group-{{ $group->id }}" class="btn btn-sm btn-outline btn-error">Delete</button>
                            </div>
                        </form>

                        <form id="delete-modifier-group-{{ $group->id }}" action="{{ route('modifiers.groups.destroy', $group) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>

                        <div class="mt-6 border-t border-white/10 pt-5">
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Options</p>

                            <div class="mt-4 space-y-3">
                                @foreach ($group->options as $option)
                                    <form action="{{ route('modifiers.options.update', $option) }}" method="POST" class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                        @csrf
                                        @method('PUT')
                                        @include('modifiers.partials.option-fields', ['option' => $option])
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button class="btn btn-xs btn-warning">Save option</button>
                                            <button form="delete-modifier-option-{{ $option->id }}" class="btn btn-xs btn-outline btn-error">Delete</button>
                                        </div>
                                    </form>

                                    <form id="delete-modifier-option-{{ $option->id }}" action="{{ route('modifiers.options.destroy', $option) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endforeach
                            </div>

                            <form action="{{ route('modifiers.groups.options.store', $group) }}" method="POST" class="mt-4 rounded-[1.5rem] border border-amber-400/20 bg-amber-400/10 p-4">
                                @csrf
                                @include('modifiers.partials.option-fields', ['option' => null])
                                <button class="btn btn-sm btn-warning mt-3">Add option</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
@endsection
