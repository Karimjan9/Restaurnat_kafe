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
                $inCartQty = $cart[$product->id] ?? 0;
            @endphp
            <button
                type="button"
                wire:click="addProduct({{ $product->id }})"
                wire:key="product-{{ $product->id }}"
                class="pos-product-card pos-grid-enter text-left"
                style="--tile-surface: {{ $productSurface }}; --tile-glow: {{ $stationTheme['glow'] }}; animation-delay: {{ $loop->index * 70 }}ms;"
            >
                <div class="relative z-10 flex h-full flex-col justify-between p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-medium uppercase tracking-[0.24em] text-white/80">
                            {{ $product->category?->name ?? 'Menu' }}
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
</section>
