<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StationTicketController extends Controller
{
    public function show(Request $request, string $station, Order $order): View
    {
        abort_unless(array_key_exists($station, config('pos.product_stations', [])), 404);

        $permission = match ($station) {
            'kitchen' => 'kitchen.view',
            'bar' => 'bar.view',
            default => null,
        };

        abort_unless($permission && $request->user()->hasPermission($permission), 403);

        if (! $request->user()->hasAnyPermission(['dashboard.view', 'reports.view']) && $request->user()->branch_id !== $order->branch_id) {
            abort(403);
        }

        $order->load(['branch', 'diningTable', 'waiter', 'cashier', 'items']);

        $items = $order->items
            ->where('station', $station)
            ->values();

        abort_if($items->isEmpty(), 404);

        return view('stations.ticket', [
            'order' => $order,
            'station' => $station,
            'stationLabel' => config("pos.product_stations.{$station}", ucfirst($station)),
            'items' => $items,
            'itemsCount' => $items->sum('quantity'),
            'stationTotal' => (float) $items->sum('line_total'),
        ]);
    }
}
