@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Basic report</p>
                    <h2 class="mt-2 text-3xl font-semibold text-white">Sales snapshot</h2>
                    <p class="mt-2 text-sm text-slate-300">Branch va sana oralig'i bo'yicha savdo, payment, top mahsulot va ofitsiant tushumini ko'rsatadi.</p>
                </div>

                <form method="GET" action="{{ route('reports.index') }}" class="grid gap-3 md:grid-cols-4">
                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Branch</span>
                        <select name="branch_id" class="select select-bordered bg-slate-950/70 text-white">
                            <option value="">All branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($filters['branch_id'] == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Date from</span>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="input input-bordered bg-slate-950/70 text-white">
                    </label>
                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Date to</span>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="input input-bordered bg-slate-950/70 text-white">
                    </label>
                    <div class="flex items-end">
                        <button type="submit" class="btn btn-warning w-full">Apply</button>
                    </div>
                </form>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('reports.export', [...$filters, 'format' => 'csv']) }}" class="btn btn-outline btn-warning btn-sm">Export Excel CSV</a>
                <a href="{{ route('reports.export', [...$filters, 'format' => 'pdf']) }}" target="_blank" rel="noopener" class="btn btn-outline btn-warning btn-sm">PDF print view</a>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Gross sales</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((float) $grossSales) }} so'm</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Completed orders</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ $orderCount }}</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Average order</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((float) $averageOrderValue) }} so'm</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Waiter commission</p>
                <p class="mt-3 text-3xl font-semibold text-violet-300">{{ number_format((float) $totalWaiterCommission) }} so'm</p>
                <p class="mt-2 text-sm text-slate-400">Range bo'yicha {{ number_format($commissionRate * 100, 0) }}% commission pool</p>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Discounts</p>
                <p class="mt-3 text-3xl font-semibold text-amber-200">{{ number_format((float) $discountTotal) }} so'm</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Refunds</p>
                <p class="mt-3 text-3xl font-semibold text-rose-300">{{ number_format((float) $refundTotal) }} so'm</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Net sales</p>
                <p class="mt-3 text-3xl font-semibold text-emerald-300">{{ number_format((float) $netSales) }} so'm</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Gross profit</p>
                <p class="mt-3 text-3xl font-semibold text-sky-300">{{ number_format((float) $grossProfit) }} so'm</p>
                <p class="mt-2 text-sm text-slate-400">COGS: {{ number_format((float) $cogs) }} so'm</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Payments</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Payment methods</h3>

                <div class="mt-5 space-y-3">
                    @foreach (config('pos.payment_methods') as $method => $label)
                        <div class="flex items-center justify-between rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <span class="text-slate-300">{{ $label }}</span>
                            <span class="text-right">
                                <span class="block font-semibold text-white">{{ number_format((float) ($paymentBreakdown[$method] ?? 0)) }} so'm</span>
                                <span class="block text-xs text-rose-300">Refund: {{ number_format((float) ($refundBreakdown[$method] ?? 0)) }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Order types</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Channel mix</h3>

                <div class="mt-5 space-y-3">
                    @foreach (config('pos.order_types') as $type => $label)
                        <div class="flex items-center justify-between rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <span class="text-slate-300">{{ $label }}</span>
                            <span class="font-semibold text-white">{{ $orderTypeBreakdown[$type] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Top products</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Best sellers</h3>

                <div class="mt-5 space-y-3">
                    @forelse ($topProducts as $product)
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-white">{{ $product->product_name }}</span>
                                <span class="text-slate-300">{{ $product->quantity }} pcs</span>
                            </div>
                            <p class="mt-2 text-sm text-amber-200">{{ number_format((float) $product->total) }} so'm</p>
                            <p class="mt-1 text-xs text-emerald-300">Margin: {{ number_format((float) $product->margin) }} so'm</p>
                        </div>
                    @empty
                        <div class="text-slate-400">No product sales in this range.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Product margin</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Revenue, COGS and profit</h3>

                <div class="mt-5 overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr class="text-slate-400">
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Revenue</th>
                                <th>COGS</th>
                                <th>Gross profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productMargins as $product)
                                <tr>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->quantity }}</td>
                                    <td>{{ number_format((float) $product->revenue) }} so'm</td>
                                    <td>{{ number_format((float) $product->cogs) }} so'm</td>
                                    <td>{{ number_format((float) $product->gross_profit) }} so'm</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-slate-400">Bu oraliqda margin ma'lumoti yo'q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Daily closing</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Shift reconciliation</h3>

                <div class="mt-5 space-y-3">
                    @forelse ($dailyClosings as $shift)
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-white">{{ $shift->branch?->name }} | {{ $shift->cashier?->name }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ optional($shift->opened_at)->format('d.m.Y H:i') }} - {{ optional($shift->closed_at)->format('d.m.Y H:i') ?? 'open' }}</p>
                                </div>
                                <span class="badge {{ $shift->status === 'open' ? 'badge-success' : 'badge-ghost' }}">{{ $shift->status }}</span>
                            </div>
                            <p class="mt-3 text-sm text-slate-300">
                                Expected {{ number_format((float) $shift->expected_cash) }} | Counted {{ number_format((float) $shift->counted_cash) }} | Diff {{ number_format((float) $shift->cash_difference) }}
                            </p>
                        </div>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                            Bu oraliqda shift closing yo'q.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Service team</p>
            <h3 class="mt-2 text-xl font-semibold text-white">Waiter performance</h3>

            <div class="mt-5 overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr class="text-slate-400">
                            <th>Waiter</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                            <th>Commission</th>
                            <th>Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($waiterPerformance as $waiter)
                            <tr>
                                <td>{{ $waiter->name }}</td>
                                <td>{{ $waiter->orders_count }}</td>
                                <td>{{ number_format((float) $waiter->revenue) }} so'm</td>
                                <td>{{ number_format((float) $waiter->commission) }} so'm</td>
                                <td>{{ number_format((float) $waiter->revenue / max(1, $waiter->orders_count)) }} so'm</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-slate-400">Bu oraliqda waiter orderlari hali yo'q.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Receipts</p>
            <h3 class="mt-2 text-xl font-semibold text-white">Recent completed orders</h3>

            <div class="mt-5 overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr class="text-slate-400">
                            <th>Order</th>
                            <th>Branch</th>
                            <th>Type</th>
                            <th>Waiter</th>
                            <th>Cashier</th>
                            <th>Commission</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->branch?->name }}</td>
                                <td>{{ config('pos.order_types')[$order->order_type] ?? $order->order_type }}</td>
                                <td>{{ $order->waiter?->name ?? 'N/A' }}</td>
                                <td>{{ $order->cashier?->name ?? 'N/A' }}</td>
                                <td>{{ $order->waiter ? number_format($order->waiterCommissionAmount()) : '0' }} so'm</td>
                                <td>{{ number_format((float) $order->total) }} so'm</td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('orders.receipt', $order) }}" class="btn btn-xs btn-outline btn-warning">Receipt</a>
                                        <a href="{{ route('orders.check', $order) }}" target="_blank" rel="noopener" class="btn btn-xs btn-warning">Check</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-slate-400">Bu oraliqda order yo'q.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
