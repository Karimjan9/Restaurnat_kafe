<?php

namespace Tests\Feature\Pos;

use App\Livewire\PosDashboard;
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

class SettleWaiterOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_finalize_a_served_waiter_order(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $order = $this->createServedWaiterOrder();
        $cashier = User::where('login', 'cashier')->firstOrFail();
        $waiter = User::where('login', 'waiter')->firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->call('selectServiceOrder', $order->id)
            ->set('servicePaymentMethod', 'card')
            ->call('completeServiceOrderPayment')
            ->assertHasNoErrors();

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame($cashier->id, $order->user_id);
        $this->assertSame($waiter->id, $order->waiter_user_id);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'user_id' => $cashier->id,
            'method' => 'card',
        ]);
    }

    public function test_cashier_can_close_a_paid_waiter_order(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $order = $this->createServedWaiterOrder();
        $cashier = User::where('login', 'cashier')->firstOrFail();
        $waiter = User::where('login', 'waiter')->firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->call('selectServiceOrder', $order->id)
            ->call('completeServiceOrderPayment')
            ->assertHasNoErrors()
            ->call('closeSelectedServiceOrder')
            ->assertHasNoErrors();

        $order->refresh();

        $this->assertSame('closed', $order->status);
        $this->assertNotNull($order->closed_at);
        $this->assertSame($waiter->id, $order->waiter_user_id);
        $this->assertSame($cashier->id, $order->closed_by_user_id);
    }

    public function test_cashier_cannot_finalize_waiter_order_before_service_is_completed(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $waiter = User::where('login', 'waiter')->firstOrFail();
        $cashier = User::where('login', 'cashier')->firstOrFail();
        $product = Product::where('station', 'kitchen')->firstOrFail();
        $order = $this->createOpenServiceOrder($waiter, $product);

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->call('selectServiceOrder', $order->id)
            ->call('completeServiceOrderPayment')
            ->assertHasErrors('selectedServiceOrderId');

        $order->refresh();

        $this->assertSame('open', $order->status);
        $this->assertSame($waiter->id, $order->waiter_user_id);
        $this->assertDatabaseMissing('payments', [
            'order_id' => $order->id,
            'user_id' => $cashier->id,
        ]);
    }

    public function test_cashier_cannot_close_waiter_order_before_payment(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $order = $this->createServedWaiterOrder();
        $cashier = User::where('login', 'cashier')->firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->call('selectServiceOrder', $order->id)
            ->call('closeSelectedServiceOrder')
            ->assertHasErrors('selectedServiceOrderId');

        $order->refresh();

        $this->assertSame('served', $order->status);
        $this->assertNull($order->closed_at);
    }

    public function test_waiter_panel_is_scoped_to_logged_in_waiter_and_shows_daily_commission(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $waiter = User::where('login', 'waiter')->firstOrFail();
        $otherWaiter = User::factory()->create([
            'name' => 'Second Waiter',
            'login' => 'waiter_two',
            'branch_id' => $waiter->branch_id,
            'role_id' => $waiter->role_id,
            'password' => 'password123',
        ]);
        $product = Product::where('station', 'kitchen')->firstOrFail();

        $myOrder = $this->createServedWaiterOrder($waiter, $product);
        $this->payServiceOrder($myOrder);

        $otherOrder = $this->createServedWaiterOrder($otherWaiter, $product);
        $this->payServiceOrder($otherOrder);

        $this->actingAs($waiter);

        Livewire::test(WaiterPanel::class)
            ->assertViewHas('orders', function ($orders) use ($myOrder, $otherOrder) {
                return $orders->contains('id', $myOrder->id)
                    && ! $orders->contains('id', $otherOrder->id);
            })
            ->assertViewHas('dailyCommission', function ($dailyCommission) use ($myOrder) {
                return abs((float) $dailyCommission - (float) $myOrder->waiterCommissionAmount()) < 0.0001;
            });
    }

    protected function createOpenServiceOrder(User $waiter, ?Product $product = null): Order
    {
        $cashier = User::where('login', 'cashier')->firstOrFail();
        $product ??= Product::where('station', 'kitchen')->firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->set('waiterUserId', $waiter->id)
            ->call('addProduct', $product->id)
            ->call('checkout')
            ->assertHasNoErrors();

        return Order::latest('id')->firstOrFail();
    }

    protected function createServedWaiterOrder(?User $waiter = null, ?Product $product = null): Order
    {
        $waiter ??= User::where('login', 'waiter')->firstOrFail();
        $chef = User::where('login', 'chef')->firstOrFail();
        $product ??= Product::where('station', 'kitchen')->firstOrFail();

        $order = $this->createOpenServiceOrder($waiter, $product);
        $item = OrderItem::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($chef);

        Livewire::test(StationQueue::class, ['station' => 'kitchen'])
            ->call('startItem', $item->id)
            ->call('markReady', $item->id)
            ->assertHasNoErrors();

        $this->actingAs($waiter);

        Livewire::test(WaiterPanel::class)
            ->call('serveReadyItems', $order->id)
            ->assertHasNoErrors();

        return $order->fresh();
    }

    protected function payServiceOrder(Order $order): void
    {
        $cashier = User::where('login', 'cashier')->firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->call('selectServiceOrder', $order->id)
            ->call('completeServiceOrderPayment')
            ->assertHasNoErrors();
    }
}
