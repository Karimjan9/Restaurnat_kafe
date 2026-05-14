<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open');
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->default(0);
            $table->decimal('counted_cash', 12, 2)->nullable();
            $table->decimal('cash_difference', 12, 2)->nullable();
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::table('dining_tables', function (Blueprint $table) {
            $table->string('status')->default('available')->after('seats');
            $table->foreignId('current_order_id')->nullable()->after('status')->constrained('orders')->nullOnDelete();
            $table->timestamp('status_updated_at')->nullable()->after('current_order_id');
            $table->index(['branch_id', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount_total', 12, 2)->default(0)->after('subtotal');
            $table->foreignId('voided_by_user_id')->nullable()->after('closed_by_user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('merged_into_order_id')->nullable()->after('voided_by_user_id')->constrained('orders')->nullOnDelete();
            $table->timestamp('voided_at')->nullable()->after('closed_at');
            $table->text('void_reason')->nullable()->after('voided_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('discount_total', 12, 2)->default(0)->after('line_total');
            $table->decimal('cost_total', 12, 2)->default(0)->after('discount_total');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index(['shift_id', 'method']);
        });

        Schema::table('order_splits', function (Blueprint $table) {
            $table->string('split_type')->default('equal')->after('label');
        });

        Schema::create('order_split_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_split_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['order_split_id', 'order_item_id']);
        });

        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method');
            $table->decimal('amount', 12, 2);
            $table->text('reason')->nullable();
            $table->timestamp('refunded_at');
            $table->timestamps();

            $table->index(['order_id', 'refunded_at']);
            $table->index(['shift_id', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
        Schema::dropIfExists('order_split_items');

        Schema::table('order_splits', function (Blueprint $table) {
            $table->dropColumn('split_type');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['shift_id', 'method']);
            $table->dropConstrainedForeignId('shift_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['discount_total', 'cost_total']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('merged_into_order_id');
            $table->dropConstrainedForeignId('voided_by_user_id');
            $table->dropColumn(['discount_total', 'voided_at', 'void_reason']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });

        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'status']);
            $table->dropConstrainedForeignId('current_order_id');
            $table->dropColumn(['status', 'status_updated_at']);
        });

        Schema::dropIfExists('shifts');
    }
};
