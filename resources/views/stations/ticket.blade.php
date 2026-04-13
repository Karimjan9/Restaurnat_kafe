<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $stationLabel }} Ticket | {{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            color: #111827;
            margin: 0;
            padding: 24px;
        }

        .ticket {
            max-width: 420px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
        }

        .eyebrow {
            font-size: 11px;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #6b7280;
        }

        h1, h2, p {
            margin: 0;
        }

        h1 {
            margin-top: 8px;
            font-size: 26px;
        }

        .meta, .summary, .items {
            margin-top: 18px;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 12px 14px;
            margin-top: 10px;
        }

        .row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
            margin-top: 4px;
        }

        .items .card {
            background: #f9fafb;
        }

        .totals {
            margin-top: 16px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 14px;
        }

        .actions {
            max-width: 420px;
            margin: 16px auto 0;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .button {
            border: 1px solid #111827;
            background: #111827;
            color: #ffffff;
            border-radius: 999px;
            padding: 10px 16px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .button.secondary {
            background: #ffffff;
            color: #111827;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .ticket {
                box-shadow: none;
                border: 0;
                max-width: none;
                border-radius: 0;
            }

            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <section class="ticket">
        <p class="eyebrow">{{ $stationLabel }} Ticket</p>
        <h1>{{ $order->order_number }}</h1>
        <p class="muted">{{ optional($order->placed_at)->format('d.m.Y H:i') }} | {{ $order->branch?->name }}</p>

        <div class="meta">
            <div class="card">
                <div class="row">
                    <div>
                        <p><strong>Table:</strong> {{ $order->diningTable?->name ?? 'Takeaway / delivery' }}</p>
                        <p class="muted"><strong>Waiter:</strong> {{ $order->waiter?->name ?? 'N/A' }}</p>
                    </div>
                    <div style="text-align:right;">
                        <p><strong>Status:</strong> {{ $order->serviceStatusLabel() }}</p>
                        <p class="muted"><strong>Cashier:</strong> {{ $order->cashier?->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            @if ($order->notes)
                <div class="card">
                    <p><strong>Note:</strong></p>
                    <p class="muted">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <div class="items">
            <p class="eyebrow">Station items</p>
            @foreach ($items as $item)
                <div class="card">
                    <div class="row">
                        <div>
                            <p><strong>{{ $item->product_name }}</strong></p>
                            <p class="muted">{{ $item->preparationStatusLabel() }}</p>
                        </div>
                        <div style="text-align:right;">
                            <p><strong>{{ $item->quantity }} pcs</strong></p>
                            <p class="muted">{{ number_format((float) $item->line_total) }} so'm</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="summary">
            <div class="card totals">
                <div class="row">
                    <div>
                        <p><strong>Total items</strong></p>
                        <p class="muted">{{ $itemsCount }} pcs</p>
                    </div>
                    <div style="text-align:right;">
                        <p><strong>Station total</strong></p>
                        <p class="muted">{{ number_format($stationTotal) }} so'm</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="actions">
        <button type="button" class="button" onclick="window.print()">Print</button>
        <button type="button" class="button secondary" onclick="window.close()">Close</button>
    </div>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 250);
        }, { once: true });
    </script>
</body>
</html>
