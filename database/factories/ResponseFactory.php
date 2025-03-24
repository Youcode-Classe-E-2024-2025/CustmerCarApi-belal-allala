<?php

namespace Database\Factories;

use App\Models\Response;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResponseFactory extends Factory
{
    protected $model = Response::class;

    public function definition()
    {
        return [
            'content' => $this->faker->paragraph,
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
        ];
    }
}