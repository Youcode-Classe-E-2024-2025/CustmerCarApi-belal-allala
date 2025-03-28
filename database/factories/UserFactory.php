<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // mot de passe par défaut: "password"
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Rôle par défaut 'client' si aucun rôle spécifique n'est attribué
            if ($user->roles->isEmpty()) {
                $clientRole = Role::firstOrCreate(['name' => 'client']);
                $user->roles()->attach($clientRole);
            }
        });
    }

    public function withRole(string $roleName): static
    {
        return $this->afterCreating(function (User $user) use ($roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $user->roles()->sync([$role->id]);
        });
    }

    // Méthodes pratiques pour les rôles spécifiques
    public function asClient(): static
    {
        return $this->withRole('client');
    }

    public function asAgent(): static
    {
        return $this->withRole('agent');
    }

    public function asAdmin(): static
    {
        return $this->withRole('admin');
    }
}