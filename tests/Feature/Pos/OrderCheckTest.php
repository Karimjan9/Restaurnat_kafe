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

class OrderCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_open_printable_check_for_paid_order(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $cashier = User::where('login', 'cashier')->firstOrFail();
        $product = Product::firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->set('orderType', 'takeaway')
            ->set('customerName', 'Walk In')
            ->set('customerPhone', '+998900000000')
            ->call('addProduct', $product->id)
            ->call('checkout')
            ->assertHasNoErrors();

        $order = Order::with(['payments', 'items'])->firstOrFail();

        $response = $this->actingAs($cashier)->get(route('orders.check', $order));

        $response->assertOk();
        $response->assertSeeText($order->order_number);
        $response->assertSeeText($product->name);
        $response->assertSeeText('Print check');
    }

    public function test_check_cannot_be_opened_for_unpaid_order(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $cashier = User::where('login', 'cashier')->firstOrFail();
        $waiter = User::where('login', 'waiter')->firstOrFail();
        $product = Product::where('station', 'kitchen')->firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->set('waiterUserId', $waiter->id)
            ->call('addProduct', $product->id)
            ->call('checkout')
            ->assertHasNoErrors();

        $order = Order::firstOrFail();

        $response = $this->actingAs($cashier)->get(route('orders.check', $order));

        $response->assertNotFound();
    }
}
