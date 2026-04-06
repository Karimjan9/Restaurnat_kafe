<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $dateFrom = Carbon::parse($request->input('date_from', now()->toDateString()))->startOfDay();
        $dateTo = Carbon::parse($request->input('date_to', now()->toDateString()))->endOfDay();
        $branchId = $request->integer('branch_id') ?: null;

        $ordersQuery = Order::query()
            ->whereIn('status', Order::financialStatuses())
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));

        $grossSales = (clone $ordersQuery)->sum('total');
        $orderCount = (clone $ordersQuery)->count();

        $paymentBreakdown = Payment::query()
            ->select('method', DB::raw('SUM(amount) as total'))
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->whereHas('order', fn ($query) => $query->when($branchId, fn ($q) => $q->where('branch_id', $branchId)))
            ->groupBy('method')
            ->pluck('total', 'method');

        $orderTypeBreakdown = (clone $ordersQuery)
            ->select('order_type', DB::raw('COUNT(*) as total'))
            ->groupBy('order_type')
            ->pluck('total', 'order_type');

        $topProducts = OrderItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(line_total) as total'))
            ->whereHas('order', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereBetween('paid_at', [$dateFrom, $dateTo])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
            })
            ->groupBy('product_name')
            ->orderByDesc('quantity')
            ->limit(8)
            ->get();

        $waiterPerformance = User::query()
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('SUM(orders.total) as revenue')
            )
            ->join('orders', 'orders.waiter_user_id', '=', 'users.id')
            ->whereIn('orders.status', Order::financialStatuses())
            ->whereBetween('orders.paid_at', [$dateFrom, $dateTo])
            ->when($branchId, fn ($query) => $query->where('orders.branch_id', $branchId))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->orderBy('users.name')
            ->get();

        $recentOrders = (clone $ordersQuery)
            ->with(['branch', 'cashier', 'waiter'])
            ->latest('paid_at')
            ->limit(10)
            ->get();

        return view('reports.index', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'filters' => [
                'branch_id' => $branchId,
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
            'grossSales' => $grossSales,
            'orderCount' => $orderCount,
            'averageOrderValue' => $orderCount > 0 ? $grossSales / $orderCount : 0,
            'paymentBreakdown' => $paymentBreakdown,
            'orderTypeBreakdown' => $orderTypeBreakdown,
            'topProducts' => $topProducts,
            'waiterPerformance' => $waiterPerformance,
            'recentOrders' => $recentOrders,
        ]);
    }
}
