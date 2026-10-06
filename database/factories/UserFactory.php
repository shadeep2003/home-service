<?php
namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
class UserFactory extends Factory
{
    protected $model = User::class;
    public function definition(): array
    {
        return ['name' => fake()->name(), 'email' => fake()->unique()->safeEmail(), 'password' => 'Password123', 'role' => 'customer', 'email_verified_at' => now()];
    }
    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
