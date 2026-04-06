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

class SplitBillTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_create_equal_splits_and_pay_them_until_order_is_paid(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $order = $this->createServedWaiterOrder();
        $cashier = User::where('login', 'cashier')->firstOrFail();
        $waiter = User::where('login', 'waiter')->firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->call('selectServiceOrder', $order->id)
            ->set('splitCount', 3)
            ->call('createEqualSplits')
            ->assertHasNoErrors();

        $order->refresh();
        $order->load('splits');

        $this->assertCount(3, $order->splits);
        $this->assertEquals((float) $order->total, (float) $order->splits->sum('amount'));
        $this->assertTrue($order->splits->every(fn ($split) => $split->status === 'draft'));

        $component = Livewire::test(PosDashboard::class)
            ->call('selectServiceOrder', $order->id)
            ->set('servicePaymentMethod', 'cash');

        foreach ($order->splits()->pluck('id') as $splitId) {
            $component
                ->call('selectSplit', $splitId)
                ->call('paySelectedSplit')
                ->assertHasNoErrors();
        }

        $order->refresh();
        $order->load('splits');

        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame($cashier->id, $order->user_id);
        $this->assertSame($waiter->id, $order->waiter_user_id);
        $this->assertTrue($order->splits->every(fn ($split) => $split->status === 'paid'));
        $this->assertDatabaseCount('payments', 3);
    }

    public function test_cashier_cannot_use_full_payment_after_split_bill_is_created(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $order = $this->createServedWaiterOrder();
        $cashier = User::where('login', 'cashier')->firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->call('selectServiceOrder', $order->id)
            ->set('splitCount', 2)
            ->call('createEqualSplits')
            ->assertHasNoErrors()
            ->call('completeServiceOrderPayment')
            ->assertHasErrors('selectedServiceOrderId');
    }

    protected function createServedWaiterOrder(): Order
    {
        $waiter = User::where('login', 'waiter')->firstOrFail();
        $chef = User::where('login', 'chef')->firstOrFail();
        $product = Product::where('station', 'kitchen')->firstOrFail();

        $this->actingAs($waiter);

        Livewire::test(WaiterPanel::class)
            ->call('addProduct', $product->id)
            ->call('sendToPreparation')
            ->assertHasNoErrors();

        $order = Order::firstOrFail();
        $item = OrderItem::where('order_id', $order->id)->firstOrFail();

        $this->assertNull($order->user_id);
        $this->assertSame($waiter->id, $order->waiter_user_id);

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
}
