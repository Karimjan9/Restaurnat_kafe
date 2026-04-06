<?php

namespace Tests\Feature\Pos;

use App\Livewire\PosDashboard;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RestaurantPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_create_and_auto_close_a_dine_in_order(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $cashier = User::where('login', 'cashier')->firstOrFail();
        $product = Product::firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->call('addProduct', $product->id)
            ->call('checkout')
            ->assertHasNoErrors();

        $order = Order::with(['items', 'payments'])->first();

        $this->assertNotNull($order);
        $this->assertSame('closed', $order->status);
        $this->assertSame('dine_in', $order->order_type);
        $this->assertNotNull($order->dining_table_id);
        $this->assertNotNull($order->closed_at);
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $order->payments);
    }
}
