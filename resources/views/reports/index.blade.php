@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Basic report</p>
                    <h2 class="mt-2 text-3xl font-semibold text-white">Sales snapshot</h2>
                    <p class="mt-2 text-sm text-slate-300">Branch va sana oralig'i bo'yicha savdo, payment va top mahsulotlarni ko'rsatadi.</p>
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
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Gross sales</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((float) $grossSales) }} so'm</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Paid orders</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ $orderCount }}</p>
            </div>
            <div class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Average order</p>
                <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((float) $averageOrderValue) }} so'm</p>
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
                            <span class="font-semibold text-white">{{ number_format((float) ($paymentBreakdown[$method] ?? 0)) }} so'm</span>
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
                        </div>
                    @empty
                        <div class="text-slate-400">No product sales in this range.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Receipts</p>
            <h3 class="mt-2 text-xl font-semibold text-white">Recent paid orders</h3>

            <div class="mt-5 overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr class="text-slate-400">
                            <th>Order</th>
                            <th>Branch</th>
                            <th>Type</th>
                            <th>Cashier</th>
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
                                <td>{{ $order->cashier?->name }}</td>
                                <td>{{ number_format((float) $order->total) }} so'm</td>
                                <td>
                                    <a href="{{ route('orders.receipt', $order) }}" class="btn btn-xs btn-outline btn-warning">Receipt</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-slate-400">Bu oraliqda order yo'q.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
