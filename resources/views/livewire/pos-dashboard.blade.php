<div class="space-y-6">
    <section class="soft-panel rounded-[2rem] border border-white/10 p-6 lg:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">POS terminal</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">Order yaratish, split bill va table close</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                    Yangi checkout order yarating yoki ofitsiant stol zakazini tanlab, full payment, equal split bill yoki table close orqali yakunlang.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Branch</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $branches->firstWhere('id', $branchId)?->name ?? 'N/A' }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Cart items</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $cartItems->sum('quantity') }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Direct subtotal</p>
                    <p class="mt-2 text-lg font-semibold text-amber-200">{{ number_format((float) $subtotal) }} so'm</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Table settlements</p>
                    <p class="mt-2 text-lg font-semibold text-emerald-300">{{ $serviceOrders->count() }}</p>
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
                                <div class="text-right">
                                    <span class="badge {{ $product->station === 'bar' ? 'badge-info' : 'badge-warning' }}">{{ $product->stationLabel() }}</span>
                                    <p class="mt-2 text-xs text-slate-500">{{ $product->sku ?: 'SKU' }}</p>
                                </div>
                            </div>

                            <div class="mt-5 flex items-center justify-between">
                                <p class="text-xl font-semibold text-amber-200">{{ number_format((float) $product->price) }} so'm</p>
                                <button type="button" wire:click="addProduct({{ $product->id }})" class="btn btn-warning">Add</button>
                            </div>
                        </article>
                    @empty
                        <div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                            Filter bo'yicha mahsulot topilmadi.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Waiter settlement</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Aktiv, to'langan va split kutayotgan stol orderlari</h3>
                    </div>

                    <label class="form-control w-full lg:max-w-sm">
                        <span class="label-text mb-2 text-slate-300">Order yoki stol qidiring</span>
                        <input type="text" wire:model.live.debounce.300ms="serviceOrderSearch" class="input input-bordered bg-slate-950/70 text-white" placeholder="SRV-..., Table 2">
                    </label>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @forelse ($serviceOrders as $serviceOrder)
                        <button
                            type="button"
                            wire:click="selectServiceOrder({{ $serviceOrder->id }})"
                            class="rounded-[1.5rem] border p-4 text-left transition {{ $selectedServiceOrder?->id === $serviceOrder->id ? 'border-amber-300/40 bg-amber-400/10' : 'border-white/10 bg-slate-950/50 hover:border-white/20' }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $serviceOrder->diningTable?->name ?? 'No table' }}</p>
                                    <h4 class="mt-2 text-lg font-semibold text-white">{{ $serviceOrder->order_number }}</h4>
                                    <p class="mt-2 text-xs text-slate-500">Waiter: {{ $serviceOrder->waiter?->name ?? 'N/A' }}</p>
                                </div>
                                <span class="badge {{ $serviceOrder->status === 'paid' ? 'badge-primary' : ($serviceOrder->status === 'served' ? 'badge-success' : ($serviceOrder->status === 'ready' ? 'badge-info' : ($serviceOrder->status === 'in_service' ? 'badge-warning' : 'badge-outline'))) }}">
                                    {{ $serviceOrder->serviceStatusLabel() }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-4 gap-2 text-center text-xs">
                                <div class="rounded-2xl bg-slate-950/60 px-2 py-2">
                                    <p class="text-slate-500">Items</p>
                                    <p class="mt-1 font-semibold text-white">{{ $serviceOrder->items->sum('quantity') }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-950/60 px-2 py-2">
                                    <p class="text-slate-500">Splits</p>
                                    <p class="mt-1 font-semibold text-white">{{ $serviceOrder->splits->count() }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-950/60 px-2 py-2">
                                    <p class="text-slate-500">Left</p>
                                    <p class="mt-1 font-semibold text-emerald-300">{{ $serviceOrder->splits->where('status', 'draft')->count() }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-950/60 px-2 py-2">
                                    <p class="text-slate-500">Total</p>
                                    <p class="mt-1 font-semibold text-amber-200">{{ number_format((float) $serviceOrder->total) }}</p>
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                            Bu filialda settlement kutayotgan stol order yo'q.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Recent receipts</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Latest completed orders</h3>
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
                                            <p class="text-xs text-slate-500">Waiter: {{ $order->waiter?->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-slate-500">Cashier: {{ $order->cashier?->name ?? 'N/A' }}</p>
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
                                    <td colspan="4" class="text-slate-400">Hozircha receipt yo'q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Settlement</p>
                        <h3 class="mt-2 text-2xl font-semibold text-white">{{ $selectedServiceOrder?->order_number ?? 'Order tanlang' }}</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            @if ($selectedServiceOrder)
                                {{ $selectedServiceOrder->diningTable?->name ?? 'No table' }} | {{ $selectedServiceOrder->serviceStatusLabel() }}
                            @else
                                Chap tomondan order tanlang.
                            @endif
                        </p>
                        @if ($selectedServiceOrder)
                            <p class="mt-2 text-xs text-slate-500">Waiter: {{ $selectedServiceOrder->waiter?->name ?? 'N/A' }}</p>
                        @endif
                    </div>

                    @if ($selectedServiceOrder)
                        <span class="badge {{ $selectedServiceOrder->status === 'paid' ? 'badge-primary' : ($selectedServiceOrder->status === 'served' ? 'badge-success' : ($selectedServiceOrder->status === 'ready' ? 'badge-info' : ($selectedServiceOrder->status === 'in_service' ? 'badge-warning' : 'badge-outline'))) }}">
                            {{ $selectedServiceOrder->serviceStatusLabel() }}
                        </span>
                    @endif
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($selectedServiceOrder?->items ?? collect() as $item)
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-white">{{ $item->product_name }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $item->quantity }} x {{ number_format((float) $item->unit_price) }} so'm</p>
                                </div>
                                <div class="text-right">
                                    <span class="badge {{ $item->preparation_status === 'served' ? 'badge-success' : ($item->preparation_status === 'ready' ? 'badge-info' : ($item->preparation_status === 'preparing' ? 'badge-warning' : 'badge-outline')) }}">
                                        {{ $item->preparationStatusLabel() }}
                                    </span>
                                    <p class="mt-2 text-xs uppercase tracking-[0.25em] text-slate-500">{{ config("pos.product_stations.{$item->station}", $item->station) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.75rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                            Settlement uchun tanlangan stol order yo'q.
                        </div>
                    @endforelse
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Total</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ number_format((float) ($selectedServiceOrder?->total ?? 0)) }} so'm</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Paid</p>
                        <p class="mt-2 text-lg font-semibold text-emerald-300">{{ number_format((float) ($selectedServiceOrder?->splitBillPaidAmount() ?? 0)) }} so'm</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Remaining</p>
                        <p class="mt-2 text-lg font-semibold text-amber-200">{{ number_format((float) ($selectedServiceOrder?->splitBillRemainingAmount() ?? 0)) }} so'm</p>
                    </div>
                </div>

                <label class="form-control mt-5">
                    <span class="label-text mb-2 text-slate-300">Settlement payment method</span>
                    <select wire:model.live="servicePaymentMethod" class="select select-bordered bg-slate-950/70 text-white">
                        @foreach (config('pos.payment_methods') as $method => $label)
                            <option value="{{ $method }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                @error('selectedServiceOrderId') <p class="mt-3 text-sm text-rose-300">{{ $message }}</p> @enderror
                @error('selectedSplitId') <p class="mt-3 text-sm text-rose-300">{{ $message }}</p> @enderror

                <div class="mt-5 rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Split bill</p>
                            <h4 class="mt-2 text-lg font-semibold text-white">Equal split</h4>
                        </div>
                        <span class="badge badge-outline">{{ $selectedServiceOrder?->splits->count() ?? 0 }} splits</span>
                    </div>

                    <div class="mt-4 flex gap-3">
                        <label class="form-control flex-1">
                            <span class="label-text mb-2 text-slate-300">Guests</span>
                            <input type="number" wire:model.live="splitCount" min="2" max="12" class="input input-bordered bg-slate-950/70 text-white">
                        </label>
                        <div class="flex items-end">
                            <button
                                type="button"
                                wire:click="createEqualSplits"
                                wire:loading.attr="disabled"
                                @disabled(! $selectedServiceOrder || $selectedServiceOrder->status !== 'served')
                                class="btn btn-outline btn-warning rounded-2xl"
                            >
                                {{ $selectedServiceOrder?->splits->isNotEmpty() ? 'Reset equal split' : 'Create equal split' }}
                            </button>
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-slate-400">
                        Split bill faqat `served` orderda ochiladi. Full payment qilingan orderni keyin split qilib bo'lmaydi.
                    </p>

                    @if ($selectedServiceOrder?->splits->isNotEmpty())
                        <div class="mt-5 space-y-3">
                            @foreach ($selectedServiceOrder->splits as $split)
                                <button
                                    type="button"
                                    wire:click="selectSplit({{ $split->id }})"
                                    class="w-full rounded-[1.5rem] border p-4 text-left transition {{ $selectedSplit?->id === $split->id ? 'border-amber-300/40 bg-amber-400/10' : 'border-white/10 bg-slate-950/60 hover:border-white/20' }}"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-white">{{ $split->label }}</p>
                                            <p class="mt-1 text-sm text-slate-400">
                                                @if ($split->paid_at)
                                                    {{ optional($split->paid_at)->format('d.m.Y H:i') }}
                                                @else
                                                    Payment kutilmoqda
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge {{ $split->status === 'paid' ? 'badge-success' : 'badge-outline' }}">{{ $split->statusLabel() }}</span>
                                            <p class="mt-2 text-lg font-semibold text-white">{{ number_format((float) $split->amount) }} so'm</p>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        <button
                            type="button"
                            wire:click="paySelectedSplit"
                            wire:loading.attr="disabled"
                            @disabled(! $selectedSplit || $selectedSplit->status !== 'draft' || ! $selectedServiceOrder || $selectedServiceOrder->status !== 'served')
                            class="btn btn-success mt-5 w-full rounded-2xl"
                        >
                            Pay selected split
                        </button>
                    @endif
                </div>

                <div class="mt-5 rounded-[1.75rem] border border-emerald-400/20 bg-emerald-400/10 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300">Settlement actions</span>
                        <span class="text-2xl font-semibold text-white">{{ number_format((float) ($selectedServiceOrder?->total ?? 0)) }} so'm</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-300">
                        @if ($selectedServiceOrder?->status === 'served' && $selectedServiceOrder->splits->isEmpty())
                            Order served. Full payment yoki split billni tanlashingiz mumkin.
                        @elseif ($selectedServiceOrder?->status === 'served')
                            Split bill ochilgan. Endi splitlarni bittalab to'lang.
                        @elseif ($selectedServiceOrder?->status === 'paid')
                            Payment yopilgan. Endi stolni close qilsangiz order arxivga o'tadi.
                        @elseif ($selectedServiceOrder)
                            To'lov actionlari served yoki paid bosqichida ochiladi.
                        @else
                            Chap tomondan order tanlang.
                        @endif
                    </p>

                    <div class="mt-5 grid gap-3">
                        <button
                            type="button"
                            wire:click="completeServiceOrderPayment"
                            wire:loading.attr="disabled"
                            @disabled(! $selectedServiceOrder || $selectedServiceOrder->status !== 'served' || $selectedServiceOrder->splits->isNotEmpty())
                            class="btn btn-success w-full rounded-2xl"
                        >
                            Finalize full payment
                        </button>

                        <button
                            type="button"
                            wire:click="closeSelectedServiceOrder"
                            wire:loading.attr="disabled"
                            @disabled(! $selectedServiceOrder || $selectedServiceOrder->status !== 'paid')
                            class="btn btn-warning w-full rounded-2xl"
                        >
                            Close table
                        </button>

                        @if ($selectedServiceOrder?->paid_at)
                            <a href="{{ route('orders.receipt', $selectedServiceOrder) }}" class="btn btn-outline w-full rounded-2xl">
                                Open receipt
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Checkout</p>
                <h3 class="mt-2 text-2xl font-semibold text-white">Direct order details</h3>

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
                        <h3 class="mt-2 text-xl font-semibold text-white">Direct checkout items</h3>
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
                            Hali cart bo'sh. Chap tomondan mahsulot qo'shing.
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
                        Complete direct payment
                    </button>
                </div>
            </section>
        </aside>
    </div>
</div>
