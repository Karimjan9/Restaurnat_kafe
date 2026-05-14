@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Operations</p>
                    <h2 class="mt-2 text-3xl font-semibold text-white">Table map, exceptions and shift control</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-300">
                        Stol statusi, move/merge, discount, void/refund va kassir shift reconciliation jarayonlari shu yerdan boshqariladi.
                    </p>
                </div>

                <form method="GET" action="{{ route('operations.index') }}" class="grid gap-3 sm:grid-cols-[minmax(12rem,1fr)_auto]">
                    <select name="branch_id" class="select select-bordered bg-slate-950/70 text-white">
                        <option value="">All branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($selectedBranchId === $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-warning">Filter</button>
                </form>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Table status map</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Hall overview</h3>
                    </div>
                    <span class="badge badge-outline">{{ $tables->count() }} tables</span>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach ($tables as $table)
                        @php
                            $order = $table->currentOrder ?: $activeOrders->firstWhere('dining_table_id', $table->id);
                            $computedStatus = $order
                                ? ($order->status === 'paid' ? 'payment_due' : 'occupied')
                                : $table->status;
                            $badge = match ($computedStatus) {
                                'available' => 'badge-success',
                                'occupied' => 'badge-warning',
                                'payment_due' => 'badge-info',
                                'cleaning' => 'badge-accent',
                                'reserved' => 'badge-primary',
                                default => 'badge-ghost',
                            };
                        @endphp
                        <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $table->branch?->name }}</p>
                                    <h4 class="mt-2 text-lg font-semibold text-white">{{ $table->name }}</h4>
                                    <p class="mt-1 text-sm text-slate-400">{{ $table->seats }} seats | {{ $table->orders_count }} orders</p>
                                </div>
                                <span class="badge {{ $badge }}">{{ config("pos.table_statuses.{$computedStatus}", $computedStatus) }}</span>
                            </div>

                            @if ($order)
                                <div class="mt-4 rounded-2xl border border-white/10 bg-slate-900/70 p-3 text-sm text-slate-300">
                                    <p class="font-medium text-white">{{ $order->order_number }}</p>
                                    <p class="mt-1">Waiter: {{ $order->waiter?->name ?? 'N/A' }}</p>
                                    <p class="mt-1">{{ number_format((float) $order->total) }} so'm</p>
                                </div>
                            @else
                                <form action="{{ route('operations.tables.status', $table) }}" method="POST" class="mt-4 flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="select select-sm select-bordered flex-1 bg-slate-950/70 text-white">
                                        @foreach (['available', 'reserved', 'cleaning', 'blocked'] as $status)
                                            <option value="{{ $status }}" @selected($table->status === $status)>{{ config("pos.table_statuses.{$status}") }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-warning">Set</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Move and merge</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Active dine-in orders</h3>

                    <div class="mt-5 space-y-4">
                        @forelse ($activeOrders as $order)
                            <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <p class="font-semibold text-white">{{ $order->order_number }}</p>
                                        <p class="mt-1 text-sm text-slate-400">
                                            {{ $order->branch?->name }} | {{ $order->diningTable?->name ?? 'No table' }} | {{ $order->serviceStatusLabel() }}
                                        </p>
                                        <p class="mt-1 text-sm text-amber-200">
                                            {{ number_format((float) $order->subtotal) }} - {{ number_format((float) $order->discount_total) }} = {{ number_format((float) $order->total) }} so'm
                                        </p>
                                    </div>
                                    @if ($order->paid_at)
                                        <a href="{{ route('orders.check', $order) }}" target="_blank" rel="noopener" class="btn btn-sm btn-warning">Print check</a>
                                    @endif
                                </div>

                                <div class="mt-4 grid gap-3 xl:grid-cols-3">
                                    <form action="{{ route('operations.orders.move-table', $order) }}" method="POST" class="grid gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="dining_table_id" class="select select-sm select-bordered bg-slate-950/70 text-white">
                                            @foreach ($tables->where('branch_id', $order->branch_id) as $table)
                                                <option value="{{ $table->id }}" @selected($order->dining_table_id === $table->id)>{{ $table->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline btn-warning">Move table</button>
                                    </form>

                                    <form action="{{ route('operations.orders.discount', $order) }}" method="POST" class="grid gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input name="discount_total" type="number" min="0" step="0.01" value="{{ $order->discount_total }}" class="input input-sm input-bordered bg-slate-950/70 text-white" placeholder="Discount">
                                        <input name="reason" type="text" class="input input-sm input-bordered bg-slate-950/70 text-white" placeholder="Reason">
                                        <button class="btn btn-sm btn-outline btn-warning">Apply discount</button>
                                    </form>

                                    <form action="{{ route('operations.orders.void', $order) }}" method="POST" class="grid gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input name="void_reason" type="text" class="input input-sm input-bordered bg-slate-950/70 text-white" placeholder="Void reason" required>
                                        <button class="btn btn-sm btn-outline btn-error">Void unpaid</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                                Aktiv dine-in order topilmadi.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Merge orders</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Two active tables into one check</h3>

                    <form action="{{ route('operations.orders.merge') }}" method="POST" class="mt-5 grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                        @csrf
                        <select name="target_order_id" class="select select-bordered bg-slate-950/70 text-white" required>
                            <option value="">Target order</option>
                            @foreach ($activeOrders as $order)
                                <option value="{{ $order->id }}">{{ $order->order_number }} | {{ $order->diningTable?->name }}</option>
                            @endforeach
                        </select>
                        <select name="source_order_id" class="select select-bordered bg-slate-950/70 text-white" required>
                            <option value="">Source order</option>
                            @foreach ($activeOrders as $order)
                                <option value="{{ $order->id }}">{{ $order->order_number }} | {{ $order->diningTable?->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-warning">Merge</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Shift reconciliation</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Open or close cashier shift</h3>

                @if ($openShift)
                    <div class="mt-5 rounded-[1.5rem] border border-emerald-400/20 bg-emerald-400/10 p-4">
                        <p class="text-sm text-emerald-100">Open shift: {{ $openShift->branch?->name }} | {{ optional($openShift->opened_at)->format('d.m.Y H:i') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($openShift->expectedCashAmount()) }} so'm expected cash</p>
                    </div>
                    <form action="{{ route('operations.shifts.close', $openShift) }}" method="POST" class="mt-5 grid gap-3">
                        @csrf
                        @method('PATCH')
                        <input name="counted_cash" type="number" min="0" step="0.01" class="input input-bordered bg-slate-950/70 text-white" placeholder="Counted cash" required>
                        <textarea name="closing_notes" rows="2" class="textarea textarea-bordered bg-slate-950/70 text-white" placeholder="Closing notes"></textarea>
                        <button class="btn btn-warning">Close shift</button>
                    </form>
                @else
                    <form action="{{ route('operations.shifts.open') }}" method="POST" class="mt-5 grid gap-3">
                        @csrf
                        <select name="branch_id" class="select select-bordered bg-slate-950/70 text-white" required>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(auth()->user()->branch_id === $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <input name="opening_cash" type="number" min="0" step="0.01" class="input input-bordered bg-slate-950/70 text-white" placeholder="Opening cash" required>
                        <textarea name="opening_notes" rows="2" class="textarea textarea-bordered bg-slate-950/70 text-white" placeholder="Opening notes"></textarea>
                        <button class="btn btn-warning">Open shift</button>
                    </form>
                @endif

                <div class="mt-6 space-y-3">
                    @foreach ($shifts as $shift)
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-white">{{ $shift->cashier?->name }} | {{ $shift->branch?->name }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ optional($shift->opened_at)->format('d.m.Y H:i') }} - {{ optional($shift->closed_at)->format('d.m.Y H:i') ?? 'open' }}</p>
                                </div>
                                <span class="badge {{ $shift->status === 'open' ? 'badge-success' : 'badge-ghost' }}">{{ $shift->status }}</span>
                            </div>
                            <div class="mt-3 grid grid-cols-3 gap-2 text-sm">
                                <span class="rounded-2xl bg-slate-900/70 p-3 text-slate-300">Expected: {{ number_format((float) $shift->expected_cash) }}</span>
                                <span class="rounded-2xl bg-slate-900/70 p-3 text-slate-300">Counted: {{ number_format((float) $shift->counted_cash) }}</span>
                                <span class="rounded-2xl bg-slate-900/70 p-3 text-slate-300">Diff: {{ number_format((float) $shift->cash_difference) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Refunds</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Paid order exceptions</h3>

                <div class="mt-5 space-y-4">
                    @forelse ($recentFinancialOrders as $order)
                        <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-white">{{ $order->order_number }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $order->branch?->name }} | {{ $order->diningTable?->name ?? 'Direct' }}</p>
                                    <p class="mt-1 text-sm text-amber-200">
                                        Paid {{ number_format((float) $order->payments->sum('amount')) }} | Refunded {{ number_format((float) $order->refunds->sum('amount')) }}
                                    </p>
                                </div>
                                <a href="{{ route('orders.check', $order) }}" target="_blank" rel="noopener" class="btn btn-xs btn-warning">Print</a>
                            </div>

                            <form action="{{ route('operations.orders.refund', $order) }}" method="POST" class="mt-4 grid gap-2 md:grid-cols-[0.7fr_0.7fr_1fr_auto]">
                                @csrf
                                <input name="amount" type="number" min="1" step="0.01" class="input input-sm input-bordered bg-slate-950/70 text-white" placeholder="Amount" required>
                                <select name="method" class="select select-sm select-bordered bg-slate-950/70 text-white">
                                    @foreach (config('pos.payment_methods') as $method => $label)
                                        <option value="{{ $method }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input name="reason" type="text" class="input input-sm input-bordered bg-slate-950/70 text-white" placeholder="Reason" required>
                                <button class="btn btn-sm btn-outline btn-error">Refund</button>
                            </form>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                            Paid order topilmadi.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
