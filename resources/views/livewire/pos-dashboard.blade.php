<div class="space-y-6">
    <section class="soft-panel rounded-[2rem] border border-white/10 p-6 lg:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">POS terminal</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">Order yaratish, payment va receipt</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                    Dine-in, takeaway va delivery buyurtmalarini bir oqimda qabul qiling, darhol to‘lovni yakunlang va receipt oching.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Branch</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $branches->firstWhere('id', $branchId)?->name ?? 'N/A' }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Items</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $cartItems->sum('quantity') }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Subtotal</p>
                    <p class="mt-2 text-lg font-semibold text-amber-200">{{ number_format((float) $subtotal) }} so'm</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <section class="space-y-6">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="grid gap-4 lg:grid-cols-[1fr_0.9fr]">
                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Search product</span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="input input-bordered bg-slate-950/70 text-white" placeholder="Burger, coffee, sku...">
                    </label>

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Branch</span>
                        <select wire:model.live="branchId" class="select select-bordered bg-slate-950/70 text-white">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <button type="button" wire:click="setCategory('all')" class="btn btn-sm {{ $categoryId === 'all' ? 'btn-warning' : 'btn-ghost text-white/70' }}">
                        All
                    </button>
                    @foreach ($categories as $category)
                        <button type="button" wire:click="setCategory('{{ $category->id }}')" class="btn btn-sm {{ $categoryId === (string) $category->id ? 'btn-warning' : 'btn-ghost text-white/70' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @forelse ($products as $product)
                        <article class="rounded-[1.75rem] border border-white/10 bg-slate-950/60 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $product->category?->name }}</p>
                                    <h3 class="mt-2 text-lg font-semibold text-white">{{ $product->name }}</h3>
                                    <p class="mt-2 text-sm text-slate-400">{{ $product->description ?: 'No description' }}</p>
                                </div>
                                <span class="badge badge-outline">{{ $product->sku ?: 'SKU' }}</span>
                            </div>

                            <div class="mt-5 flex items-center justify-between">
                                <p class="text-xl font-semibold text-amber-200">{{ number_format((float) $product->price) }} so'm</p>
                                <button type="button" wire:click="addProduct({{ $product->id }})" class="btn btn-warning">Add</button>
                            </div>
                        </article>
                    @empty
                        <div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                            Filter bo‘yicha mahsulot topilmadi.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Recent receipts</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Latest paid orders</h3>
                    </div>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr class="text-slate-400">
                                <th>Order</th>
                                <th>Type</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <div>
                                            <p class="font-medium text-white">{{ $order->order_number }}</p>
                                            <p class="text-xs text-slate-500">{{ $order->cashier?->name }}</p>
                                        </div>
                                    </td>
                                    <td>{{ config('pos.order_types')[$order->order_type] ?? $order->order_type }}</td>
                                    <td>{{ number_format((float) $order->total) }} so'm</td>
                                    <td>
                                        <a href="{{ route('orders.receipt', $order) }}" class="btn btn-xs btn-outline btn-warning">Receipt</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-slate-400">Hozircha receipt yo‘q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Checkout</p>
                <h3 class="mt-2 text-2xl font-semibold text-white">Order details</h3>

                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach (config('pos.order_types') as $type => $label)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="orderType" value="{{ $type }}" class="peer sr-only">
                            <span class="inline-flex rounded-full border border-white/10 px-4 py-2 text-sm text-slate-300 transition peer-checked:border-amber-300/40 peer-checked:bg-amber-400/10 peer-checked:text-white">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-5 grid gap-4">
                    @if ($orderType === 'dine_in')
                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Dining table</span>
                            <select wire:model.live="tableId" class="select select-bordered bg-slate-950/70 text-white">
                                <option value="">Select table</option>
                                @foreach ($availableTables as $table)
                                    <option value="{{ $table->id }}">{{ $table->name }} · {{ $table->seats }} seats</option>
                                @endforeach
                            </select>
                            @error('tableId') <span class="mt-2 text-xs text-rose-300">{{ $message }}</span> @enderror
                        </label>
                    @else
                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Customer name</span>
                            <input type="text" wire:model.live="customerName" class="input input-bordered bg-slate-950/70 text-white" placeholder="Customer name">
                            @error('customerName') <span class="mt-2 text-xs text-rose-300">{{ $message }}</span> @enderror
                        </label>

                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Customer phone</span>
                            <input type="text" wire:model.live="customerPhone" class="input input-bordered bg-slate-950/70 text-white" placeholder="+998 90 000 00 00">
                            @error('customerPhone') <span class="mt-2 text-xs text-rose-300">{{ $message }}</span> @enderror
                        </label>

                        @if ($orderType === 'delivery')
                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Delivery address</span>
                                <textarea wire:model.live="deliveryAddress" rows="3" class="textarea textarea-bordered bg-slate-950/70 text-white" placeholder="Delivery address"></textarea>
                                @error('deliveryAddress') <span class="mt-2 text-xs text-rose-300">{{ $message }}</span> @enderror
                            </label>
                        @endif
                    @endif

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Payment method</span>
                        <select wire:model.live="paymentMethod" class="select select-bordered bg-slate-950/70 text-white">
                            @foreach (config('pos.payment_methods') as $method => $label)
                                <option value="{{ $method }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Notes</span>
                        <textarea wire:model.live="notes" rows="3" class="textarea textarea-bordered bg-slate-950/70 text-white" placeholder="Order note"></textarea>
                    </label>
                </div>
            </section>

            <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Cart</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Order items</h3>
                    </div>
                    <span class="badge badge-outline">{{ $cartItems->count() }} lines</span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($cartItems as $item)
                        <div class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-white">{{ $item['name'] }}</p>
                                    <p class="text-sm text-slate-400">{{ $item['category'] }}</p>
                                </div>
                                <button type="button" wire:click="removeProduct({{ $item['id'] }})" class="btn btn-xs btn-ghost text-rose-200">Remove</button>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <div class="join">
                                    <button type="button" wire:click="decrementQuantity({{ $item['id'] }})" class="btn btn-sm join-item">-</button>
                                    <button type="button" class="btn btn-sm join-item pointer-events-none">{{ $item['quantity'] }}</button>
                                    <button type="button" wire:click="incrementQuantity({{ $item['id'] }})" class="btn btn-sm join-item">+</button>
                                </div>
                                <p class="font-semibold text-amber-200">{{ number_format((float) $item['line_total']) }} so'm</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.75rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                            Hali cart bo‘sh. Chap tomondan mahsulot qo‘shing.
                        </div>
                    @endforelse
                </div>

                @error('cart') <p class="mt-4 text-sm text-rose-300">{{ $message }}</p> @enderror

                <div class="mt-5 rounded-[1.75rem] border border-emerald-400/20 bg-emerald-400/10 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300">Subtotal</span>
                        <span class="text-2xl font-semibold text-white">{{ number_format((float) $subtotal) }} so'm</span>
                    </div>
                    <button type="button" wire:click="checkout" wire:loading.attr="disabled" class="btn btn-warning mt-5 w-full rounded-2xl">
                        Complete payment
                    </button>
                </div>
            </section>
        </aside>
    </div>
</div>
