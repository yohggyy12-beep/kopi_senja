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
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'nama_pengguna' => fake()->unique()->userName(),
            'nomor_telepon' => fake()->unique()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'kata_sandi' => static::$password ??= Hash::make('password'),
            'peran' => 'pelanggan',
            'wajib_ganti_kata_sandi' => false,
            'remember_token' => Str::random(10),
        ];
    }
    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn(array $attributes) => [
            'peran' => 'manager',
        ]);
    }

    public function kasir(): static
    {
        return $this->state(fn(array $attributes) => [
            'peran' => 'kasir',
        ]);
    }

    public function pelanggan(): static
    {
        return $this->state(fn(array $attributes) => [
            'peran' => User::PERAN_PELANGGAN,
        ]);
    }
}
