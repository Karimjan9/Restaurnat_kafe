<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restaurant Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 32px; }
        h1, h2 { margin: 0; }
        .muted { color: #6b7280; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 24px 0; }
        .card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        th { background: #f9fafb; }
        @media print { button { display: none; } body { margin: 18px; } }
    </style>
</head>
<body>
    <button onclick="window.print()">Print / Save as PDF</button>

    <h1>Restaurant Report</h1>
    <p class="muted">{{ $filters['date_from'] }} - {{ $filters['date_to'] }}</p>

    <div class="grid">
        <div class="card"><strong>Gross sales</strong><br>{{ number_format((float) $grossSales) }} so'm</div>
        <div class="card"><strong>Refunds</strong><br>{{ number_format((float) $refundTotal) }} so'm</div>
        <div class="card"><strong>Net sales</strong><br>{{ number_format((float) $netSales) }} so'm</div>
        <div class="card"><strong>Discounts</strong><br>{{ number_format((float) $discountTotal) }} so'm</div>
        <div class="card"><strong>COGS</strong><br>{{ number_format((float) $cogs) }} so'm</div>
        <div class="card"><strong>Gross profit</strong><br>{{ number_format((float) $grossProfit) }} so'm</div>
    </div>

    <h2>Product Margin</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Revenue</th>
                <th>COGS</th>
                <th>Gross profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productMargins as $product)
                <tr>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ number_format((float) $product->revenue) }}</td>
                    <td>{{ number_format((float) $product->cogs) }}</td>
                    <td>{{ number_format((float) $product->gross_profit) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
