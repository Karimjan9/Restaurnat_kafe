<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'cost_price')) {
            return;
        }

        $costs = [
            'BG-001' => 21000,
            'BG-002' => 18000,
            'HD-001' => 14000,
            'HD-002' => 27000,
            'DR-001' => 5000,
            'DR-002' => 6000,
            'DS-001' => 9000,
        ];

        foreach ($costs as $sku => $costPrice) {
            DB::table('products')
                ->where('sku', $sku)
                ->where(function ($query) {
                    $query->whereNull('cost_price')->orWhere('cost_price', 0);
                })
                ->update(['cost_price' => $costPrice]);
        }
    }

    public function down(): void
    {
        //
    }
};
