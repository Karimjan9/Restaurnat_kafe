@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="soft-panel rounded-[2rem] border border-white/10 p-6 lg:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Overview</p>
                    <h2 class="mt-2 text-3xl font-semibold text-white">Restaurant POS MVP dashboard</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Kunlik savdo, buyurtma hajmi va asosiy katalog holati bitta joyda ko'rinadi.
                    </p>
                </div>

                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-5 py-4">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Today</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ number_format((float) $stats['salesToday']) }} so'm</p>
                    <p class="text-sm text-slate-400">{{ $stats['ordersToday'] }} ta order</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Branches</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ $stats['branches'] }}</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Tables</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ $stats['tables'] }}</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Products</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ $stats['products'] }}</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Staff</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ $stats['staff'] }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Order type</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Today breakdown</h3>
                    </div>
                    @can('reports.view')
                        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline btn-warning">Open report</a>
                    @endcan
                </div>

                <div class="mt-5 space-y-3">
                    @foreach (config('pos.order_types') as $type => $label)
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-300">{{ $label }}</span>
                                <span class="text-lg font-semibold text-white">{{ $orderTypeBreakdown[$type] ?? 0 }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Recent activity</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Latest completed orders</h3>
                    </div>
                    @can('orders.create')
                        <a href="{{ route('pos.index') }}" class="btn btn-sm btn-warning">New order</a>
                    @endcan
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr class="text-slate-400">
                                <th>Order</th>
                                <th>Branch</th>
                                <th>Type</th>
                                <th>Waiter</th>
                                <th>Cashier</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('orders.receipt', $order) }}" class="font-medium text-amber-200 hover:text-white">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->branch?->name }}</td>
                                    <td>{{ config('pos.order_types')[$order->order_type] ?? $order->order_type }}</td>
                                    <td>{{ $order->waiter?->name ?? 'N/A' }}</td>
                                    <td>{{ $order->cashier?->name ?? 'N/A' }}</td>
                                    <td>{{ number_format((float) $order->total) }} so'm</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-slate-400">Hali order yo'q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
