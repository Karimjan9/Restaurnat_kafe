<?php

namespace Tests\Feature\Reports;

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

class WaiterPerformanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_shows_waiter_order_count_and_revenue(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $waiter = User::where('login', 'waiter')->firstOrFail();
        $cashier = User::where('login', 'cashier')->firstOrFail();
        $manager = User::where('login', 'manager')->firstOrFail();

        $order = $this->createServedWaiterOrder($waiter);

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->call('selectServiceOrder', $order->id)
            ->call('completeServiceOrderPayment')
            ->assertHasNoErrors()
            ->call('closeSelectedServiceOrder')
            ->assertHasNoErrors();

        $order->refresh();

        $response = $this->actingAs($manager)->get(route('reports.index', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('waiterPerformance', function ($waiterPerformance) use ($waiter, $order) {
            return $waiterPerformance->contains(function ($row) use ($waiter, $order) {
                return (int) $row->id === $waiter->id
                    && (int) $row->orders_count === 1
                    && (float) $row->revenue === (float) $order->total
                    && (float) $row->commission === (float) $order->waiterCommissionAmount();
            });
        });
    }

    protected function createServedWaiterOrder(User $waiter): Order
    {
        $cashier = User::where('login', 'cashier')->firstOrFail();
        $chef = User::where('login', 'chef')->firstOrFail();
        $product = Product::where('station', 'kitchen')->firstOrFail();

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->set('waiterUserId', $waiter->id)
            ->call('addProduct', $product->id)
            ->call('checkout')
            ->assertHasNoErrors();

        $order = Order::firstOrFail();
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
}
