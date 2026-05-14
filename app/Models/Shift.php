<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'closed_by_user_id',
        'status',
        'opening_cash',
        'expected_cash',
        'counted_cash',
        'cash_difference',
        'opening_notes',
        'closing_notes',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'counted_cash' => 'decimal:2',
            'cash_difference' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public static function currentFor(?int $userId, ?int $branchId): ?self
    {
        if (! $userId || ! $branchId) {
            return null;
        }

        return self::query()
            ->open()
            ->where('user_id', $userId)
            ->where('branch_id', $branchId)
            ->latest('opened_at')
            ->first();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class);
    }

    public function cashPaymentsTotal(): float
    {
        return (float) $this->payments()->where('method', 'cash')->sum('amount');
    }

    public function cashRefundsTotal(): float
    {
        return (float) $this->refunds()->where('method', 'cash')->sum('amount');
    }

    public function expectedCashAmount(): float
    {
        return round((float) $this->opening_cash + $this->cashPaymentsTotal() - $this->cashRefundsTotal(), 2);
    }
}
