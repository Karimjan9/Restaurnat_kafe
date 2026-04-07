<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        if (! $request->user()->hasPermission('reports.view') && $request->user()->branch_id !== $order->branch_id) {
            abort(403);
        }

        return view('orders.receipt', [
            'order' => $order->load(['branch', 'diningTable', 'cashier.role', 'waiter.role', 'closedBy.role', 'items', 'splits.paidBy', 'payments.orderSplit']),
        ]);
    }
}
