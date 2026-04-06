<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'branch_id',
        'dining_table_id',
        'user_id',
        'waiter_user_id',
        'closed_by_user_id',
        'order_type',
        'status',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'notes',
        'subtotal',
        'total',
        'placed_at',
        'paid_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'dining_table_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function splits(): HasMany
    {
        return $this->hasMany(OrderSplit::class)->orderBy('split_number');
    }

    public static function activeStatuses(): array
    {
        return ['open', 'in_service', 'ready', 'served'];
    }

    public static function settlementStatuses(): array
    {
        return ['open', 'in_service', 'ready', 'served', 'paid'];
    }

    public static function financialStatuses(): array
    {
        return ['paid', 'closed'];
    }

    public function hasSplitBill(): bool
    {
        return $this->splits()->exists();
    }

    public function splitBillOutstandingCount(): int
    {
        return $this->splits()->where('status', 'draft')->count();
    }

    public function splitBillPaidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function splitBillRemainingAmount(): float
    {
        return max(0, (float) $this->total - $this->splitBillPaidAmount());
    }

    public function serviceStatusLabel(): string
    {
        return config("pos.service_order_statuses.{$this->status}", $this->status);
    }

    public function refreshPreparationStatus(): void
    {
        if (in_array($this->status, self::financialStatuses(), true)) {
            return;
        }

        /** @var Collection<int, string> $statuses */
        $statuses = $this->items()->pluck('preparation_status');

        if ($statuses->isEmpty()) {
            $status = 'open';
        } elseif ($statuses->every(fn (string $status) => $status === 'served')) {
            $status = 'served';
        } elseif ($statuses->every(fn (string $status) => in_array($status, ['ready', 'served'], true))) {
            $status = 'ready';
        } elseif ($statuses->contains('preparing')) {
            $status = 'in_service';
        } else {
            $status = 'open';
        }

        if ($this->status !== $status) {
            $this->forceFill(['status' => $status])->save();
        }
    }
}
