<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $this->authorizeAccess($request, $order);

        return view('orders.receipt', [
            'order' => $this->loadOrder($order),
        ]);
    }

    public function check(Request $request, Order $order): View
    {
        $this->authorizeAccess($request, $order);

        $order = $this->loadOrder($order);

        abort_if($order->payments->isEmpty(), 404);

        return view('orders.check', [
            'order' => $order,
            'itemsCount' => $order->items->sum('quantity'),
            'paidAmount' => (float) $order->payments->sum('amount'),
        ]);
    }

    protected function authorizeAccess(Request $request, Order $order): void
    {
        if (! $request->user()->hasPermission('reports.view') && $request->user()->branch_id !== $order->branch_id) {
            abort(403);
        }
    }

    protected function loadOrder(Order $order): Order
    {
        return $order->load([
            'branch',
            'diningTable',
            'cashier.role',
            'waiter.role',
            'closedBy.role',
            'items',
            'splits.paidBy',
            'payments.orderSplit',
        ]);
    }
}
