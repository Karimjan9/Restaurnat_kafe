<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('station')->default('kitchen')->after('product_name');
            $table->string('preparation_status')->default('queued')->after('line_total');
            $table->timestamp('sent_to_station_at')->nullable()->after('preparation_status');
            $table->timestamp('started_preparing_at')->nullable()->after('sent_to_station_at');
            $table->timestamp('ready_at')->nullable()->after('started_preparing_at');
            $table->timestamp('served_at')->nullable()->after('ready_at');

            $table->index(['station', 'preparation_status']);
        });

        $productStations = DB::table('products')->pluck('station', 'id');
        $orders = DB::table('orders')
            ->select(['id', 'status', 'placed_at', 'paid_at'])
            ->get()
            ->keyBy('id');

        DB::table('order_items')
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($orders, $productStations) {
                foreach ($items as $item) {
                    $order = $orders->get($item->order_id);
                    $isPaid = $order?->status === 'paid';

                    DB::table('order_items')
                        ->where('id', $item->id)
                        ->update([
                            'station' => $productStations[$item->product_id] ?? 'kitchen',
                            'preparation_status' => $isPaid ? 'served' : 'queued',
                            'sent_to_station_at' => $item->created_at,
                            'started_preparing_at' => $isPaid ? ($order->placed_at ?? $item->created_at) : null,
                            'ready_at' => $isPaid ? ($order->paid_at ?? $item->created_at) : null,
                            'served_at' => $isPaid ? ($order->paid_at ?? $item->created_at) : null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['station', 'preparation_status']);
            $table->dropColumn([
                'station',
                'preparation_status',
                'sent_to_station_at',
                'started_preparing_at',
                'ready_at',
                'served_at',
            ]);
        });
    }
};
