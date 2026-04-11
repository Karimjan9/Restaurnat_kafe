<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check | {{ $order->order_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 18px;
            background: #f4f4f5;
            color: #111827;
            font-family: 'Courier New', Courier, monospace;
        }

        .check {
            width: 100%;
            max-width: 360px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #d4d4d8;
            border-radius: 12px;
            padding: 18px 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
        }

        .center {
            text-align: center;
        }

        h1, h2, p {
            margin: 0;
        }

        h1 {
            font-size: 20px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .muted {
            color: #52525b;
            font-size: 12px;
            line-height: 1.5;
        }

        .divider {
            border-top: 1px dashed #a1a1aa;
            margin: 12px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            font-size: 13px;
            line-height: 1.5;
        }

        .row strong {
            font-size: 14px;
        }

        .items {
            display: grid;
            gap: 10px;
        }

        .item-name {
            font-size: 13px;
            font-weight: 700;
        }

        .summary .row {
            margin-top: 6px;
        }

        .summary .total {
            font-size: 16px;
            font-weight: 700;
        }

        .payments {
            display: grid;
            gap: 8px;
        }

        .footer {
            margin-top: 14px;
            font-size: 11px;
            line-height: 1.6;
            color: #52525b;
            text-align: center;
        }

        .actions {
            max-width: 360px;
            margin: 14px auto 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .button {
            border: 1px solid #111827;
            background: #111827;
            color: #ffffff;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 14px;
            cursor: pointer;
        }

        .button.secondary {
            background: #ffffff;
            color: #111827;
        }

        @page {
            size: 80mm auto;
            margin: 8mm;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .check {
                border: 0;
                border-radius: 0;
                box-shadow: none;
                max-width: none;
            }

            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <section class="check">
        <div class="center">
            <h1>{{ $order->branch?->name ?? 'Restaurant POS' }}</h1>
            <p class="muted">{{ $order->branch?->address ?? 'Address not set' }}</p>
            <p class="muted">{{ $order->branch?->phone ?? 'Phone not set' }}</p>
        </div>

        <div class="divider"></div>

        <div class="row">
            <span>Check No:</span>
            <strong>{{ $order->order_number }}</strong>
        </div>
        <div class="row">
            <span>Date:</span>
            <span>{{ optional($order->paid_at ?? $order->placed_at)->format('d.m.Y H:i') }}</span>
        </div>
        <div class="row">
            <span>Type:</span>
            <span>{{ config('pos.order_types')[$order->order_type] ?? $order->order_type }}</span>
        </div>
        <div class="row">
            <span>Table:</span>
            <span>{{ $order->diningTable?->name ?? 'N/A' }}</span>
        </div>
        <div class="row">
            <span>Waiter:</span>
            <span>{{ $order->waiter?->name ?? 'N/A' }}</span>
        </div>
        <div class="row">
            <span>Cashier:</span>
            <span>{{ $order->cashier?->name ?? 'N/A' }}</span>
        </div>

        @if ($order->customer_name || $order->customer_phone)
            <div class="divider"></div>
            <div class="row">
                <span>Customer:</span>
                <span>{{ $order->customer_name ?: 'Walk-in' }}</span>
            </div>
            @if ($order->customer_phone)
                <div class="row">
                    <span>Phone:</span>
                    <span>{{ $order->customer_phone }}</span>
                </div>
            @endif
        @endif

        <div class="divider"></div>

        <div class="items">
            @foreach ($order->items as $item)
                <div>
                    <div class="row">
                        <span class="item-name">{{ $item->product_name }}</span>
                        <strong>{{ number_format((float) $item->line_total) }}</strong>
                    </div>
                    <div class="row muted">
                        <span>{{ $item->quantity }} x {{ number_format((float) $item->unit_price) }}</span>
                        <span>{{ config("pos.product_stations.{$item->station}", $item->station) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <div class="summary">
            <div class="row">
                <span>Items count</span>
                <span>{{ $itemsCount }}</span>
            </div>
            <div class="row">
                <span>Subtotal</span>
                <span>{{ number_format((float) $order->subtotal) }} so'm</span>
            </div>
            <div class="row total">
                <span>Total</span>
                <span>{{ number_format((float) $order->total) }} so'm</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="payments">
            @foreach ($order->payments as $payment)
                <div class="row">
                    <span>
                        {{ config('pos.payment_methods')[$payment->method] ?? $payment->method }}
                        @if ($payment->orderSplit)
                            ({{ $payment->orderSplit->label }})
                        @endif
                    </span>
                    <span>{{ number_format((float) $payment->amount) }} so'm</span>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <div class="row">
            <span>Paid amount</span>
            <strong>{{ number_format($paidAmount) }} so'm</strong>
        </div>

        @if ($order->notes)
            <div class="divider"></div>
            <p class="muted">Note: {{ $order->notes }}</p>
        @endif

        <p class="footer">
            Xaridingiz uchun rahmat.<br>
            Status: {{ $order->serviceStatusLabel() }}
        </p>
    </section>

    <div class="actions">
        <button type="button" class="button" onclick="window.print()">Print check</button>
        <button type="button" class="button secondary" onclick="window.close()">Close</button>
    </div>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 250);
        }, { once: true });
    </script>
</body>
</html>
