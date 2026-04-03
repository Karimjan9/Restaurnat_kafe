@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <section class="soft-panel rounded-[2rem] border border-white/10 p-6 lg:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Receipt</p>
                    <h2 class="mt-2 text-3xl font-semibold text-white">{{ $order->order_number }}</h2>
                    <p class="mt-2 text-sm text-slate-300">
                        {{ $order->branch?->name }} | {{ config('pos.order_types')[$order->order_type] ?? $order->order_type }}
                        @if ($order->diningTable)
                            | {{ $order->diningTable->name }}
                        @endif
                    </p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('pos.index') }}" class="btn btn-warning">New order</a>
                    <button type="button" onclick="window.print()" class="btn btn-outline">Print</button>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <h3 class="text-xl font-semibold text-white">Order items</h3>

                <div class="mt-5 space-y-3">
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                            <div>
                                <p class="font-medium text-white">{{ $item->product_name }}</p>
                                <p class="text-sm text-slate-400">{{ $item->quantity }} x {{ number_format((float) $item->unit_price) }} so'm</p>
                            </div>
                            <p class="font-semibold text-amber-200">{{ number_format((float) $item->line_total) }} so'm</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <h3 class="text-xl font-semibold text-white">Customer</h3>
                    <div class="mt-4 space-y-2 text-sm text-slate-300">
                        <p><span class="text-slate-400">Name:</span> {{ $order->customer_name ?: 'Walk-in customer' }}</p>
                        <p><span class="text-slate-400">Phone:</span> {{ $order->customer_phone ?: 'Not provided' }}</p>
                        <p><span class="text-slate-400">Address:</span> {{ $order->delivery_address ?: 'N/A' }}</p>
                    </div>
                </section>

                <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <h3 class="text-xl font-semibold text-white">Payment</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($order->payments as $payment)
                            <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-300">{{ config('pos.payment_methods')[$payment->method] ?? $payment->method }}</span>
                                    <span class="font-semibold text-white">{{ number_format((float) $payment->amount) }} so'm</span>
                                </div>
                                <p class="mt-2 text-xs uppercase tracking-[0.25em] text-slate-500">
                                    {{ optional($payment->paid_at)->format('d.m.Y H:i') }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 rounded-[1.5rem] border border-emerald-400/20 bg-emerald-400/10 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-300">Total</span>
                            <span class="text-2xl font-semibold text-white">{{ number_format((float) $order->total) }} so'm</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-300">
                            Cashier: {{ $order->cashier?->name }} | {{ optional($order->paid_at)->format('d.m.Y H:i') }}
                        </p>
                    </div>
                </section>
            </div>
        </section>
    </div>
@endsection
