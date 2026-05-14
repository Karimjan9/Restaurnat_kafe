<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('selection_type')->default('single');
            $table->boolean('is_required')->default(false);
            $table->unsignedSmallInteger('min_selected')->default(0);
            $table->unsignedSmallInteger('max_selected')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('modifier_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modifier_group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('price_delta', 12, 2)->default(0);
            $table->decimal('cost_delta', 12, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['modifier_group_id', 'code']);
        });

        Schema::create('modifier_group_product', function (Blueprint $table) {
            $table->foreignId('modifier_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['modifier_group_id', 'product_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->text('item_note')->nullable()->after('cost_total');
        });

        Schema::create('order_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('modifier_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('modifier_option_id')->nullable()->constrained()->nullOnDelete();
            $table->string('group_name');
            $table->string('option_name');
            $table->decimal('price_delta', 12, 2)->default(0);
            $table->decimal('cost_delta', 12, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->index(['order_item_id', 'modifier_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_modifiers');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('item_note');
        });

        Schema::dropIfExists('modifier_group_product');
        Schema::dropIfExists('modifier_options');
        Schema::dropIfExists('modifier_groups');
    }
};
