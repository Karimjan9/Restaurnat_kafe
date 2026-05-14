<section id="pos-menu-canvas" class="pos-screen-card rounded-[2rem] border border-white/70 p-4 lg:p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-[11px] uppercase tracking-[0.34em] text-slate-500">Menu categories</p>
            <h4 class="mt-2 text-xl font-semibold text-slate-900">Tap-friendly product grid</h4>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="setCategory('all')" class="pos-filter-pill {{ $categoryId === 'all' ? 'is-active' : '' }}">
                All
                <span class="ml-2 rounded-full bg-white/70 px-2 py-0.5 text-[11px] text-slate-500">{{ $products->count() }}</span>
            </button>
            @foreach ($categories as $category)
                <button type="button" wire:click="setCategory('{{ $category->id }}')" class="pos-filter-pill {{ $categoryId === (string) $category->id ? 'is-active' : '' }}">
                    {{ $category->name }}
                    <span class="ml-2 rounded-full bg-white/70 px-2 py-0.5 text-[11px] text-slate-500">{{ $categoryCounts[$category->id] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse ($products as $product)
            @php
                $stationTheme = $stationThemes[$product->station] ?? $stationThemes['kitchen'];
                $categoryKey = \Illuminate\Support\Str::slug($product->category?->name ?? 'default');
                $productSurface = $categoryThemes[$categoryKey] ?? $categoryThemes['default'];
                $inCartQty = collect($cart)->sum(fn ($line) => is_array($line) && (int) ($line['product_id'] ?? 0) === $product->id ? (int) ($line['quantity'] ?? 0) : 0);
                $hasModifiers = $product->activeModifierGroups->isNotEmpty();
            @endphp
            <button
                type="button"
                wire:click="{{ $hasModifiers ? 'configureProduct' : 'addProduct' }}({{ $product->id }})"
                wire:key="product-{{ $product->id }}"
                class="pos-product-card pos-grid-enter text-left"
                style="--tile-surface: {{ $productSurface }}; --tile-glow: {{ $stationTheme['glow'] }}; animation-delay: {{ $loop->index * 70 }}ms;"
            >
                <div class="relative z-10 flex h-full flex-col justify-between p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-medium uppercase tracking-[0.24em] text-white/80">
                            {{ $hasModifiers ? $product->activeModifierGroups->count().' modifiers' : ($product->category?->name ?? 'Menu') }}
                        </div>
                        <div class="rounded-full border border-white/20 bg-slate-950/25 px-3 py-1 text-sm font-semibold text-white">
                            {{ number_format((float) $product->price) }}
                        </div>
                    </div>

                    <div class="pt-10">
                        <p class="text-[10px] uppercase tracking-[0.3em] text-white/65">{{ $product->sku ?: 'SKU' }}</p>
                        <h5 class="mt-2 max-w-[13rem] text-xl font-semibold text-white">{{ $product->name }}</h5>
                        <p class="mt-2 max-w-[14rem] text-sm leading-6 text-white/80">
                            {{ \Illuminate\Support\Str::limit($product->description ?: 'Quick add product for direct checkout.', 70) }}
                        </p>
                    </div>

                    <div class="mt-5 flex items-end justify-between gap-3">
                        <div class="rounded-full border px-3 py-1 text-[11px] font-medium uppercase tracking-[0.24em] {{ $product->station === 'bar' ? 'border-sky-200/40 bg-sky-100/10 text-sky-100' : 'border-amber-200/40 bg-amber-100/10 text-amber-50' }}">
                            {{ $product->stationLabel() }}
                        </div>

                        @if ($inCartQty > 0)
                            <div class="rounded-full border border-white/20 bg-white/15 px-3 py-1 text-sm font-semibold text-white">
                                {{ $inCartQty }}x
                            </div>
                        @elseif ($hasModifiers)
                            <div class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-sm font-semibold text-white">
                                Customize
                            </div>
                        @else
                            <div class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-sm font-semibold text-white">
                                Add
                            </div>
                        @endif
                    </div>
                </div>

                <div class="absolute bottom-3 right-4 text-4xl font-semibold tracking-[0.32em] text-white/10">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 2)) }}
                </div>
            </button>
        @empty
            <div class="sm:col-span-2 xl:col-span-3 2xl:col-span-4 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50/80 p-8 text-center text-sm leading-7 text-slate-500">
                Filter bo'yicha mahsulot topilmadi.
            </div>
        @endforelse
    </div>

    @if ($configuringProduct)
        <div class="mt-5 rounded-[2rem] border border-violet-200 bg-white p-5 shadow-[0_24px_44px_-30px_rgba(109,40,217,0.45)]">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.34em] text-violet-500">Customize item</p>
                    <h4 class="mt-2 text-2xl font-semibold text-slate-900">{{ $configuringProduct->name }}</h4>
                    <p class="mt-2 text-sm text-slate-500">{{ number_format((float) $configuringProduct->price) }} so'm base price</p>
                </div>
                <button type="button" wire:click="cancelProductConfiguration" class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600">
                    Cancel
                </button>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                @foreach ($configuringProduct->activeModifierGroups as $group)
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $group->name }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.22em] text-slate-500">
                                    {{ $group->selection_type }} | min {{ $group->min_selected }} | max {{ $group->max_selected ?? 'N/A' }}
                                </p>
                            </div>
                            @if ($group->is_required)
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">Required</span>
                            @endif
                        </div>

                        <div class="mt-4 grid gap-2">
                            @foreach ($group->activeOptions as $option)
                                @php($selected = in_array($option->id, array_map('intval', $configuredModifierOptions[$group->id] ?? []), true))
                                <button
                                    type="button"
                                    wire:click="toggleModifierOption({{ $group->id }}, {{ $option->id }})"
                                    class="flex items-center justify-between rounded-[1.1rem] border px-4 py-3 text-left transition {{ $selected ? 'border-violet-300 bg-violet-100 text-violet-800' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}"
                                >
                                    <span class="font-medium">{{ $option->name }}</span>
                                    <span class="text-sm">{{ (float) $option->price_delta > 0 ? '+'.number_format((float) $option->price_delta) : 'Included' }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-[0.7fr_1fr_auto]">
                <label class="block">
                    <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Quantity</span>
                    <input type="number" wire:model.live="configuredQuantity" min="1" max="99" class="w-full rounded-[1.25rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800">
                </label>
                <label class="block">
                    <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Cooking note</span>
                    <input type="text" wire:model.live="configuredItemNote" maxlength="500" class="w-full rounded-[1.25rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800" placeholder="No onion, well done, less ice...">
                </label>
                <div class="flex items-end">
                    <button type="button" wire:click="addConfiguredProduct" class="w-full rounded-[1.25rem] bg-emerald-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-400">
                        Add configured
                    </button>
                </div>
            </div>

            @error('configuredModifierOptions')
                <p class="mt-3 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
    @endif
</section>
