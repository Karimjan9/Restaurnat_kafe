<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();

        $recentOrders = Order::query()
            ->with(['branch', 'cashier'])
            ->latest('paid_at')
            ->limit(6)
            ->get();

        $orderTypeBreakdown = Order::query()
            ->select('order_type', DB::raw('COUNT(*) as total'))
            ->whereDate('placed_at', $today)
            ->groupBy('order_type')
            ->pluck('total', 'order_type');

        return view('dashboard', [
            'stats' => [
                'salesToday' => Order::whereDate('paid_at', $today)->sum('total'),
                'ordersToday' => Order::whereDate('placed_at', $today)->count(),
                'branches' => Branch::count(),
                'tables' => DiningTable::count(),
                'categories' => Category::count(),
                'products' => Product::count(),
                'staff' => User::count(),
            ],
            'recentOrders' => $recentOrders,
            'orderTypeBreakdown' => $orderTypeBreakdown,
        ]);
    }
}
