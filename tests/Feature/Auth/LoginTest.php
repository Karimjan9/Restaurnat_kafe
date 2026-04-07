<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RestaurantPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_admin_is_redirected_to_dashboard_after_login(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $response = $this->post(route('login.store'), [
            'login' => 'admin',
            'password' => 'admin456',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_cashier_is_redirected_to_pos_after_login(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $response = $this->post(route('login.store'), [
            'login' => 'cashier',
            'password' => 'cashier456',
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertAuthenticated();
    }

    public function test_waiter_is_redirected_to_waiter_panel_after_login(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $response = $this->post(route('login.store'), [
            'login' => 'waiter',
            'password' => 'waiter456',
        ]);

        $response->assertRedirect(route('waiter.index'));
        $this->assertAuthenticated();
    }
}
