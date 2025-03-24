<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(), // Titre aléatoire
            'description' => $this->faker->paragraph(), // Description aléatoire
            'status' => $this->faker->randomElement(['open', 'pending', 'closed']), // Statut aléatoire parmi les valeurs possibles
            'user_id' => User::factory(), // Associer à un utilisateur (UserFactory sera utilisé pour créer un utilisateur lié)
        ];
    }
}