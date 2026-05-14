<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSplitItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_split_id',
        'order_item_id',
        'quantity',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function split(): BelongsTo
    {
        return $this->belongsTo(OrderSplit::class, 'order_split_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
}
