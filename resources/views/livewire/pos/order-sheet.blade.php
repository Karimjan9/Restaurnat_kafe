<aside class="pos-rail-surface flex items-center gap-3 overflow-x-auto border-b border-white/10 px-3 py-3 text-white xl:flex-col xl:justify-between xl:overflow-visible xl:border-b-0 xl:border-r xl:px-2 xl:py-4">
    <div class="flex items-center gap-3 xl:flex-col xl:gap-4">
        <button type="button" wire:click="startNewOrder" class="pos-rail-link is-active">
            <span class="text-[11px] font-semibold tracking-[0.28em]">OR</span>
            <span class="mt-1 text-[10px] text-white/70">Order</span>
        </button>

        <a href="#pos-menu-canvas" class="pos-rail-link">
            <span class="text-[11px] font-semibold tracking-[0.28em]">MN</span>
            <span class="mt-1 text-[10px] text-white/70">Menu</span>
        </a>

        <a href="#pos-settlements" class="pos-rail-link">
            <span class="text-[11px] font-semibold tracking-[0.28em]">ST</span>
            <span class="mt-1 text-[10px] text-white/70">Settle</span>
        </a>

        <a href="#pos-receipts" class="pos-rail-link">
            <span class="text-[11px] font-semibold tracking-[0.28em]">RC</span>
            <span class="mt-1 text-[10px] text-white/70">Receipts</span>
        </a>
    </div>

    <div class="hidden xl:block">
        <div class="rounded-2xl border border-white/10 bg-white/10 px-3 py-3 text-center">
            <span class="pos-live-dot mx-auto block h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
            <p class="mt-2 text-[10px] uppercase tracking-[0.32em] text-white/70">Live</p>
        </div>
    </div>
</aside>

<section id="pos-order-composer" class="flex min-h-[32rem] flex-col border-b border-slate-200/80 bg-white/92 xl:border-b-0 xl:border-r">
    <div class="shrink-0 border-b border-slate-200/80 px-4 py-5 lg:px-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] uppercase tracking-[0.34em] text-violet-500">Order composer</p>
                <h3 class="mt-2 text-2xl font-semibold text-slate-900">
                    {{ $orderType === 'dine_in' ? 'Table order' : (config('pos.order_types')[$orderType] ?? 'New order') }}
                </h3>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $activeBranch?->name ?? 'Branch tanlanmagan' }} | {{ $cartItems->sum('quantity') }} item | {{ number_format((float) $subtotal) }} so'm
                </p>
            </div>

            <button type="button" wire:click="startNewOrder" class="rounded-full border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-medium text-violet-700 transition hover:border-violet-300 hover:bg-violet-100">
                + New
            </button>
        </div>

        <div class="mt-5 grid grid-cols-3 gap-2">
            @foreach (config('pos.order_types') as $type => $label)
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="orderType" value="{{ $type }}" class="peer sr-only">
                    <span class="block rounded-[1.35rem] border border-slate-200 bg-slate-50 px-3 py-3 text-slate-700 transition peer-checked:border-violet-400 peer-checked:bg-violet-600 peer-checked:text-white">
                        <span class="block text-[10px] uppercase tracking-[0.28em] opacity-70">{{ $orderTypeMeta[$type]['code'] }}</span>
                        <span class="mt-1 block text-sm font-semibold">{{ $label }}</span>
                        <span class="mt-1 block text-xs opacity-80">{{ $orderTypeMeta[$type]['hint'] }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="mt-5 space-y-3">
            @if ($orderType === 'dine_in')
                <div class="grid gap-3">
                    <label class="block">
                        <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Dining table</span>
                        <select wire:model.live="tableId" class="w-full rounded-[1.25rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                            <option value="">Select table</option>
                            @foreach ($availableTables as $table)
                                <option value="{{ $table->id }}">{{ $table->name }} | {{ $table->seats }} seats</option>
                            @endforeach
                        </select>
                        @error('tableId') <span class="mt-2 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Waiter</span>
                        <select wire:model.live="waiterUserId" class="w-full rounded-[1.25rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                            <option value="">Select waiter</option>
                            @foreach ($waiters as $waiter)
                                <option value="{{ $waiter->id }}">{{ $waiter->name }}</option>
                            @endforeach
                        </select>
                        @error('waiterUserId') <span class="mt-2 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </label>
                </div>
            @else
                <div class="grid gap-3">
                    <label class="block">
                        <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Customer name</span>
                        <input type="text" wire:model.live="customerName" class="w-full rounded-[1.25rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" placeholder="Customer name">
                        @error('customerName') <span class="mt-2 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Customer phone</span>
                        <input type="text" wire:model.live="customerPhone" class="w-full rounded-[1.25rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" placeholder="+998 90 000 00 00">
                        @error('customerPhone') <span class="mt-2 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </label>

                    @if ($orderType === 'delivery')
                        <label class="block">
                            <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Delivery address</span>
                            <textarea wire:model.live="deliveryAddress" rows="3" class="w-full rounded-[1.25rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" placeholder="Delivery address"></textarea>
                            @error('deliveryAddress') <span class="mt-2 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        </label>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="min-h-0 flex-1 px-4 py-5 lg:px-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] uppercase tracking-[0.34em] text-slate-500">Current items</p>
                <h4 class="mt-2 text-lg font-semibold text-slate-900">Live cart</h4>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                {{ $cartItems->count() }} lines
            </span>
        </div>

        <div class="pos-scroll mt-4 max-h-[24rem] space-y-3 pr-1">
            @forelse ($cartItems as $item)
                @php
                    $cartTheme = $stationThemes[$item['station']] ?? $stationThemes['kitchen'];
                @endphp
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-[0_16px_32px_-24px_rgba(15,23,42,0.35)] transition hover:-translate-y-0.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900">{{ $item['name'] }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-[11px] font-medium uppercase tracking-[0.24em] text-slate-500">
                                    {{ $item['category'] ?? 'Menu' }}
                                </span>
                                <span class="rounded-full border px-2.5 py-1 text-[11px] font-medium uppercase tracking-[0.24em] {{ $cartTheme['badge'] }}">
                                    {{ config("pos.product_stations.{$item['station']}", $item['station']) }}
                                </span>
                            </div>
                        </div>

                        <button type="button" wire:click="removeProduct({{ $item['id'] }})" class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-medium text-rose-600 transition hover:bg-rose-100">
                            Remove
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-[auto_1fr_auto] items-center gap-3">
                        <div class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 p-1">
                            <button type="button" wire:click="decrementQuantity({{ $item['id'] }})" class="h-8 w-8 rounded-full text-lg font-semibold text-slate-600 transition hover:bg-white">-</button>
                            <span class="inline-flex min-w-[2.2rem] items-center justify-center text-sm font-semibold text-slate-900">{{ $item['quantity'] }}</span>
                            <button type="button" wire:click="incrementQuantity({{ $item['id'] }})" class="h-8 w-8 rounded-full text-lg font-semibold text-slate-600 transition hover:bg-white">+</button>
                        </div>

                        <div class="text-sm text-slate-500">{{ number_format((float) $item['price']) }} so'm each</div>

                        <div class="text-right">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Line total</p>
                            <p class="mt-1 text-base font-semibold text-slate-900">{{ number_format((float) $item['line_total']) }} so'm</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50/80 p-6 text-center text-sm leading-7 text-slate-500">
                    Hali cart bo'sh. O'ng tomondagi menu kartalaridan mahsulot qo'shing.
                </div>
            @endforelse
        </div>
    </div>

    <div class="shrink-0 border-t border-slate-200/80 px-4 py-5 lg:px-5">
        <div class="space-y-4">
            @if ($orderType === 'dine_in')
                <div class="rounded-[1.4rem] border border-violet-200 bg-violet-50 px-4 py-4 text-sm leading-6 text-violet-700">
                    Dine-in order hozircha to'lanmaydi. Zakaz kitchen va barga yuboriladi, tayyor bo'lgach ofitsiant mijozga olib boradi.
                </div>
            @else
                <label class="block">
                    <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Payment method</span>
                    <select wire:model.live="paymentMethod" class="w-full rounded-[1.25rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                        @foreach (config('pos.payment_methods') as $method => $label)
                            <option value="{{ $method }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            <label class="block">
                <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Notes</span>
                <textarea wire:model.live="notes" rows="3" class="w-full rounded-[1.25rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" placeholder="Order note"></textarea>
            </label>

            @error('cart') <p class="text-sm text-rose-500">{{ $message }}</p> @enderror

            <div class="rounded-[1.75rem] border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.28em] text-emerald-700">Subtotal</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format((float) $subtotal) }} so'm</p>
                    </div>
                    <div class="text-right text-sm text-emerald-700">
                        <p>{{ $cartItems->sum('quantity') }} items</p>
                        <p>{{ config('pos.order_types')[$orderType] ?? $orderType }}</p>
                        @if ($orderType === 'dine_in')
                            <p>{{ $waiters->firstWhere('id', $waiterUserId)?->name ?? 'Waiter tanlanmagan' }}</p>
                        @endif
                    </div>
                </div>

                <button type="button" wire:click="checkout" wire:loading.attr="disabled" class="mt-4 w-full rounded-[1.25rem] bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-[0_24px_44px_-24px_rgba(16,185,129,0.85)] transition hover:-translate-y-0.5 hover:bg-emerald-400">
                    {{ $orderType === 'dine_in' ? 'Create order and send to stations' : 'Complete direct payment' }}
                </button>
            </div>
        </div>
    </div>
</section>
