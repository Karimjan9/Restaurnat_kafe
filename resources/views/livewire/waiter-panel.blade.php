<div class="space-y-12">

    <section class="soft-panel rounded-[2rem] border border-white/10 p-6 lg:p-8">
        <div class="flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Waiter terminal</p>

                <h2 class="mt-3 text-3xl font-semibold text-white">Stol buyurtmasini qabul qilish paneli</h2>
                <p class="mt-4 max-w-3xl text-sm leading-6 text-slate-300">
                    Ofitsiant buyurtmani stolga bog'laydi, taomlar oshxonaga, ichimliklar esa barga avtomatik navbat sifatida yuboriladi.
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Branch</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $branch?->name ?? 'Branch tanlanmagan' }}</p>
                </div>

                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Active tables</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $activeTablesCount }}</p>
                </div>

                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-emerald-300">Ready to serve</p>
                    <p class="mt-2 text-lg font-semibold text-emerald-300">{{ $readyTablesCount }}</p>
                </div>

                <h2 class="mt-2 text-3xl font-semibold text-white">Bugungi servis orderlari</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                    Bu ekranda faqat sizga biriktirilgan bugungi orderlar ko'rinadi. Tayyor bo'lgan itemlarni mijozga topshirishingiz va kunlik tushum hamda {{ number_format($commissionRate * 100, 0) }}% komissiyangizni kuzatishingiz mumkin.
                </p>
            </div>

            <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-5 py-4">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Branch</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ $branch?->name ?? 'Branch tanlanmagan' }}</p>

            </div>
        </div>
    </section>

    <!-- FIX: gap + alignment -->
    <div class="grid gap-12 xl:grid-cols-[300px_minmax(0,1fr)_380px] items-start">

        <aside class="soft-panel rounded-[2rem] border border-white/10 p-5 h-fit">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Tables</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Stol holati</h3>
                </div>
                <span class="badge badge-outline">{{ $tables->count() }}</span>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Today orders</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ $dailyOrdersCount }}</p>
            <p class="mt-2 text-sm text-slate-400">Faqat sizga biriktirilgan orderlar</p>
        </article>

        <article class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Ready orders</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-300">{{ $readyOrdersCount }}</p>
            <p class="mt-2 text-sm text-slate-400">Hozir topshirishga tayyor orderlar</p>
        </article>

        <article class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Today order total</p>
            <p class="mt-3 text-3xl font-semibold text-amber-200">{{ number_format($dailyOrderAmount) }}</p>
            <p class="mt-2 text-sm text-slate-400">Bugungi orderlar summasi</p>
        </article>

        <article class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Today paid revenue</p>
            <p class="mt-3 text-3xl font-semibold text-sky-300">{{ number_format($dailyRevenue) }}</p>
            <p class="mt-2 text-sm text-slate-400">To'langan orderlar bo'yicha</p>
        </article>

        <article class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">My commission</p>
            <p class="mt-3 text-3xl font-semibold text-violet-300">{{ number_format($dailyCommission) }}</p>
            <p class="mt-2 text-sm text-slate-400">Bugungi tushumdan {{ number_format($commissionRate * 100, 0) }}%</p>
        </article>
    </section>

    <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Assigned orders</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Mening bugungi orderlarim</h3>

            </div>
            <span class="badge badge-outline">{{ $orders->count() }} ta</span>
        </div>


            <div class="mt-6 space-y-5">
                @forelse ($tables as $table)

                    @php
                        $isSelected = $selectedTable?->id === $table['id'];

                        $statusClasses = match ($table['status']) {
                            'paid' => 'border-violet-300/30 bg-violet-400/10',
                            'ready' => 'border-emerald-400/40 bg-emerald-400/10',
                            'preparing' => 'border-amber-300/30 bg-amber-400/10',
                            'served' => 'border-sky-400/30 bg-sky-400/10',
                            default => 'border-white/10 bg-slate-950/50',
                        };
                    @endphp

                    <button
                        type="button"
                        wire:click="selectTable({{ $table['id'] }})"
                        class="w-full rounded-[1.5rem] border p-4 text-left transition {{ $statusClasses }} {{ $isSelected ? 'ring-2 ring-amber-300/50' : '' }}"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm uppercase tracking-[0.25em] text-slate-400">{{ $table['name'] }}</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ $table['seats'] }} seats</p>
                            </div>

                            <span class="badge">
                                {{ ucfirst($table['status']) }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="rounded-2xl bg-slate-950/60 px-2 py-2">
                                <p class="text-slate-500">Queue</p>
                                <p class="mt-1 font-semibold text-white">{{ $table['queued_qty'] + $table['preparing_qty'] }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-950/60 px-2 py-2">
                                <p class="text-slate-500">Ready</p>
                                <p class="mt-1 font-semibold text-emerald-300">{{ $table['ready_qty'] }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-950/60 px-2 py-2">
                                <p class="text-slate-500">Total</p>
                                <p class="mt-1 font-semibold text-amber-200">{{ number_format($table['total']) }}</p>
                            </div>
                        </div>
                    </button>

                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                        Bu filialda aktiv stol topilmadi.

        <div class="mt-5 grid gap-4 xl:grid-cols-2">
            @forelse ($orders as $order)
                @php
                    $queuedCount = $order->items->where('preparation_status', 'queued')->sum('quantity');
                    $preparingCount = $order->items->where('preparation_status', 'preparing')->sum('quantity');
                    $readyCount = $order->items->where('preparation_status', 'ready')->sum('quantity');
                    $servedCount = $order->items->where('preparation_status', 'served')->sum('quantity');
                    $statusClasses = match ($order->status) {
                        'ready' => 'badge-success',
                        'in_service' => 'badge-warning',
                        'served' => 'badge-info',
                        'paid' => 'badge-primary',
                        'closed' => 'badge-outline',
                        default => 'badge-outline',
                    };
                @endphp

                <article class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $order->diningTable?->name ?? 'No table' }}</p>
                            <h4 class="mt-2 text-xl font-semibold text-white">{{ $order->order_number }}</h4>
                            <p class="mt-2 text-sm text-slate-400">
                                {{ optional($order->placed_at)->format('d.m.Y H:i') }}
                                @if ($order->cashier)
                                    | Cashier: {{ $order->cashier->name }}
                                @endif
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="badge {{ $statusClasses }}">{{ $order->serviceStatusLabel() }}</span>
                            <p class="mt-3 text-lg font-semibold text-amber-200">{{ number_format((float) $order->total) }} so'm</p>
                        </div>

                    </div>


        <section class="space-y-8">

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="grid gap-4 lg:grid-cols-[1fr_auto]">
                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Menu ichidan qidirish</span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            class="input input-bordered bg-slate-950/70 text-white"
                            placeholder="Burger, coffee, sku..."
                        >
                    </label>

                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Selected table</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $selectedTable?->name ?? 'Tanlanmagan' }}</p>
                    </div>
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
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($products as $product)
                    <article class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $product->category?->name }}</p>
                                <h3 class="mt-2 text-lg font-semibold text-white">{{ $product->name }}</h3>
                                <p class="mt-2 text-sm text-slate-400">{{ $product->description ?: 'Short service note yoq' }}</p>
                            </div>

                            <span class="badge {{ $product->station === 'bar' ? 'badge-info' : 'badge-warning' }}">
                                {{ $product->stationLabel() }}
                            </span>
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xl font-semibold text-amber-200">{{ number_format((float) $product->price) }} so'm</p>
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $product->sku ?: 'No SKU' }}</p>
                            </div>

                            <button type="button" wire:click="addProduct({{ $product->id }})" class="btn btn-warning rounded-2xl">
                                Add
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-[1.75rem] border border-dashed border-white/10 bg-slate-950/40 p-10 text-center text-slate-400">
                        Filtr bo'yicha mahsulot topilmadi.
                    </div>
                @endforelse
            </div>

        </section>

        <aside class="space-y-8 h-fit">

            <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Current ticket</p>
                        <h3 class="mt-2 text-2xl font-semibold text-white">{{ $selectedTable?->name ?? 'Table tanlang' }}</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            @if ($selectedOrder)
                                {{ $selectedOrder->order_number }} | {{ $selectedOrder->serviceStatusLabel() }}
                            @else
                                Hozircha aktiv zakaz yo'q.
                            @endif
                        </p>
                    </div>

                    @if ($selectedOrder && $selectedOrder->items->where('preparation_status', 'ready')->isNotEmpty() && $selectedOrder->status !== 'paid')
                        <button type="button" wire:click="serveReadyItems({{ $selectedOrder->id }})" class="btn btn-success btn-sm rounded-2xl">
                            Serve ready
                        </button>

                    @if ($order->notes)
                        <div class="mt-4 rounded-[1.4rem] border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-slate-300">
                            <span class="text-slate-500">Note:</span> {{ $order->notes }}
                        </div>

                    @endif


                <div class="mt-5 space-y-3">
                    @forelse ($selectedOrder?->items->sortByDesc('id') ?? collect() as $item)
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-white">{{ $item->product_name }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $item->quantity }} x {{ number_format((float) $item->unit_price) }} so'm</p>
                                </div>
                                <div class="text-right">
                                    <span class="badge">
                                        {{ $item->preparationStatusLabel() }}
                                    </span>
                                    <p class="mt-2 text-xs uppercase tracking-[0.25em] text-slate-500">
                                        {{ config("pos.product_stations.{$item->station}") }}
                                    </p>
                                </div>
                            </div>

                    <div class="mt-4 grid grid-cols-4 gap-3 text-center text-sm">
                        <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                            <p class="text-slate-500">Queued</p>
                            <p class="mt-1 font-semibold text-white">{{ $queuedCount }}</p>

                        </div>
                        <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                            <p class="text-slate-500">Prep</p>
                            <p class="mt-1 font-semibold text-amber-200">{{ $preparingCount }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                            <p class="text-slate-500">Ready</p>
                            <p class="mt-1 font-semibold text-emerald-300">{{ $readyCount }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                            <p class="text-slate-500">Served</p>
                            <p class="mt-1 font-semibold text-sky-300">{{ $servedCount }}</p>
                        </div>
                    </div>


                <div class="mt-5 space-y-3">
                    @forelse ($cartItems as $item)
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-white">{{ $item['name'] }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $item['station_label'] }} | {{ number_format((float) $item['price']) }} so'm</p>
                                </div>

                                <button type="button" wire:click="removeProduct({{ $item['id'] }})" class="btn btn-xs btn-ghost text-rose-200">
                                    Remove
                                </button>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <div class="join">
                                    <button type="button" wire:click="decrementQuantity({{ $item['id'] }})" class="btn btn-sm join-item">-</button>
                                    <button type="button" class="btn btn-sm join-item pointer-events-none">{{ $item['quantity'] }}</button>
                                    <button type="button" wire:click="incrementQuantity({{ $item['id'] }})" class="btn btn-sm join-item">+</button>
                                </div>

                                <p class="font-semibold text-amber-200">{{ number_format((float) $item['line_total']) }} so'm</p>
                            </div>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-slate-400">
                            <p>Items: {{ $order->items->sum('quantity') }} ta</p>
                            <p>To'langan: {{ number_format((float) $order->payments->sum('amount')) }} so'm</p>
                            <p>Komissiya: {{ number_format($order->waiterCommissionAmount()) }} so'm</p>

                        </div>


                <label class="form-control mt-5">
                    <span class="label-text mb-2 text-slate-300">Service note</span>
                    <textarea wire:model.live="notes" rows="3" class="textarea textarea-bordered bg-slate-950/70 text-white"></textarea>
                </label>

                <div class="mt-5 rounded-[1.75rem] border border-amber-300/20 bg-amber-400/10 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300">Send subtotal</span>
                        <span class="text-2xl font-semibold text-white">{{ number_format((float) $subtotal) }} so'm</span>
                    </div>

                    <button
                        type="button"
                        wire:click="sendToPreparation"
                        class="btn btn-warning mt-5 w-full rounded-2xl"
                    >
                        Yuborish: kitchen / bar
                    </button>
                </div>
            </section>

        </aside>

    </div>

</div>

                        @if ($readyCount > 0 && ! in_array($order->status, ['paid', 'closed'], true))
                            <button type="button" wire:click="serveReadyItems({{ $order->id }})" class="btn btn-success rounded-2xl">
                                Ready itemlarni topshirish
                            </button>
                        @else
                            <button type="button" class="btn btn-ghost rounded-2xl text-slate-400" disabled>
                                {{ in_array($order->status, ['paid', 'closed'], true) ? 'Order yakunlangan' : 'Ready item yo\'q' }}
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="xl:col-span-2 rounded-[1.75rem] border border-dashed border-white/10 bg-slate-950/40 p-10 text-center text-slate-400">
                    Sizga bugun hali order biriktirilmagan.
                </div>
            @endforelse
        </div>
    </section>
</div>

