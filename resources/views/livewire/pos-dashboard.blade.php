@php
    $activeBranch = $branches->firstWhere('id', $branchId);
    $categoryCounts = $products->groupBy('category_id')->map->count();
    $orderTypeMeta = [
        'dine_in' => ['code' => 'DI', 'hint' => 'Table service'],
        'takeaway' => ['code' => 'TA', 'hint' => 'Fast pickup'],
        'delivery' => ['code' => 'DL', 'hint' => 'Courier drop'],
    ];
    $serviceOverview = [
        ['label' => 'Open tables', 'value' => $serviceOrders->count(), 'tone' => 'text-violet-700'],
        ['label' => 'Served', 'value' => $serviceOrders->where('status', 'served')->count(), 'tone' => 'text-emerald-700'],
        ['label' => 'Paid', 'value' => $serviceOrders->where('status', 'paid')->count(), 'tone' => 'text-sky-700'],
        ['label' => 'Cart qty', 'value' => $cartItems->sum('quantity'), 'tone' => 'text-amber-700'],
    ];
    $selectedStatusClasses = match ($selectedServiceOrder?->status) {
        'paid' => 'border-sky-200 bg-sky-100 text-sky-700',
        'served' => 'border-emerald-200 bg-emerald-100 text-emerald-700',
        'ready' => 'border-cyan-200 bg-cyan-100 text-cyan-700',
        'in_service' => 'border-amber-200 bg-amber-100 text-amber-700',
        default => 'border-slate-200 bg-slate-100 text-slate-600',
    };
    $stationThemes = [
        'kitchen' => [
            'badge' => 'border-amber-200 bg-amber-100 text-amber-700',
            'tile' => 'linear-gradient(140deg, #fb923c 0%, #f97316 26%, #ef4444 58%, #111827 100%)',
            'glow' => 'rgba(251, 146, 60, 0.45)',
        ],
        'bar' => [
            'badge' => 'border-sky-200 bg-sky-100 text-sky-700',
            'tile' => 'linear-gradient(140deg, #38bdf8 0%, #06b6d4 32%, #2563eb 60%, #0f172a 100%)',
            'glow' => 'rgba(56, 189, 248, 0.42)',
        ],
    ];
    $categoryThemes = [
        'burgers' => 'linear-gradient(140deg, #fbbf24 0%, #f97316 34%, #b91c1c 68%, #111827 100%)',
        'hot-dishes' => 'linear-gradient(140deg, #fb7185 0%, #ef4444 34%, #7c2d12 70%, #111827 100%)',
        'drinks' => 'linear-gradient(140deg, #67e8f9 0%, #22d3ee 32%, #0284c7 65%, #082f49 100%)',
        'desserts' => 'linear-gradient(140deg, #f9a8d4 0%, #ec4899 34%, #9333ea 70%, #1e1b4b 100%)',
        'default' => 'linear-gradient(140deg, #818cf8 0%, #6366f1 35%, #4338ca 68%, #111827 100%)',
    ];
@endphp

<div class="space-y-6">
    <section class="soft-panel rounded-[2.4rem] border border-white/10 p-3 sm:p-4 lg:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">POS canvas</p>
                <h2 class="mt-2 text-3xl font-semibold text-white lg:text-4xl">Order page tablet terminal strukturasi bo'yicha qayta yig'ildi</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">
                    Chapda live order composer, o'ngda visual menu grid, pastda settlement va receipt oqimi bitta ish maydonida ishlaydi.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($serviceOverview as $metric)
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/55 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-2xl font-semibold {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="pos-kiosk-shell mt-6">
            <div class="pos-kiosk-screen grid overflow-hidden xl:grid-cols-[4.75rem_minmax(19rem,23rem)_minmax(0,1fr)]">
                @include('livewire.pos.order-sheet')
                @include('livewire.pos.browser')
            </div>
        </div>
    </section>
</div>
