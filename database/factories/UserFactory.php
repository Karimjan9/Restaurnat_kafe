<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $login = fake()->unique()->userName();

        return [
            'name' => fake()->name(),
            'login' => $login,
            'password' => Hash::make($login.'456'),
            'remember_token' => Str::random(10),
        ];
    }
}
