<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'price',
        'cost_price',
        'station',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function stationLabel(): string
    {
        return config("pos.product_stations.{$this->station}", $this->station);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('modifier_group_product.sort_order')
            ->orderBy('modifier_groups.sort_order')
            ->orderBy('modifier_groups.name');
    }

    public function activeModifierGroups(): BelongsToMany
    {
        return $this->modifierGroups()->where('modifier_groups.is_active', true);
    }
}
