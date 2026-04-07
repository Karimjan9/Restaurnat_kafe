<div class="space-y-6">
    <section class="soft-panel rounded-[2rem] border border-white/10 p-6 lg:p-8">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">{{ $stationLabel }} queue</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ $stationLabel }} buyurtmalari</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                    Bu ekranda faqat sizning stansiyangizga yuborilgan itemlar ko'rinadi. Har bir item navbatdan tayyor holatiga o'tkaziladi.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Branch</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $branch?->name ?? 'Branch tanlanmagan' }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Queued</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $queuedCount }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Preparing</p>
                    <p class="mt-2 text-lg font-semibold text-amber-200">{{ $preparingCount }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Ready</p>
                    <p class="mt-2 text-lg font-semibold text-emerald-300">{{ $readyCount }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse ($orders as $entry)
            @php($order = $entry['order'])

            <article class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">{{ $order?->diningTable?->name ?? 'Takeaway / delivery' }}</p>
                        <h3 class="mt-2 text-2xl font-semibold text-white">{{ $order?->order_number }}</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            {{ $order?->serviceStatusLabel() }} | {{ optional($order?->placed_at)->format('d.m.Y H:i') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded-2xl bg-slate-950/60 px-3 py-3">
                            <p class="text-slate-500">Queue</p>
                            <p class="mt-1 font-semibold text-white">{{ $entry['queued_qty'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-950/60 px-3 py-3">
                            <p class="text-slate-500">Prep</p>
                            <p class="mt-1 font-semibold text-amber-200">{{ $entry['preparing_qty'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-950/60 px-3 py-3">
                            <p class="text-slate-500">Ready</p>
                            <p class="mt-1 font-semibold text-emerald-300">{{ $entry['ready_qty'] }}</p>
                        </div>
                    </div>
                </div>

                @if ($order?->notes)
                    <div class="mt-4 rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <span class="text-slate-500">Service note:</span> {{ $order->notes }}
                    </div>
                @endif

                <div class="mt-5 space-y-3">
                    @foreach ($entry['items'] as $item)
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-medium text-white">{{ $item->product_name }}</p>
                                        <span class="badge badge-outline">{{ $item->quantity }} pcs</span>
                                        <span class="badge {{ $item->preparation_status === 'ready' ? 'badge-success' : ($item->preparation_status === 'preparing' ? 'badge-warning' : 'badge-outline') }}">
                                            {{ $item->preparationStatusLabel() }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm text-slate-400">
                                        Yuborildi: {{ optional($item->sent_to_station_at)->format('H:i') ?: optional($item->created_at)->format('H:i') }}
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    @if ($item->preparation_status === 'queued')
                                        <button type="button" wire:click="startItem({{ $item->id }})" class="btn btn-warning btn-sm rounded-2xl">
                                            Start
                                        </button>
                                    @endif

                                    @if (in_array($item->preparation_status, ['queued', 'preparing'], true))
                                        <button type="button" wire:click="markReady({{ $item->id }})" class="btn btn-success btn-sm rounded-2xl">
                                            Ready
                                        </button>
                                    @endif

                                    @if ($item->preparation_status === 'ready')
                                        <button type="button" class="btn btn-ghost btn-sm rounded-2xl text-emerald-300" disabled>
                                            Wait waiter
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="xl:col-span-2 rounded-[2rem] border border-dashed border-white/10 bg-slate-950/40 p-10 text-center text-slate-400">
                Hozircha {{ strtolower($stationLabel) }} uchun aktiv zakaz yo'q.
            </div>
        @endforelse
    </section>
</div>
