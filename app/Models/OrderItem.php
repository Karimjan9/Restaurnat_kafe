<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'station',
        'quantity',
        'unit_price',
        'line_total',
        'discount_total',
        'cost_total',
        'item_note',
        'preparation_status',
        'sent_to_station_at',
        'started_preparing_at',
        'ready_at',
        'served_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'cost_total' => 'decimal:2',
            'sent_to_station_at' => 'datetime',
            'started_preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'served_at' => 'datetime',
        ];
    }

    public function preparationStatusLabel(): string
    {
        return config("pos.preparation_statuses.{$this->preparation_status}", $this->preparation_status);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderItemModifier::class);
    }

    public function modifierSummary(): string
    {
        $this->loadMissing('modifiers');

        return $this->modifiers
            ->map(fn (OrderItemModifier $modifier) => "{$modifier->group_name}: {$modifier->option_name}")
            ->implode(', ');
    }
}
