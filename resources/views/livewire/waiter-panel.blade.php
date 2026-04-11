<div class="space-y-6">
    <section class="soft-panel rounded-[2rem] border border-white/10 p-6 lg:p-8">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Waiter terminal</p>
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

                    @if ($order->notes)
                        <div class="mt-4 rounded-[1.4rem] border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-slate-300">
                            <span class="text-slate-500">Note:</span> {{ $order->notes }}
                        </div>
                    @endif

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

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-slate-400">
                            <p>Items: {{ $order->items->sum('quantity') }} ta</p>
                            <p>To'langan: {{ number_format((float) $order->payments->sum('amount')) }} so'm</p>
                            <p>Komissiya: {{ number_format($order->waiterCommissionAmount()) }} so'm</p>
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
