<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'split_number',
        'label',
        'amount',
        'status',
        'paid_by_user_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_split_id');
    }

    public function statusLabel(): string
    {
        return config("pos.order_split_statuses.{$this->status}", $this->status);
    }
}
