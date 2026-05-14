<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shift;
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
        $commissionRate = (float) config('pos.waiter_commission_rate', 0.15);
        $commissionRateSql = number_format($commissionRate, 4, '.', '');

        $ordersQuery = Order::query()
            ->whereIn('status', Order::financialStatuses())
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));

        $grossSales = (clone $ordersQuery)->sum('total');
        $orderCount = (clone $ordersQuery)->count();
        $discountTotal = (clone $ordersQuery)->sum('discount_total');

        $refundsQuery = OrderRefund::query()
            ->whereBetween('refunded_at', [$dateFrom, $dateTo])
            ->whereHas('order', fn ($query) => $query->when($branchId, fn ($q) => $q->where('branch_id', $branchId)));

        $refundTotal = (clone $refundsQuery)->sum('amount');
        $netSales = max(0, (float) $grossSales - (float) $refundTotal);

        $paymentBreakdown = Payment::query()
            ->select('method', DB::raw('SUM(amount) as total'))
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->whereHas('order', fn ($query) => $query->when($branchId, fn ($q) => $q->where('branch_id', $branchId)))
            ->groupBy('method')
            ->pluck('total', 'method');

        $refundBreakdown = (clone $refundsQuery)
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->pluck('total', 'method');

        $orderTypeBreakdown = (clone $ordersQuery)
            ->select('order_type', DB::raw('COUNT(*) as total'))
            ->groupBy('order_type')
            ->pluck('total', 'order_type');

        $topProducts = OrderItem::query()
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(line_total) as total'),
                DB::raw('SUM(cost_total) as cogs'),
                DB::raw('SUM(line_total - cost_total) as margin')
            )
            ->whereHas('order', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereBetween('paid_at', [$dateFrom, $dateTo])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
            })
            ->groupBy('product_name')
            ->orderByDesc('quantity')
            ->limit(8)
            ->get();

        $cogs = OrderItem::query()
            ->whereHas('order', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereIn('status', Order::financialStatuses())
                    ->whereBetween('paid_at', [$dateFrom, $dateTo])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
            })
            ->sum('cost_total');

        $productMargins = OrderItem::query()
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(line_total) as revenue'),
                DB::raw('SUM(cost_total) as cogs'),
                DB::raw('SUM(line_total - cost_total) as gross_profit')
            )
            ->whereHas('order', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereIn('status', Order::financialStatuses())
                    ->whereBetween('paid_at', [$dateFrom, $dateTo])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
            })
            ->groupBy('product_name')
            ->orderByDesc('gross_profit')
            ->limit(12)
            ->get();

        $waiterPerformance = User::query()
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('SUM(orders.total) as revenue'),
                DB::raw("ROUND(SUM(orders.total) * {$commissionRateSql}, 2) as commission")
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

        $dailyClosings = Shift::query()
            ->with(['branch', 'cashier', 'closedBy'])
            ->whereBetween('opened_at', [$dateFrom, $dateTo])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->latest('opened_at')
            ->limit(12)
            ->get();

        return view('reports.index', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'filters' => [
                'branch_id' => $branchId,
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
            'grossSales' => $grossSales,
            'discountTotal' => $discountTotal,
            'refundTotal' => $refundTotal,
            'netSales' => $netSales,
            'cogs' => $cogs,
            'grossProfit' => $netSales - (float) $cogs,
            'orderCount' => $orderCount,
            'averageOrderValue' => $orderCount > 0 ? $grossSales / $orderCount : 0,
            'commissionRate' => $commissionRate,
            'totalWaiterCommission' => (float) $waiterPerformance->sum('commission'),
            'paymentBreakdown' => $paymentBreakdown,
            'refundBreakdown' => $refundBreakdown,
            'orderTypeBreakdown' => $orderTypeBreakdown,
            'topProducts' => $topProducts,
            'productMargins' => $productMargins,
            'waiterPerformance' => $waiterPerformance,
            'dailyClosings' => $dailyClosings,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');

        if ($format === 'pdf') {
            return view('reports.print', $this->exportData($request));
        }

        $data = $this->exportData($request);
        $filename = 'restaurant-report-'.$data['filters']['date_from'].'-'.$data['filters']['date_to'].'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Gross sales', $data['grossSales']]);
            fputcsv($handle, ['Discounts', $data['discountTotal']]);
            fputcsv($handle, ['Refunds', $data['refundTotal']]);
            fputcsv($handle, ['Net sales', $data['netSales']]);
            fputcsv($handle, ['COGS', $data['cogs']]);
            fputcsv($handle, ['Gross profit', $data['grossProfit']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Product', 'Quantity', 'Revenue', 'COGS', 'Gross profit']);

            foreach ($data['productMargins'] as $product) {
                fputcsv($handle, [
                    $product->product_name,
                    $product->quantity,
                    $product->revenue,
                    $product->cogs,
                    $product->gross_profit,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function exportData(Request $request): array
    {
        $dateFrom = Carbon::parse($request->input('date_from', now()->toDateString()))->startOfDay();
        $dateTo = Carbon::parse($request->input('date_to', now()->toDateString()))->endOfDay();
        $branchId = $request->integer('branch_id') ?: null;

        $ordersQuery = Order::query()
            ->whereIn('status', Order::financialStatuses())
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));

        $grossSales = (clone $ordersQuery)->sum('total');
        $discountTotal = (clone $ordersQuery)->sum('discount_total');

        $refundTotal = OrderRefund::query()
            ->whereBetween('refunded_at', [$dateFrom, $dateTo])
            ->whereHas('order', fn ($query) => $query->when($branchId, fn ($q) => $q->where('branch_id', $branchId)))
            ->sum('amount');

        $cogs = OrderItem::query()
            ->whereHas('order', function ($query) use ($dateFrom, $dateTo, $branchId) {
                $query->whereIn('status', Order::financialStatuses())
                    ->whereBetween('paid_at', [$dateFrom, $dateTo])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
            })
            ->sum('cost_total');

        $netSales = max(0, (float) $grossSales - (float) $refundTotal);

        return [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'filters' => [
                'branch_id' => $branchId,
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
            'grossSales' => $grossSales,
            'discountTotal' => $discountTotal,
            'refundTotal' => $refundTotal,
            'netSales' => $netSales,
            'cogs' => $cogs,
            'grossProfit' => $netSales - (float) $cogs,
            'productMargins' => OrderItem::query()
                ->select(
                    'product_name',
                    DB::raw('SUM(quantity) as quantity'),
                    DB::raw('SUM(line_total) as revenue'),
                    DB::raw('SUM(cost_total) as cogs'),
                    DB::raw('SUM(line_total - cost_total) as gross_profit')
                )
                ->whereHas('order', function ($query) use ($dateFrom, $dateTo, $branchId) {
                    $query->whereIn('status', Order::financialStatuses())
                        ->whereBetween('paid_at', [$dateFrom, $dateTo])
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
                })
                ->groupBy('product_name')
                ->orderByDesc('gross_profit')
                ->get(),
        ];
    }
}
