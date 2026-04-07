<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('split_number');
            $table->string('label');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('draft');
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'split_number']);
            $table->index(['order_id', 'status']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_split_id')->nullable()->after('order_id')->constrained('order_splits')->nullOnDelete();
            $table->index('order_split_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['order_split_id']);
            $table->dropConstrainedForeignId('order_split_id');
        });

        Schema::dropIfExists('order_splits');
    }
};
