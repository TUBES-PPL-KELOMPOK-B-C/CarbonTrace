<?php

namespace Database\Factories;

use App\Models\User314;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User314>
 */
class User314Factory extends Factory
{
    protected $model = User314::class;

    protected static ?string $password;

    public function definition(): array
    {
        $role = Role::whereIn('name', ['pengguna', 'user'])->first()
            ?? Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);

        return [
            'role_id' => $role->id,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'photo' => null,
            'campus' => 'Telkom University',
            'study_program' => 'S1 Informatika',
            'points' => 0,
            'streak_count' => 0,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
