<section id="pos-settlements" class="pos-screen-card rounded-[2rem] border border-white/70 p-4 lg:p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-[11px] uppercase tracking-[0.34em] text-slate-500">Waiter settlement</p>
            <h4 class="mt-2 text-xl font-semibold text-slate-900">Open tables and active service orders</h4>
        </div>

        <label class="block w-full lg:max-w-sm">
            <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Search table or order</span>
            <input type="text" wire:model.live.debounce.300ms="serviceOrderSearch" class="w-full rounded-[1.25rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="SRV-..., Table 2">
        </label>
    </div>

    <div class="mt-5 grid gap-3 md:grid-cols-2">
        @forelse ($serviceOrders as $serviceOrder)
            @php
                $orderTone = match ($serviceOrder->status) {
                    'paid' => 'border-sky-200 bg-sky-50',
                    'served' => 'border-emerald-200 bg-emerald-50',
                    'ready' => 'border-cyan-200 bg-cyan-50',
                    'in_service' => 'border-amber-200 bg-amber-50',
                    default => 'border-slate-200 bg-white',
                };
                $orderBadge = match ($serviceOrder->status) {
                    'paid' => 'border-sky-200 bg-sky-100 text-sky-700',
                    'served' => 'border-emerald-200 bg-emerald-100 text-emerald-700',
                    'ready' => 'border-cyan-200 bg-cyan-100 text-cyan-700',
                    'in_service' => 'border-amber-200 bg-amber-100 text-amber-700',
                    default => 'border-slate-200 bg-slate-100 text-slate-600',
                };
            @endphp
            <button
                type="button"
                wire:click="selectServiceOrder({{ $serviceOrder->id }})"
                wire:key="service-order-{{ $serviceOrder->id }}"
                class="rounded-[1.6rem] border p-4 text-left shadow-sm transition hover:-translate-y-0.5 {{ $selectedServiceOrder?->id === $serviceOrder->id ? 'border-violet-300 bg-violet-50 shadow-[0_24px_44px_-28px_rgba(109,40,217,0.45)]' : $orderTone }}"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.28em] text-slate-500">{{ $serviceOrder->diningTable?->name ?? 'No table' }}</p>
                        <h5 class="mt-2 text-lg font-semibold text-slate-900">{{ $serviceOrder->order_number }}</h5>
                        <p class="mt-2 text-sm text-slate-500">Waiter: {{ $serviceOrder->waiter?->name ?? 'N/A' }}</p>
                    </div>
                    <span class="rounded-full border px-3 py-1 text-[11px] font-medium uppercase tracking-[0.22em] {{ $orderBadge }}">
                        {{ $serviceOrder->serviceStatusLabel() }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-4 gap-2 text-center">
                    <div class="rounded-[1.1rem] bg-white/80 px-2 py-2">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">Items</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $serviceOrder->items->sum('quantity') }}</p>
                    </div>
                    <div class="rounded-[1.1rem] bg-white/80 px-2 py-2">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">Splits</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $serviceOrder->splits->count() }}</p>
                    </div>
                    <div class="rounded-[1.1rem] bg-white/80 px-2 py-2">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">Left</p>
                        <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $serviceOrder->splits->where('status', 'draft')->count() }}</p>
                    </div>
                    <div class="rounded-[1.1rem] bg-white/80 px-2 py-2">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">Total</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ number_format((float) $serviceOrder->total) }}</p>
                    </div>
                </div>
            </button>
        @empty
            <div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50/80 p-8 text-center text-sm leading-7 text-slate-500">
                Bu filialda settlement kutayotgan stol order yo'q.
            </div>
        @endforelse
    </div>
</section>

<section class="pos-screen-card rounded-[2rem] border border-white/70 p-4 lg:p-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-[11px] uppercase tracking-[0.34em] text-slate-500">Settlement detail</p>
            <h4 class="mt-2 text-2xl font-semibold text-slate-900">{{ $selectedServiceOrder?->order_number ?? 'Order tanlang' }}</h4>
            <p class="mt-2 text-sm text-slate-500">
                @if ($selectedServiceOrder)
                    {{ $selectedServiceOrder->diningTable?->name ?? 'No table' }} · Waiter: {{ $selectedServiceOrder->waiter?->name ?? 'N/A' }}
                @else
                    Chapdagi kartalardan order tanlang.
                @endif
            </p>
        </div>

        @if ($selectedServiceOrder)
            <span class="rounded-full border px-3 py-1 text-[11px] font-medium uppercase tracking-[0.22em] {{ $selectedStatusClasses }}">
                {{ $selectedServiceOrder->serviceStatusLabel() }}
            </span>
        @endif
    </div>

    <div class="pos-scroll mt-5 max-h-[16rem] space-y-3 pr-1">
        @forelse ($selectedServiceOrder?->items ?? collect() as $item)
            @php
                $prepBadge = match ($item->preparation_status) {
                    'served' => 'border-emerald-200 bg-emerald-100 text-emerald-700',
                    'ready' => 'border-cyan-200 bg-cyan-100 text-cyan-700',
                    'preparing' => 'border-amber-200 bg-amber-100 text-amber-700',
                    default => 'border-slate-200 bg-slate-100 text-slate-600',
                };
            @endphp
            <div class="rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $item->product_name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $item->quantity }} x {{ number_format((float) $item->unit_price) }} so'm</p>
                    </div>
                    <div class="text-right">
                        <span class="rounded-full border px-3 py-1 text-[11px] font-medium uppercase tracking-[0.22em] {{ $prepBadge }}">
                            {{ $item->preparationStatusLabel() }}
                        </span>
                        <p class="mt-2 text-[11px] uppercase tracking-[0.24em] text-slate-400">{{ config("pos.product_stations.{$item->station}", $item->station) }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50/80 p-8 text-center text-sm leading-7 text-slate-500">
                Settlement uchun tanlangan stol order yo'q.
            </div>
        @endforelse
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-[11px] uppercase tracking-[0.24em] text-slate-500">Total</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ number_format((float) ($selectedServiceOrder?->total ?? 0)) }} so'm</p>
        </div>
        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-[11px] uppercase tracking-[0.24em] text-slate-500">Paid</p>
            <p class="mt-2 text-lg font-semibold text-emerald-700">{{ number_format((float) ($selectedServiceOrder?->splitBillPaidAmount() ?? 0)) }} so'm</p>
        </div>
        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-[11px] uppercase tracking-[0.24em] text-slate-500">Remaining</p>
            <p class="mt-2 text-lg font-semibold text-amber-700">{{ number_format((float) ($selectedServiceOrder?->splitBillRemainingAmount() ?? 0)) }} so'm</p>
        </div>
    </div>

    <div class="mt-5 space-y-4">
        <label class="block">
            <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Settlement payment method</span>
            <select wire:model.live="servicePaymentMethod" class="w-full rounded-[1.25rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100">
                @foreach (config('pos.payment_methods') as $method => $label)
                    <option value="{{ $method }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>

        @error('selectedServiceOrderId') <p class="text-sm text-rose-500">{{ $message }}</p> @enderror
        @error('selectedSplitId') <p class="text-sm text-rose-500">{{ $message }}</p> @enderror

        <div class="rounded-[1.6rem] border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.24em] text-slate-500">Split bill</p>
                    <h5 class="mt-2 text-lg font-semibold text-slate-900">Equal split</h5>
                </div>
                <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600">
                    {{ $selectedServiceOrder ? $selectedServiceOrder->splits->count() : 0 }} splits
                </span>
            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                <label class="block flex-1">
                    <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Guests</span>
                    <input type="number" wire:model.live="splitCount" min="2" max="12" class="w-full rounded-[1.2rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                </label>
                <div class="flex items-end">
                    <button
                        type="button"
                        wire:click="createEqualSplits"
                        wire:loading.attr="disabled"
                        @disabled(! $selectedServiceOrder || $selectedServiceOrder->status !== 'served')
                        class="rounded-[1.2rem] border border-violet-200 bg-violet-50 px-4 py-3 text-sm font-medium text-violet-700 transition hover:bg-violet-100"
                    >
                        {{ $selectedServiceOrder && $selectedServiceOrder->splits->isNotEmpty() ? 'Reset equal split' : 'Create equal split' }}
                    </button>
                </div>
            </div>

            <p class="mt-3 text-sm leading-6 text-slate-500">
                Split bill faqat `served` orderda ochiladi. Full payment qilingan orderni keyin split qilib bo'lmaydi.
            </p>

            @if ($selectedServiceOrder && $selectedServiceOrder->splits->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($selectedServiceOrder->splits as $split)
                        <button
                            type="button"
                            wire:click="selectSplit({{ $split->id }})"
                            class="w-full rounded-[1.3rem] border px-4 py-3 text-left transition {{ $selectedSplit?->id === $split->id ? 'border-violet-300 bg-violet-50' : 'border-slate-200 bg-white hover:border-slate-300' }}"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $split->label }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        @if ($split->paid_at)
                                            {{ optional($split->paid_at)->format('d.m.Y H:i') }}
                                        @else
                                            Payment kutilmoqda
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="rounded-full border px-3 py-1 text-[11px] font-medium uppercase tracking-[0.22em] {{ $split->status === 'paid' ? 'border-emerald-200 bg-emerald-100 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                        {{ $split->statusLabel() }}
                                    </span>
                                    <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $split->amount) }} so'm</p>
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
                    class="mt-4 w-full rounded-[1.2rem] bg-emerald-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-400"
                >
                    Pay selected split
                </button>
            @endif
        </div>

        <div class="rounded-[1.7rem] border border-emerald-200 bg-[linear-gradient(135deg,rgba(16,185,129,0.12),rgba(14,165,233,0.14))] p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.24em] text-slate-500">Settlement actions</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ number_format((float) ($selectedServiceOrder?->total ?? 0)) }} so'm</p>
                </div>
                <div class="rounded-full border border-white/70 bg-white/70 px-3 py-1 text-xs font-medium text-slate-600">
                    {{ $selectedServiceOrder?->serviceStatusLabel() ?? 'Waiting' }}
                </div>
            </div>

            <p class="mt-3 text-sm leading-6 text-slate-600">
                @if ($selectedServiceOrder && $selectedServiceOrder->status === 'served' && $selectedServiceOrder->splits->isEmpty())
                    Order served. Full payment yoki split billni tanlashingiz mumkin.
                @elseif ($selectedServiceOrder && $selectedServiceOrder->status === 'served')
                    Split bill ochilgan. Endi splitlarni bittalab to'lang.
                @elseif ($selectedServiceOrder && $selectedServiceOrder->status === 'paid')
                    Payment yopilgan. Endi stolni close qilsangiz order arxivga o'tadi.
                @elseif ($selectedServiceOrder)
                    To'lov actionlari served yoki paid bosqichida ochiladi.
                @else
                    Chap tomondan order tanlang.
                @endif
            </p>

            <div class="mt-4 grid gap-3">
                <button
                    type="button"
                    wire:click="completeServiceOrderPayment"
                    wire:loading.attr="disabled"
                    @disabled(! $selectedServiceOrder || $selectedServiceOrder->status !== 'served' || $selectedServiceOrder->splits->isNotEmpty())
                    class="rounded-[1.2rem] bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    Finalize full payment
                </button>

                <button
                    type="button"
                    wire:click="closeSelectedServiceOrder"
                    wire:loading.attr="disabled"
                    @disabled(! $selectedServiceOrder || $selectedServiceOrder->status !== 'paid')
                    class="rounded-[1.2rem] bg-amber-400 px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-amber-300"
                >
                    Close table
                </button>

                @if ($selectedServiceOrder?->paid_at)
                    <div class="grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('orders.receipt', $selectedServiceOrder) }}" class="rounded-[1.2rem] border border-slate-200 bg-white px-4 py-3 text-center text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                            Open receipt
                        </a>
                        <a href="{{ route('orders.check', $selectedServiceOrder) }}" target="_blank" rel="noopener" class="rounded-[1.2rem] border border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                            Print check
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
