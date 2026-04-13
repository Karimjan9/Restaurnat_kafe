<?php

namespace Tests\Feature\Service;

use App\Livewire\PosDashboard;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RestaurantPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StationTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_station_ticket_shows_only_items_for_requested_station(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $chef = User::where('login', 'chef')->firstOrFail();
        $waiter = User::where('login', 'waiter')->firstOrFail();
        $kitchenProduct = Product::where('station', 'kitchen')->firstOrFail();
        $barProduct = Product::where('station', 'bar')->firstOrFail();

        $order = $this->createServiceOrder($waiter, [$kitchenProduct->id, $barProduct->id]);

        $response = $this->actingAs($chef)->get(route('stations.ticket', [
            'station' => 'kitchen',
            'order' => $order,
        ]));

        $response->assertOk();
        $response->assertSeeText($order->order_number);
        $response->assertSeeText($kitchenProduct->name);
        $response->assertDontSeeText($barProduct->name);
    }

    public function test_wrong_station_permission_cannot_open_ticket(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $bartender = User::where('login', 'bartender')->firstOrFail();
        $waiter = User::where('login', 'waiter')->firstOrFail();
        $kitchenProduct = Product::where('station', 'kitchen')->firstOrFail();

        $order = $this->createServiceOrder($waiter, [$kitchenProduct->id]);

        $response = $this->actingAs($bartender)->get(route('stations.ticket', [
            'station' => 'kitchen',
            'order' => $order,
        ]));

        $response->assertForbidden();
    }

    protected function createServiceOrder(User $waiter, array $productIds): Order
    {
        $cashier = User::where('login', 'cashier')->firstOrFail();

        $this->actingAs($cashier);

        $component = Livewire::test(PosDashboard::class)
            ->set('waiterUserId', $waiter->id);

        foreach ($productIds as $productId) {
            $component->call('addProduct', $productId);
        }

        $component
            ->call('checkout')
            ->assertHasNoErrors();

        return Order::with('items')->latest('id')->firstOrFail();
    }
}
