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

    public function test_cashier_can_create_a_dine_in_service_order_with_waiter_assignment(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $cashier = User::where('login', 'cashier')->firstOrFail();
        $waiter = User::where('login', 'waiter')->firstOrFail();
        $product = Product::firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->set('waiterUserId', $waiter->id)
            ->call('addProduct', $product->id)
            ->call('checkout')
            ->assertHasNoErrors();

        $order = Order::with(['items', 'payments'])->first();

        $this->assertNotNull($order);
        $this->assertSame('open', $order->status);
        $this->assertSame('dine_in', $order->order_type);
        $this->assertNotNull($order->dining_table_id);
        $this->assertSame($waiter->id, $order->waiter_user_id);
        $this->assertNull($order->paid_at);
        $this->assertNull($order->closed_at);
        $this->assertCount(1, $order->items);
        $this->assertCount(0, $order->payments);
        $this->assertTrue($order->items->every(fn ($item) => $item->preparation_status === 'queued'));
    }
}
