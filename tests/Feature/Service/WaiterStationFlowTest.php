<?php

namespace Tests\Feature\Service;

use App\Livewire\StationQueue;
use App\Livewire\WaiterPanel;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RestaurantPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WaiterStationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiter_can_send_items_to_kitchen_and_bar(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $waiter = User::where('login', 'waiter')->firstOrFail();
        $kitchenProduct = Product::where('station', 'kitchen')->firstOrFail();
        $barProduct = Product::where('station', 'bar')->firstOrFail();

        $this->actingAs($waiter);

        Livewire::test(WaiterPanel::class)
            ->call('addProduct', $kitchenProduct->id)
            ->call('addProduct', $barProduct->id)
            ->set('notes', 'First service round')
            ->call('sendToPreparation')
            ->assertHasNoErrors();

        $order = Order::with('items')->first();

        $this->assertNotNull($order);
        $this->assertSame('dine_in', $order->order_type);
        $this->assertSame('open', $order->status);
        $this->assertNull($order->user_id);
        $this->assertSame($waiter->id, $order->waiter_user_id);
        $this->assertNull($order->paid_at);
        $this->assertCount(2, $order->items);
        $this->assertSame(['bar', 'kitchen'], $order->items->pluck('station')->sort()->values()->all());
        $this->assertTrue($order->items->every(fn (OrderItem $item) => $item->preparation_status === 'queued'));
    }

    public function test_ready_items_can_move_from_chef_queue_back_to_waiter_service(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $waiter = User::where('login', 'waiter')->firstOrFail();
        $chef = User::where('login', 'chef')->firstOrFail();
        $kitchenProduct = Product::where('station', 'kitchen')->firstOrFail();

        $this->actingAs($waiter);

        Livewire::test(WaiterPanel::class)
            ->call('addProduct', $kitchenProduct->id)
            ->call('sendToPreparation')
            ->assertHasNoErrors();

        $order = Order::firstOrFail();
        $item = OrderItem::where('station', 'kitchen')->firstOrFail();

        $this->actingAs($chef);

        Livewire::test(StationQueue::class, ['station' => 'kitchen'])
            ->call('startItem', $item->id)
            ->call('markReady', $item->id)
            ->assertHasNoErrors();

        $item->refresh();
        $order->refresh();

        $this->assertSame('ready', $item->preparation_status);
        $this->assertSame('ready', $order->status);

        $this->actingAs($waiter);

        Livewire::test(WaiterPanel::class)
            ->call('serveReadyItems', $order->id)
            ->assertHasNoErrors();

        $item->refresh();
        $order->refresh();

        $this->assertSame('served', $item->preparation_status);
        $this->assertSame('served', $order->status);
        $this->assertSame($waiter->id, $order->waiter_user_id);
    }
}
