<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Shift;
use App\Support\Audit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = $this->scopedBranchId($request);

        $tables = DiningTable::query()
            ->with(['branch', 'currentOrder.waiter', 'currentOrder.payments'])
            ->withCount('orders')
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $activeOrders = Order::query()
            ->with(['branch', 'diningTable', 'waiter', 'cashier', 'payments', 'refunds', 'items'])
            ->where('order_type', 'dine_in')
            ->whereIn('status', array_merge(Order::settlementStatuses(), Order::activeStatuses()))
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->latest('placed_at')
            ->limit(40)
            ->get();

        $recentFinancialOrders = Order::query()
            ->with(['branch', 'diningTable', 'cashier', 'payments', 'refunds'])
            ->whereIn('status', Order::financialStatuses())
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->latest('paid_at')
            ->limit(12)
            ->get();

        $shifts = Shift::query()
            ->with(['branch', 'cashier', 'closedBy'])
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->latest('opened_at')
            ->limit(12)
            ->get();

        $openShift = Shift::currentFor($request->user()->id, $request->user()->branch_id);

        return view('operations.index', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'selectedBranchId' => $branchId,
            'tables' => $tables,
            'activeOrders' => $activeOrders,
            'recentFinancialOrders' => $recentFinancialOrders,
            'shifts' => $shifts,
            'openShift' => $openShift,
            'activeStatuses' => array_merge(Order::activeStatuses(), ['paid']),
        ]);
    }

    public function updateTableStatus(Request $request, DiningTable $table): RedirectResponse
    {
        $this->authorizeBranch($request, $table->branch_id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('pos.table_statuses')))],
        ]);

        if (in_array($validated['status'], ['available', 'reserved', 'cleaning', 'blocked'], true)) {
            $table->forceFill([
                'status' => $validated['status'],
                'current_order_id' => null,
                'status_updated_at' => now(),
            ])->save();
        } else {
            return back()->with('error', 'Occupied/payment status order oqimi orqali boshqariladi.');
        }

        Audit::record('table.status.updated', $table, [
            'branch_id' => $table->branch_id,
            'status' => $validated['status'],
        ], $request);

        return back()->with('status', 'Stol statusi yangilandi.');
    }

    public function moveTable(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBranch($request, $order->branch_id);

        $validated = $request->validate([
            'dining_table_id' => ['required', 'integer', 'exists:dining_tables,id'],
        ]);

        $targetTable = DiningTable::whereKey($validated['dining_table_id'])
            ->where('branch_id', $order->branch_id)
            ->where('is_active', true)
            ->firstOrFail();

        if (! in_array($order->status, Order::activeStatuses(), true)) {
            return back()->with('error', 'Faqat aktiv xizmatdagi orderni boshqa stolga ko‘chirish mumkin.');
        }

        if ($this->tableHasOpenOrder($targetTable->id, $order->id)) {
            return back()->with('error', 'Tanlangan stol band.');
        }

        DB::transaction(function () use ($request, $order, $targetTable) {
            $previousTableId = $order->dining_table_id;

            $order->update(['dining_table_id' => $targetTable->id]);

            DiningTable::whereKey($previousTableId)->update([
                'status' => 'cleaning',
                'current_order_id' => null,
                'status_updated_at' => now(),
            ]);

            $targetTable->forceFill([
                'status' => 'occupied',
                'current_order_id' => $order->id,
                'status_updated_at' => now(),
            ])->save();

            Audit::record('order.table.moved', $order, [
                'branch_id' => $order->branch_id,
                'from_table_id' => $previousTableId,
                'to_table_id' => $targetTable->id,
            ], $request);
        });

        return back()->with('status', 'Order boshqa stolga ko‘chirildi.');
    }

    public function mergeOrders(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_order_id' => ['required', 'integer', 'exists:orders,id'],
            'source_order_id' => ['required', 'integer', 'exists:orders,id', 'different:target_order_id'],
        ]);

        $target = Order::with(['items', 'payments', 'splits'])->findOrFail($validated['target_order_id']);
        $source = Order::with(['items', 'payments', 'splits'])->findOrFail($validated['source_order_id']);

        $this->authorizeBranch($request, $target->branch_id);
        $this->authorizeBranch($request, $source->branch_id);

        if ($target->branch_id !== $source->branch_id) {
            return back()->with('error', 'Faqat bitta filial ichidagi orderlar merge qilinadi.');
        }

        if ($target->payments->isNotEmpty() || $source->payments->isNotEmpty() || $target->splits->isNotEmpty() || $source->splits->isNotEmpty()) {
            return back()->with('error', "To'lov yoki split boshlangan orderlarni merge qilib bo'lmaydi.");
        }

        if (! in_array($target->status, Order::activeStatuses(), true) || ! in_array($source->status, Order::activeStatuses(), true)) {
            return back()->with('error', 'Faqat aktiv xizmatdagi orderlar merge qilinadi.');
        }

        DB::transaction(function () use ($request, $target, $source) {
            $sourceTableId = $source->dining_table_id;

            $source->items()->update(['order_id' => $target->id]);
            $subtotal = (float) $target->items()->sum('line_total');
            $discount = min((float) $target->discount_total, $subtotal);

            $target->forceFill([
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'total' => max(0, $subtotal - $discount),
            ])->save();
            $target->refreshPreparationStatus();

            $source->forceFill([
                'status' => 'merged',
                'merged_into_order_id' => $target->id,
                'closed_at' => now(),
            ])->save();

            DiningTable::whereKey($sourceTableId)->update([
                'status' => 'cleaning',
                'current_order_id' => null,
                'status_updated_at' => now(),
            ]);

            Audit::record('order.merged', $target, [
                'branch_id' => $target->branch_id,
                'source_order_id' => $source->id,
            ], $request);
        });

        return back()->with('status', 'Orderlar merge qilindi.');
    }

    public function applyDiscount(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBranch($request, $order->branch_id);

        $validated = $request->validate([
            'discount_total' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($order->payments()->exists() || $order->splits()->exists()) {
            return back()->with('error', "Payment yoki split boshlangan orderga discount qo'llab bo'lmaydi.");
        }

        if (! in_array($order->status, Order::settlementStatuses(), true)) {
            return back()->with('error', 'Bu order discount uchun ochiq emas.');
        }

        $discount = min((float) $validated['discount_total'], (float) $order->subtotal);

        $order->forceFill([
            'discount_total' => $discount,
            'total' => max(0, (float) $order->subtotal - $discount),
        ])->save();

        Audit::record('order.discount.applied', $order, [
            'branch_id' => $order->branch_id,
            'discount_total' => $discount,
            'reason' => $validated['reason'] ?? null,
        ], $request);

        return back()->with('status', 'Discount orderga qo‘llandi.');
    }

    public function voidOrder(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBranch($request, $order->branch_id);
        $this->authorizePermission($request, 'refunds.manage');

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($order->payments()->exists()) {
            return back()->with('error', "To'lov bor order void qilinmaydi. Refund jarayonidan foydalaning.");
        }

        if (! in_array($order->status, Order::activeStatuses(), true)) {
            return back()->with('error', 'Faqat unpaid aktiv order void qilinadi.');
        }

        DB::transaction(function () use ($request, $order, $validated) {
            $order->forceFill([
                'status' => 'voided',
                'voided_by_user_id' => $request->user()->id,
                'voided_at' => now(),
                'void_reason' => $validated['void_reason'],
            ])->save();

            DiningTable::whereKey($order->dining_table_id)->update([
                'status' => 'cleaning',
                'current_order_id' => null,
                'status_updated_at' => now(),
            ]);

            Audit::record('order.voided', $order, [
                'branch_id' => $order->branch_id,
                'reason' => $validated['void_reason'],
            ], $request);
        });

        return back()->with('status', 'Order void qilindi.');
    }

    public function refundOrder(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBranch($request, $order->branch_id);
        $this->authorizePermission($request, 'refunds.manage');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::in(array_keys(config('pos.payment_methods')))],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if (! in_array($order->status, Order::financialStatuses(), true)) {
            return back()->with('error', 'Faqat paid/closed order bo‘yicha refund qilinadi.');
        }

        $paidAmount = (float) $order->payments()->sum('amount');
        $refundedAmount = (float) $order->refunds()->sum('amount');
        $available = max(0, $paidAmount - $refundedAmount);

        if ((float) $validated['amount'] > $available) {
            return back()->with('error', 'Refund miqdori mavjud to‘langan qoldiqdan katta.');
        }

        $shift = Shift::currentFor($request->user()->id, $order->branch_id);
        $payment = $order->payments()->where('method', $validated['method'])->latest('paid_at')->first()
            ?? $order->payments()->latest('paid_at')->first();

        $refund = $order->refunds()->create([
            'payment_id' => $payment?->id,
            'shift_id' => $shift?->id,
            'user_id' => $request->user()->id,
            'method' => $validated['method'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'refunded_at' => now(),
        ]);

        Audit::record('order.refunded', $order, [
            'branch_id' => $order->branch_id,
            'refund_id' => $refund->id,
            'amount' => (float) $validated['amount'],
            'method' => $validated['method'],
            'reason' => $validated['reason'],
        ], $request);

        return back()->with('status', 'Refund qayd qilindi.');
    }

    public function openShift(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'shifts.manage');

        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'opening_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->authorizeBranch($request, (int) $validated['branch_id']);

        if (Shift::currentFor($request->user()->id, (int) $validated['branch_id'])) {
            return back()->with('error', 'Bu filial uchun sizda ochiq shift bor.');
        }

        $shift = Shift::create([
            'branch_id' => $validated['branch_id'],
            'user_id' => $request->user()->id,
            'status' => 'open',
            'opening_cash' => $validated['opening_cash'],
            'expected_cash' => $validated['opening_cash'],
            'opening_notes' => $validated['opening_notes'] ?? null,
            'opened_at' => now(),
        ]);

        Audit::record('shift.opened', $shift, [
            'branch_id' => $shift->branch_id,
            'opening_cash' => (float) $shift->opening_cash,
        ], $request);

        return back()->with('status', 'Shift ochildi.');
    }

    public function closeShift(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorizePermission($request, 'shifts.manage');
        $this->authorizeBranch($request, $shift->branch_id);

        $validated = $request->validate([
            'counted_cash' => ['required', 'numeric', 'min:0'],
            'closing_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($shift->status !== 'open') {
            return back()->with('error', 'Bu shift allaqachon yopilgan.');
        }

        if ($shift->user_id !== $request->user()->id && ! $request->user()->hasAnyPermission(['reports.view', 'branches.manage'])) {
            return back()->with('error', "Faqat o'z shiftingizni yopishingiz mumkin.");
        }

        $expectedCash = $shift->expectedCashAmount();
        $countedCash = (float) $validated['counted_cash'];

        $shift->update([
            'status' => 'closed',
            'closed_by_user_id' => $request->user()->id,
            'expected_cash' => $expectedCash,
            'counted_cash' => $countedCash,
            'cash_difference' => round($countedCash - $expectedCash, 2),
            'closing_notes' => $validated['closing_notes'] ?? null,
            'closed_at' => now(),
        ]);

        Audit::record('shift.closed', $shift, [
            'branch_id' => $shift->branch_id,
            'expected_cash' => $expectedCash,
            'counted_cash' => $countedCash,
            'cash_difference' => (float) $shift->cash_difference,
        ], $request);

        return back()->with('status', 'Shift yopildi va cash reconciliation saqlandi.');
    }

    protected function scopedBranchId(Request $request): ?int
    {
        $requestedBranchId = $request->integer('branch_id') ?: null;

        if ($requestedBranchId) {
            $this->authorizeBranch($request, $requestedBranchId);

            return $requestedBranchId;
        }

        return $request->user()->hasAnyPermission(['reports.view', 'branches.manage'])
            ? null
            : $request->user()->branch_id;
    }

    protected function authorizeBranch(Request $request, int $branchId): void
    {
        if ($request->user()->hasAnyPermission(['reports.view', 'branches.manage'])) {
            return;
        }

        abort_unless($request->user()->branch_id === $branchId, 403);
    }

    protected function authorizePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasPermission($permission), 403);
    }

    protected function tableHasOpenOrder(int $tableId, ?int $ignoreOrderId = null): bool
    {
        return Order::query()
            ->where('dining_table_id', $tableId)
            ->whereIn('status', Order::settlementStatuses())
            ->when($ignoreOrderId, fn (Builder $query) => $query->whereKeyNot($ignoreOrderId))
            ->exists();
    }
}
