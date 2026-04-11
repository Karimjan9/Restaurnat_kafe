<?php

namespace App\Livewire;

use App\Events\OperationsUpdated;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class WaiterPanel extends Component
{
    public int $branchId;

    public function mount(): void
    {
        $this->branchId = auth()->user()->branch_id
            ?? Branch::where('is_active', true)->value('id')
            ?? 0;
    }

    public function serveReadyItems(int $orderId): void
    {
        $order = $this->dailyOrdersQuery()
            ->whereKey($orderId)
            ->whereIn('status', Order::activeStatuses())
            ->with('items')
            ->firstOrFail();

        $readyItems = $order->items->where('preparation_status', 'ready');

        if ($readyItems->isEmpty()) {
            session()->flash('error', 'Bu orderda topshirishga tayyor item yo\'q.');

            return;
        }

        $servedAt = now();

        DB::transaction(function () use ($order, $readyItems, $servedAt) {
            $order->items()
                ->whereIn('id', $readyItems->pluck('id'))
                ->update([
                    'preparation_status' => 'served',
                    'served_at' => $servedAt,
                ]);

            $order->refreshPreparationStatus();
        });

        OperationsUpdated::dispatch(
            type: 'waiter.order.served',
            branchId: $order->branch_id,
            orderId: $order->id,
            meta: ['ready_count' => $readyItems->count()],
        );

        session()->flash('status', 'Tayyor itemlar mijozga topshirildi.');
    }

    #[On('operations-updated')]
    public function syncFromRealtime(): void
    {
        // Re-render the waiter terminal when operational state changes.
    }

    protected function dailyOrdersQuery(): Builder
    {
        return Order::query()
            ->where('branch_id', $this->branchId)
            ->where('order_type', 'dine_in')
            ->where('waiter_user_id', auth()->id())
            ->whereDate('placed_at', now()->toDateString());
    }

    protected function dailyPaidOrdersQuery(): Builder
    {
        return Order::query()
            ->where('branch_id', $this->branchId)
            ->where('order_type', 'dine_in')
            ->where('waiter_user_id', auth()->id())
            ->whereIn('status', Order::financialStatuses())
            ->whereDate('paid_at', now()->toDateString());
    }

    public function render()
    {
        $orders = $this->dailyOrdersQuery()
            ->with(['items', 'diningTable', 'cashier', 'payments'])
            ->orderByRaw("CASE status WHEN 'ready' THEN 0 WHEN 'in_service' THEN 1 WHEN 'open' THEN 2 WHEN 'served' THEN 3 WHEN 'paid' THEN 4 WHEN 'closed' THEN 5 ELSE 6 END")
            ->latest('placed_at')
            ->get();

        $dailyOrderAmount = (float) $orders->sum('total');
        $readyOrdersCount = $orders->filter(fn (Order $order) => $order->items->contains('preparation_status', 'ready'))->count();

        $paidOrders = $this->dailyPaidOrdersQuery()->get();
        $dailyRevenue = (float) $paidOrders->sum('total');
        $dailyCommission = (float) $paidOrders->sum(fn (Order $order) => $order->waiterCommissionAmount());

        return view('livewire.waiter-panel', [
            'branch' => Branch::find($this->branchId),
            'orders' => $orders,
            'commissionRate' => (float) config('pos.waiter_commission_rate', 0.15),
            'dailyOrdersCount' => $orders->count(),
            'readyOrdersCount' => $readyOrdersCount,
            'dailyOrderAmount' => $dailyOrderAmount,
            'dailyRevenue' => $dailyRevenue,
            'dailyCommission' => $dailyCommission,
        ]);
    }
}
