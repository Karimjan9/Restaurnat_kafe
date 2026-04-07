<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('waiter_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        DB::table('orders')
            ->whereNull('waiter_user_id')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->chunkById(100, function ($orders) {
                $waiterIds = DB::table('users')
                    ->join('roles', 'roles.id', '=', 'users.role_id')
                    ->whereIn('users.id', collect($orders)->pluck('user_id')->filter()->unique())
                    ->where('roles.name', 'waiter')
                    ->pluck('users.id')
                    ->all();

                foreach ($orders as $order) {
                    if (in_array($order->user_id, $waiterIds, true)) {
                        DB::table('orders')
                            ->where('id', $order->id)
                            ->update(['waiter_user_id' => $order->user_id]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('waiter_user_id');
        });
    }
};
