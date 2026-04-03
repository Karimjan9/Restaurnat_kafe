<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RestaurantPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_open_branch_management(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $cashier = User::where('login', 'cashier')->firstOrFail();

        $response = $this->actingAs($cashier)->get(route('branches.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_open_branch_management(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $admin = User::where('login', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('branches.index'));

        $response->assertOk();
    }
}
