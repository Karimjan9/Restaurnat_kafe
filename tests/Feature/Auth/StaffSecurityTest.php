<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RestaurantPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_creation_uses_temporary_password_and_audit_log(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $admin = User::where('login', 'admin')->firstOrFail();
        $branch = Branch::where('code', 'MAIN')->firstOrFail();
        $role = Role::where('name', 'waiter')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('staff.store'), [
            'name' => 'New Waiter',
            'login' => 'new_waiter',
            'branch_id' => $branch->id,
            'role_id' => $role->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $user = User::where('login', 'new_waiter')->firstOrFail();

        $this->assertTrue($user->force_password_change);
        $this->assertFalse(Hash::check('new_waiter456', $user->password));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'staff.created',
            'actor_user_id' => $admin->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_forced_password_user_must_change_password_before_using_app(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $waiter = User::where('login', 'waiter')->firstOrFail();
        $waiter->forceFill([
            'password' => Hash::make('Temp1234'),
            'force_password_change' => true,
        ])->save();

        $this->actingAs($waiter)
            ->get(route('waiter.index'))
            ->assertRedirect(route('password.change'));

        $this->actingAs($waiter)
            ->put(route('password.update'), [
                'current_password' => 'Temp1234',
                'password' => 'Strong1234',
                'password_confirmation' => 'Strong1234',
            ])
            ->assertRedirect(route('cabinet'));

        $this->assertFalse($waiter->fresh()->force_password_change);
        $this->assertNotNull($waiter->fresh()->password_changed_at);
        $this->assertTrue(AuditLog::where('action', 'user.password.changed')->where('user_id', $waiter->id)->exists());
    }
}
