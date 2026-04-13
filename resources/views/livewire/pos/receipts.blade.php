<section id="pos-receipts" class="pos-screen-card mt-5 rounded-[2rem] border border-white/70 p-4 lg:p-5">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-[11px] uppercase tracking-[0.34em] text-slate-500">Recent receipts</p>
            <h4 class="mt-2 text-xl font-semibold text-slate-900">Latest completed orders</h4>
        </div>
        <p class="text-sm text-slate-500">Kassir yopgan oxirgi orderlar shu yerda ko'rinadi.</p>
    </div>

    <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($recentOrders as $order)
            <article class="rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.28em] text-slate-500">{{ config('pos.order_types')[$order->order_type] ?? $order->order_type }}</p>
                        <h5 class="mt-2 text-lg font-semibold text-slate-900">{{ $order->order_number }}</h5>
                        <p class="mt-2 text-sm text-slate-500">{{ $order->branch?->name ?? 'N/A' }}</p>
                    </div>
                    <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                        {{ number_format((float) $order->total) }}
                    </span>
                </div>

                <div class="mt-4 space-y-1 text-sm text-slate-500">
                    <p>Waiter: {{ $order->waiter?->name ?? 'N/A' }}</p>
                    <p>Cashier: {{ $order->cashier?->name ?? 'N/A' }}</p>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('orders.receipt', $order) }}" class="inline-flex rounded-full border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-medium text-violet-700 transition hover:bg-violet-100">
                        Open receipt
                    </a>
                    <a href="{{ route('orders.check', $order) }}" target="_blank" rel="noopener" class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-100">
                        Print check
                    </a>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50/80 p-8 text-center text-sm leading-7 text-slate-500">
                Hozircha receipt yo'q.
            </div>
        @endforelse
    </div>
</section>
