<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('station')->default('kitchen')->after('price');
            $table->index('station');
        });

        $drinkCategoryIds = DB::table('categories')
            ->where('slug', 'drinks')
            ->pluck('id');

        if ($drinkCategoryIds->isNotEmpty()) {
            DB::table('products')
                ->whereIn('category_id', $drinkCategoryIds)
                ->update([
                    'station' => 'bar',
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['station']);
            $table->dropColumn('station');
        });
    }
};
