<?php

namespace Tests\Feature\Pos;

use App\Livewire\PosDashboard;
use App\Models\ModifierGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RestaurantPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuModifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_add_menu_modifiers_and_item_note_to_order(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $cashier = User::where('login', 'cashier')->firstOrFail();
        $waiter = User::where('login', 'waiter')->firstOrFail();
        $burger = Product::where('sku', 'BG-001')->firstOrFail();
        $extras = ModifierGroup::where('code', 'burger_extras')->with('options')->firstOrFail();
        $spice = ModifierGroup::where('code', 'spice_level')->with('options')->firstOrFail();
        $cheese = $extras->options->firstWhere('code', 'cheese');
        $hot = $spice->options->firstWhere('code', 'hot');

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->set('waiterUserId', $waiter->id)
            ->call('configureProduct', $burger->id)
            ->call('toggleModifierOption', $extras->id, $cheese->id)
            ->call('toggleModifierOption', $spice->id, $hot->id)
            ->set('configuredQuantity', 2)
            ->set('configuredItemNote', 'no onion, well done')
            ->call('addConfiguredProduct')
            ->assertHasNoErrors()
            ->call('checkout')
            ->assertHasNoErrors();

        $order = Order::with('items.modifiers')->firstOrFail();
        $item = $order->items->first();
        $expectedUnitPrice = (float) $burger->price + (float) $cheese->price_delta + (float) $hot->price_delta;
        $expectedUnitCost = (float) $burger->cost_price + (float) $cheese->cost_delta + (float) $hot->cost_delta;

        $this->assertSame(2, $item->quantity);
        $this->assertSame('no onion, well done', $item->item_note);
        $this->assertEquals($expectedUnitPrice, (float) $item->unit_price);
        $this->assertEquals($expectedUnitPrice * 2, (float) $item->line_total);
        $this->assertEquals($expectedUnitCost * 2, (float) $item->cost_total);
        $this->assertCount(2, $item->modifiers);
        $this->assertTrue($item->modifiers->contains(fn ($modifier) => $modifier->option_name === 'Extra cheese'));
        $this->assertTrue($item->modifiers->contains(fn ($modifier) => $modifier->option_name === 'Hot'));
        $this->assertTrue($item->modifiers->every(fn ($modifier) => $modifier->quantity === 2));
    }

    public function test_unattached_modifier_options_are_not_applied_from_livewire_state(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $cashier = User::where('login', 'cashier')->firstOrFail();
        $waiter = User::where('login', 'waiter')->firstOrFail();
        $burger = Product::where('sku', 'BG-001')->firstOrFail();
        $extras = ModifierGroup::where('code', 'burger_extras')->with('options')->firstOrFail();
        $spice = ModifierGroup::where('code', 'spice_level')->with('options')->firstOrFail();
        $drinkSize = ModifierGroup::where('code', 'drink_size')->with('options')->firstOrFail();
        $cheese = $extras->options->firstWhere('code', 'cheese');
        $mild = $spice->options->firstWhere('code', 'mild');
        $large = $drinkSize->options->firstWhere('code', 'large');

        $this->actingAs($cashier);

        Livewire::test(PosDashboard::class)
            ->set('waiterUserId', $waiter->id)
            ->call('configureProduct', $burger->id)
            ->set('configuredModifierOptions', [
                $extras->id => [$cheese->id],
                $spice->id => [$mild->id],
                $drinkSize->id => [$large->id],
            ])
            ->call('addConfiguredProduct')
            ->assertHasNoErrors()
            ->call('checkout')
            ->assertHasNoErrors();

        $item = Order::with('items.modifiers')->firstOrFail()->items->first();

        $this->assertTrue($item->modifiers->contains(fn ($modifier) => $modifier->option_name === 'Extra cheese'));
        $this->assertFalse($item->modifiers->contains(fn ($modifier) => $modifier->option_name === 'Large'));
        $this->assertEquals((float) $burger->price + (float) $cheese->price_delta, (float) $item->unit_price);
    }
}
